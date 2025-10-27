<?php

namespace Application\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Application\Entity\Cidade;
use Application\Entity\Estado;
class LoadCidades extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {

        $jsonContent = file_get_contents(__DIR__ . '/data/municipios.json');

        $cidadesData = json_decode($jsonContent, true);

        if ($cidadesData === null) {
            throw new \RuntimeException('Não foi possível decodificar o arquivo municipios.json');
        }

        echo "Carregando " . count($cidadesData) . " cidades...\n";

        foreach ($cidadesData as $data) {

            $idEstado = $data['Estado'];
            $nomeCidade = $data['Nome'];

            /** @var Estado $estado */
            $estado = $this->getReference('estado-id-' . $idEstado, Estado::class);

            if (!$estado) {
                continue;
            }

            $cidade = new Cidade();
            $cidade->setNome($nomeCidade);
            $cidade->setEstado($estado);

            $manager->persist($cidade);
        }

        $manager->flush();
        echo "Cidades carregadas com sucesso.\n";
    }

    public function getOrder(): int
    {
        return 2;
    }
}