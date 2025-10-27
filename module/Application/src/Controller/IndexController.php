<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Entity\Cidade;
use Application\Entity\Endereco;
use Application\Entity\Estado;
use Application\Entity\Paciente;
use Application\Entity\Pessoa;
use Application\Entity\Responsavel;
use Application\Form\PessoaForm;
use Laminas\Json\Json;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }

    public function getCidadesAction()
    {
        $idEstado = (int) $this->params()->fromRoute('id_estado', 0);

        $cidadesArray = [];
        $statusCode = 200;
        if ($idEstado > 0) {
            /** @var \Doctrine\ORM\EntityManager $em */
            $em = $this->getEvent()->getApplication()->getServiceManager()->get('doctrine.entitymanager.orm_default');

            $cidadesRepo = $em->getRepository(Cidade::class);
            $cidades = $cidadesRepo->findBy(['estado' => $idEstado], ['nome' => 'ASC']);

            foreach ($cidades as $cidade) {
                $cidadesArray[] = [
                    'id' => $cidade->getId(),
                    'nome' => $cidade->getNome(),
                ];
            }
        } else {
            $cidadesArray = ['error' => 'ID do estado inválido'];
            $statusCode = 400;
        }

        $response = $this->getResponse();
        $response->setStatusCode($statusCode);
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode($cidadesArray));

        return $response;
    }
}
