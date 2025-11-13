<?php
namespace Application\Controller;

use Application\Plugin\Login\AuthManager;
use Doctrine\ORM\EntityManager;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Application\Form\LoginForm;

class AuthController extends AbstractActionController
{
    private EntityManager $entityManager;
    private AuthManager $authManager;

    public function __construct($entityManager, $authManager)
    {
        $this->entityManager = $entityManager;
        $this->authManager = $authManager;
    }

    public function loginAction()
    {
        $form = new LoginForm();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();

                $result = $this->authManager->login($data['email'], $data['password']);

                if ($result->isValid()) {
                    return $this->redirect()->toRoute('paciente');
                } else {
                    $this->flashMessenger()->addErrorMessage('E-mail ou senha inválidos.');
                }
            }
        }

        $this->layout('layout/login-layout');
        return new ViewModel(['form' => $form]);
    }

    public function logoutAction()
    {
        $this->authManager->clearIdentity();
        $this->flashMessenger()->addSuccessMessage('Você foi desconectado com sucesso.');

        return $this->redirect()->toRoute('home');
    }
}