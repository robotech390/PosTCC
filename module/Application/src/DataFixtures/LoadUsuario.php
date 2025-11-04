<?php
namespace Application\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Application\Entity\Usuario;

class LoadUsuario extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $email = 'admin@gmail.com';

        $userRepo = $manager->getRepository(Usuario::class);
        $user = $userRepo->findOneBy(['email' => $email]);

        if (!$user) {
            $adminUser = new Usuario();
            $adminUser->setEmail($email);
            $adminUser->setPassword('123456');

            $manager->persist($adminUser);
            $manager->flush();

            echo "Usuário '{$email}' criado com senha 'admin123'.\n";
        }
    }

    public function getOrder(): int
    {
        return 0;
    }
}