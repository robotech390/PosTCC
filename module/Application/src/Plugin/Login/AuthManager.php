<?php

namespace Application\Plugin\Login;

use Doctrine\ORM\EntityManager;
use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\Result;

class AuthManager
{
    private AuthenticationService $authService;
    private EntityManager $em;

    public function __construct(AuthenticationService $authService, EntityManager $entityManager)
    {
        $this->authService = $authService;
        $this->em = $entityManager;
    }

    public function login($login, $password): Result
    {
        if ($this->authService->getIdentity() != null) {
           $this->authService->clearIdentity();
        }

        /** @var AuthAdapter $authAdapter */
        $authAdapter = $this->authService->getAdapter();

        $authAdapter->setLogin($login);
        $authAdapter->setPassword($password);

        return $this->authService->authenticate();
    }

    public function hasIdentity(): bool
    {
        return $this->authService->hasIdentity();
    }

    public function clearIdentity(): void
    {
        $this->authService->clearIdentity();

    }
}
