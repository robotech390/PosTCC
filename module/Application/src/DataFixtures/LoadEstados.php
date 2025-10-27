<?php

namespace Application\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Application\Entity\Estado;

class LoadEstados  extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $jsonContent = file_get_contents(__DIR__ . '/data/estados.json');

        $estadosData = json_decode($jsonContent, true);

        if ($estadosData === null) {
            throw new \RuntimeException('Não foi possível decodificar o arquivo estados.json');
        }

        echo "Carregando " . count($estadosData) . " estados...\n";

        foreach ($estadosData as $data) {
            $estado = new Estado();
            $estado->setSigla($data['Sigla']);
            $estado->setNome($data['Nome']);

            $manager->persist($estado);

            $this->addReference('estado-id-' . $data['ID'], $estado);
        }

        $manager->flush();
        echo "Estados carregados com sucesso.\n";
    }

    public function getOrder(): int
    {
        return 1;
    }
}