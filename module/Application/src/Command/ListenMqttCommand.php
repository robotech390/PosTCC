<?php
namespace Application\Command;

use Application\Entity\ESP32;
use Application\Entity\Leito;
use Application\Entity\Paciente;
use Application\Entity\Pino;
use Doctrine\ORM\EntityManager;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ListenMqttCommand extends Command
{
    protected static $defaultName = 'app:mqtt:listen';

    private EntityManager $entityManager;
    private array $mqttConfig;
    private ?MqttClient $mqttClient = null;

    public function __construct(EntityManager $entityManager, array $mqttConfig)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->mqttConfig = $mqttConfig;
    }

    protected function configure(): void
    {
        $this->setDescription('Inicia o listener de mensagens MQTT para atualização de status de pacientes/leitos.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        error_reporting(E_ALL);
        ini_set('display_errors', '1');

        $io = new SymfonyStyle($input, $output);
        $io->title('Iniciando Listener MQTT');

        $server   = $this->mqttConfig['server'];
        $port     = $this->mqttConfig['port'];
        $clientId = $this->mqttConfig['client_id'];
        $topic    = $this->mqttConfig['topic'];
        $connectionSettings = (new ConnectionSettings())
            ->setKeepAliveInterval(60);

        $this->mqttClient = new MqttClient($server, $port, $clientId);

        try {
            $this->mqttClient->connect($connectionSettings, true); // true = clean session
            $io->success("Conectado ao broker MQTT em {$server}:{$port} com Client ID: {$clientId}");
        } catch (\Exception $e) {
            $io->error("Falha ao conectar ao broker MQTT: " . $e->getMessage());
            return Command::FAILURE;
        }

        $this->mqttClient->subscribe($topic, function ($receivedTopic, $message) use ($io) {
            $io->info(sprintf("MSG Recebida [%s]: %s", $receivedTopic, $message));

            try {
                $data = json_decode($message, true);
                if ($data === null || !isset($data['mac_address']) || !isset($data['pino']) || !isset($data['evento'])) {
                    $io->warning('Payload JSON inválido. Esperado: {"mac_address": "...", "pino": N, "evento": "..."}. Ignorando.');
                    return;
                }

                $macAddress = trim($data['mac_address']);
                $numeroPino = (int) $data['pino'];
                $evento = trim($data['evento']);

                $esp32Repo = $this->entityManager->getRepository(ESP32::class);
                $esp32 = $esp32Repo->findOneBy(['macAddress' => $macAddress]);

                if (!$esp32) {
                    $io->note("ESP32 [{$macAddress}] não encontrado. Criando novo.");

                    try {

                        $qntEsp = count($esp32Repo->findAll()) + 1;

                        $esp32 = new ESP32();
                        $esp32->setMacAddress($macAddress);
                        $esp32->setNomeAmigavel("Dispositivo - {$qntEsp}");

                        $this->entityManager->persist($esp32);
                        $this->entityManager->flush();

                    }catch (\Exception $e) {
                        $io->error("Erro ao criar novo ESP32 para MAC [{$macAddress}]: " . $e->getMessage());
                        return;
                    }

                    $io->info("Novo ESP32 ID [{$esp32->getId()}] criado.");
                    $io->warning("Pino [{$numeroPino}] do novo ESP32 [{$macAddress}] ainda não está cadastrado no sistema. Ignorando evento.");

                } else {
                    $io->text("ESP32 ID [{$esp32->getId()}] encontrado.");
                }

                $pinoRepo = $this->entityManager->getRepository(Pino::class);
                $pino = $pinoRepo->findOneBy(['esp32' => $esp32, 'numeroPino' => $numeroPino]);

                if (!$pino) {
                     $io->note("Criando registro para o pino {$numeroPino}...");
                     $pino = new Pino();
                     $pino->setEsp32($esp32);
                     $pino->setNumeroPino($numeroPino);

                     $this->entityManager->persist($pino);
                     $this->entityManager->flush();

                     $io->info("Pino ID [{$pino->getId()}] criado.");

                } else {
                    $io->text("Entidade Pino ID [{$pino->getId()}] (GPIO {$pino->getNumeroPino()}) encontrada.");
                }

                $leitoRepo = $this->entityManager->getRepository(Leito::class);
                $leito = $leitoRepo->findOneBy(['pino' => $pino]);

                if (!$leito) {
                    $io->warning("Nenhum leito encontrado associado ao Pino ID [{$pino->getId()}] (GPIO {$numeroPino} do ESP {$macAddress}). Verifique a associação Leito <-> Pino. Ignorando.");
                    return;
                }

                $io->text("Leito ID [{$leito->getId()}] ({$leito->getNumero()} - {$leito->getSetor()}) encontrado.");

                $paciente = $leito->getPaciente();

                if (!$paciente) {
                    $io->note("Leito [{$leito->getNumero()}] está vazio. Nenhum paciente para atualizar.");
                    return;
                }

                $paciente->setUltimoStatus($evento);
                $paciente->setDataUltimoStatus(new \DateTime());

                $this->entityManager->flush();
                $this->entityManager->clear();

                $io->success("Status do Paciente [{$paciente->getPessoa()->getNome()}] no Leito [{$leito->getNumero()}] atualizado para '{$evento}'.");

            } catch (\Exception $e) {
                $io->error("Erro ao processar mensagem: " . $e->getMessage());
            }
        }, 0);

        try {
            $this->mqttClient->loop(true);
        } catch (\Exception $e) {
            $io->error("Erro crítico no loop MQTT: " . $e->getMessage());
            if ($this->mqttClient->isConnected()) {
                $this->mqttClient->disconnect();
            }
            return Command::FAILURE;
        }

        if ($this->mqttClient->isConnected()) {
            $this->mqttClient->disconnect();
        }
        $io->note('Listener MQTT finalizado.');
        return Command::SUCCESS;
    }
}