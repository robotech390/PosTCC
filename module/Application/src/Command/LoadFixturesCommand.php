<?php

namespace Application\Command;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class LoadFixturesCommand extends Command
{
    protected static $defaultName = 'app:fixtures:load';

    private EntityManager $entityManager;
    private string $fixturesPath;

    public function __construct(EntityManager $entityManager, string $fixturesPath)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->fixturesPath = $fixturesPath;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Carrega os data fixtures no banco de dados.')
            ->addOption('append', null, InputOption::VALUE_NONE, 'Adiciona os dados sem apagar o que já existe.')
            ->addOption('purge-with-truncate', null, InputOption::VALUE_NONE, 'Limpa o banco de dados antes de carregar.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Carregando Data Fixtures');

        $loader = new Loader();
        $loader->loadFromDirectory($this->fixturesPath);
        $fixtures = $loader->getFixtures();

        if (!$fixtures) {
            $io->warning('Nenhum fixture encontrado no caminho: ' . $this->fixturesPath);
            return Command::SUCCESS;
        }

        $purger = new ORMPurger($this->entityManager);
        $executor = new ORMExecutor($this->entityManager, $purger);

        if ($input->getOption('append') === false) {
            if (!$io->confirm('CUIDADO! Isso irá apagar TODOS os dados do seu banco. Deseja continuar?', false)) {
                $io->note('Comando cancelado.');
                return Command::INVALID;
            }
            $executor->purge();
            $io->text('Banco de dados limpo.');
        }

        $executor->execute($fixtures, true); // true = append
        $io->success('Data Fixtures carregados com sucesso!');

        return Command::SUCCESS;
    }
}