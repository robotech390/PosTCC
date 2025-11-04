<?php
namespace Application\Controller;

use Laminas\Authentication\AuthenticationService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Application\Form\LoginForm;

class AuthController extends AbstractActionController
{
    private AuthenticationService $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    public function loginAction()
    {
        if ($this->authService->hasIdentity()) {
            return $this->redirect()->toRoute('paciente');
        }

        $form = new LoginForm();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();

                $adapter = $this->authService->getAdapter();
                $adapter->setIdentity($data['email']);
                $adapter->setCredential($data['password']);

                $result = $this->authService->authenticate();

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
        $this->authService->clearIdentity();
        $this->flashMessenger()->addSuccessMessage('Você foi desconectado com sucesso.');

        return $this->redirect()->toRoute('home');
    }
}