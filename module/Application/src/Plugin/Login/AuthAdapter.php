<?php

namespace Application\Plugin\Login;

use Application\Entity\Usuario;
use Doctrine\ORM\EntityManager;
use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\Result;

class AuthAdapter implements AdapterInterface
{
    private string $login;
    private string $password;

    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function setLogin(string $login): void
    {
        $this->login = $login;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function authenticate(): Result
    {
        $user = $this->entityManager->getRepository(Usuario::class)->findOneBy(['email' => $this->login]);

        if ($user == null) {
            return new Result(
                Result::FAILURE_IDENTITY_NOT_FOUND,
                null,
                ['Credenciais inválidas.']
            );
        }

        if (password_verify($this->password, $user->getPassword())) {
            return new Result(
                Result::SUCCESS,
                [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'login' => $this->login,
                ],
                ['Sucesso']
            );
        }

        return new Result(
            Result::FAILURE_CREDENTIAL_INVALID,
            null,
            ['Credenciais inválidas.']
        );
    }
}
