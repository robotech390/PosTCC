<?php
namespace Application\Controller;

use Application\Entity\Leito;
use Application\Entity\ESP32;
use Application\Entity\Pino;
use Application\Form\LeitoForm; // Importar o LeitoForm
use Doctrine\ORM\EntityManager;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class LeitoController extends AbstractActionController
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function listarAction()
    {
        $leitos = $this->entityManager->getRepository(Leito::class)->findAll();
        return new ViewModel(['leitos' => $leitos]);
    }

    public function cadastrarAction()
    {
        $formManager = $this->getEvent()->getApplication()->getServiceManager()->get('FormElementManager');
        /** @var LeitoForm $form */
        $form = new LeitoForm($this->entityManager);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();

                $pinoId = $data['pino'];
                $pino = $this->entityManager->find(Pino::class, $pinoId);

                if (!$pino || $pino->getEsp32()->getId() != $data['esp32']) {
                    $this->flashMessenger()->addErrorMessage('Pino inválido ou não pertence ao dispositivo selecionado.');
                    return new ViewModel(['form' => $form, 'title' => 'Cadastrar Leito']);
                }

                $leitoExistente = $this->entityManager->getRepository(Leito::class)->findOneBy(['pino' => $pino]);
                if ($leitoExistente) {
                    $this->flashMessenger()->addErrorMessage("O pino {$pino->getNumeroPino()} do dispositivo selecionado já está associado ao leito {$leitoExistente->getNumero()}.");
                    return new ViewModel(['form' => $form, 'title' => 'Cadastrar Leito']);
                }

                $leito = new Leito();
                $leito->setNumero($data['numero']);
                $leito->setSetor($data['setor']);
                $leito->setPino($pino);

                $this->entityManager->persist($leito);
                $this->entityManager->flush();

                $this->flashMessenger()->addSuccessMessage('Leito cadastrado com sucesso!');
                return $this->redirect()->toRoute('leitos');
            }
        }

        $view = new ViewModel([
            'form' => $form,
            'title' => 'Cadastrar Leito'
        ]);

        $view->setTemplate('application/leito/form');

        return $view;
    }

    public function editarAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if ($id === 0) {
            $this->flashMessenger()->addErrorMessage('Id invalido!');
            return $this->redirect()->toRoute('leitos');
        }

        /** @var Leito|null $leito */
        $leito = $this->entityManager->find(Leito::class, $id);
        if (!$leito) {
            $this->flashMessenger()->addErrorMessage('Leito não encontrado Invalido!');
            return $this->redirect()->toRoute('leitos');
        }

        $form = new LeitoForm($this->entityManager);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();

                $pinoId = $data['pino'];
                $pino = $this->entityManager->find(Pino::class, $pinoId);

                if (!$pino || $pino->getEsp32()->getId() != $data['esp32']) {
                    $this->flashMessenger()->addErrorMessage('Pino inválido ou não pertence ao dispositivo selecionado.');
                    return new ViewModel(['form' => $form, 'id' => $id, 'title' => 'Editar Leito']);
                }

                $leitoExistente = $this->entityManager->getRepository(Leito::class)->findOneBy(['pino' => $pino]);
                if ($leitoExistente && $leitoExistente->getId() !== $leito->getId()) {
                    $this->flashMessenger()->addErrorMessage("O pino {$pino->getNumeroPino()} do dispositivo selecionado já está associado ao leito {$leitoExistente->getNumero()}.");
                    return new ViewModel(['form' => $form, 'id' => $id, 'title' => 'Editar Leito']);
                }

                $leito->setNumero($data['numero']);
                $leito->setSetor($data['setor']);
                $leito->setPino($pino);

                $this->entityManager->flush();

                $this->flashMessenger()->addSuccessMessage('Leito atualizado com sucesso!');
                return $this->redirect()->toRoute('leitos');
            }
        } else {
            $form->setData([
                'id' => $leito->getId(),
                'numero' => $leito->getNumero(),
                'setor' => $leito->getSetor(),
                'esp32' => $leito->getPino()->getEsp32()->getId(),
                'pino' => $leito->getPino()->getId(),
            ]);
        }

        $view = new ViewModel([
            'form' => $form,
            'title' => 'Cadastrar Leito',
            'id' => $id
        ]);

        $view->setTemplate('application/leito/form');

        return $view;
    }

    public function excluirAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        $leito = $this->entityManager->find(Leito::class, $id);

        if ($leito) {
            try {
                $this->entityManager->remove($leito);
                $this->entityManager->flush();
                $this->flashMessenger()->addSuccessMessage('Leito excluído com sucesso!');
            } catch (\Exception $e) {
                $this->flashMessenger()->addErrorMessage('Não foi possível excluir o leito. Detalhes: ' . $e->getMessage());
            }
        } else {
            $this->flashMessenger()->addErrorMessage('Leito não encontrado.');
        }

        return $this->redirect()->toRoute('leitos');
    }

    public function getPinosAction()
    {
        $esp32Id = (int) $this->params()->fromRoute('esp32_id', 0);
        $pinosArray = [];
        $statusCode = 200;

        if ($esp32Id > 0) {
            $pinoRepo = $this->entityManager->getRepository(Pino::class);
            $pinos = $pinoRepo->findAvailablePinos($esp32Id);

            foreach ($pinos as $pino) {
                $pinosArray[] = [
                    'id' => $pino->getId(),
                    'numero' => $pino->getNumeroPino(),
                ];
            }
        } else {
            $pinosArray = ['error' => 'ID do ESP32 inválido'];
            $statusCode = 400;
        }

        $response = $this->getResponse();
        $response->setStatusCode($statusCode);
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode($pinosArray));

        return $response;
    }
}