<?php

namespace Application\Controller;

use Application\Entity\Cidade;
use Application\Entity\Endereco;
use Application\Entity\Estado;
use Application\Entity\Leito;
use Application\Entity\Paciente;
use Application\Entity\Pessoa;
use Application\Form\PessoaForm;
use Doctrine\ORM\EntityManager;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class PacienteController extends AbstractActionController
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    public function listarAction()
    {
        $pacientes = $this->entityManager->getRepository(Paciente::class)->findAll();

        return new ViewModel([
            'pacientes' => $pacientes,
        ]);
    }

    public function cadastrarAction()
    {

        $form = new PessoaForm($this->entityManager);

        $request = $this->getRequest();

        if ($request->isPost()) {
            $postData = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            $form->setData($postData);

            if ($form->isValid()) {
                $data = $form->getData();

                $pessoaExistente = $this->entityManager->getRepository(Pessoa::class)->findOneBy(['cpf' => $data['cpf']]);

                if ($pessoaExistente) {
                    $this->flashMessenger()->addErrorMessage('Já existe um pacientes cadastrado com esse CPF!');
                    return $this->redirect()->toUrl('/application/cadastrar');
                }

                $fotoData = $data['foto'];
                if (! empty($fotoData['tmp_name'])) {
                    $uploadPath = 'public/uploads/';
                    $extension = pathinfo($fotoData['name'], PATHINFO_EXTENSION);
                    $newFileName = uniqid('paciente_') . '.' . $extension;
                    $filePath = $uploadPath . $newFileName;

                    move_uploaded_file($fotoData['tmp_name'], $filePath);

                    $data['foto'] = $newFileName;
                } else {
                    $data['foto'] = null;
                }

                $nascimento = \DateTime::createFromFormat('d/m/Y', $data['nascimento']);

                $pessoa = new Pessoa();
                $pessoa->setNome($data['nome']);
                $pessoa->setNascimento($nascimento);
                $pessoa->setCpf($data['cpf']);
                $pessoa->setRg($data['rg']);
                $pessoa->setTelefone($data['telefone']);
                $pessoa->setFoto($data['foto']);
                $this->entityManager->persist($pessoa);

                $enderecoData = $data['endereco'];

                $estado = $this->entityManager->find(Estado::class, $enderecoData['estado']);
                $cidade = $this->entityManager->find(Cidade::class, $enderecoData['cidade']);

                $endereco = new Endereco();
                $endereco->setRua($enderecoData['rua']);
                $endereco->setNumero($enderecoData['numero']);
                $endereco->setCep($enderecoData['cep']);

                if ($cidade) {
                    $endereco->setCidade($cidade);
                }
                if ($estado) {
                    $endereco->setEstado($estado);
                }

                $this->entityManager->persist($endereco);

                $paciente = new Paciente();
                $paciente->setPessoa($pessoa);
                $paciente->setEndereco($endereco);

                $leitoId = $data['leito'] ?? null;
                if ($leitoId) {

                    $leitoSelecionado = $this->entityManager->find(Leito::class, $leitoId);

                    if ($leitoSelecionado && $leitoSelecionado->getPaciente() === null) {
                        $paciente->setLeito($leitoSelecionado);
                        $leitoSelecionado->setPaciente($paciente);

                    } else {
                        $this->flashMessenger()->addWarningMessage('O leito selecionado não estava disponível. O paciente foi cadastrado sem leito.');
                    }
                }

                foreach ($data['responsaveis'] as $respData) {
                    $cpfLimpo = preg_replace('/[^0-9]/', '', $respData['cpf']);

                    if (empty($cpfLimpo)) {
                        continue;
                    }

                    $pessoaResponsavel = $this->entityManager->getRepository(Pessoa::class)->findOneBy(['cpf' => $cpfLimpo]);

                    if ($pessoaResponsavel === null) {
                        $pessoaResponsavel = new Pessoa();
                        $pessoaResponsavel->setNome($respData['nome']);
                        $pessoaResponsavel->setCpf($cpfLimpo);
                        $pessoaResponsavel->setRg($respData['rg']);
                        $pessoaResponsavel->setTelefone(preg_replace('/[^0-9]/', '', $respData['telefone']));

                        $this->entityManager->persist($pessoaResponsavel);
                    }

                    $paciente->addResponsavel($pessoaResponsavel);
                }

                $this->entityManager->persist($paciente);

                $this->entityManager->flush();

                $this->flashMessenger()->addSuccessMessage('Paciente cadastrado com sucesso!');
                return $this->redirect()->toUrl('/paciente');
            }
        }

        $viewModel = new ViewModel([
            'form' => $form,
            'id' => 0,
            'title' => 'Cadastro de Paciente',
        ]);

        $viewModel->setTemplate('application/paciente/form');

        return $viewModel;
    }

    public function editarAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if ($id === 0) {
            $this->flashMessenger()->addErrorMessage('ID do paciente inválido.');
            return $this->redirect()->toRoute('pacientes');
        }

        $paciente = $this->entityManager->find(Paciente::class, $id);
        if (!$paciente) {
            $this->flashMessenger()->addErrorMessage('Paciente não encontrado.');
            return $this->redirect()->toRoute('pacientes');
        }

        $form = new PessoaForm($this->entityManager);

        $request = $this->getRequest();
        if ($request->isPost()) {

            $postData = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            $form->setData($postData);

            if ($form->isValid()) {
                $data = $form->getData();

                $fotoData = $data['foto'];
                if (!empty($fotoData['tmp_name'])) {
                    $uploadPath = 'public/uploads/';

                    $oldPhoto = $paciente->getPessoa()->getFoto();

                    $extension = pathinfo($fotoData['name'], PATHINFO_EXTENSION);
                    $newFileName = uniqid('paciente_') . '.' . $extension;
                    move_uploaded_file($fotoData['tmp_name'], $uploadPath . $newFileName);

                    $paciente->getPessoa()->setFoto($newFileName);

                    if ($oldPhoto && $oldPhoto !== 'default_img.jpeg' && file_exists($uploadPath . $oldPhoto)) {
                        unlink($uploadPath . $oldPhoto);
                    }
                }

                $nascimento = \DateTime::createFromFormat('d/m/Y', $data['nascimento']);

                $pessoa = $paciente->getPessoa();
                $pessoa->setNome($data['nome']);
                $pessoa->setNascimento($nascimento);
                $pessoa->setCpf($data['cpf']);
                $pessoa->setRg($data['rg']);
                $pessoa->setTelefone($data['telefone']);

                $enderecoData = $data['endereco'];

                $estado = $this->entityManager->find(Estado::class, $enderecoData['estado']);
                $cidade = $this->entityManager->find(Cidade::class, $enderecoData['cidade']);

                $endereco = $paciente->getEndereco();
                $endereco->setRua($enderecoData['rua']);
                $endereco->setNumero($enderecoData['numero']);
                $endereco->setCep($enderecoData['cep']);

                if ($cidade) {
                    $endereco->setCidade($cidade);
                }
                if ($estado) {
                    $endereco->setEstado($estado);
                }

                $novoLeitoId = $data['leito'] ?? null;
                $leitoAtual = $paciente->getLeito();
                $novoLeito = null;

                if ($novoLeitoId) {
                    $novoLeito = $this->entityManager->find(Leito::class, $novoLeitoId);
                }

                if (($leitoAtual ? $leitoAtual->getId() : null) !== ($novoLeito ? $novoLeito->getId() : null))
                {
                    if ($leitoAtual) {
                        $leitoAtual->setPaciente(null);
                    }

                    if ($novoLeito) {
                        if ($novoLeito->getPaciente() === null) {
                            $paciente->setLeito($novoLeito);
                            $novoLeito->setPaciente($paciente);
                        } else {
                            $paciente->setLeito(null);
                            $this->flashMessenger()->addWarningMessage("O leito selecionado ({$novoLeito->getNumero()}) foi ocupado por outro paciente. Alteração de leito não realizada.");
                        }
                    } else {
                        $paciente->setLeito(null);
                    }
                }

                $paciente->getResponsaveis()->clear();

                foreach ($data['responsaveis'] as $respData) {
                    $cpfLimpo = preg_replace('/[^0-9]/', '', $respData['cpf']);

                    if (empty($cpfLimpo)) {
                        continue;
                    }

                    $pessoaResponsavel = $this->entityManager->getRepository(Pessoa::class)->findOneBy(['cpf' => $cpfLimpo]);

                    if ($pessoaResponsavel === null) {
                        $pessoaResponsavel = new Pessoa();
                        $pessoaResponsavel->setNome($respData['nome']);
                        $pessoaResponsavel->setCpf($cpfLimpo);
                        $pessoaResponsavel->setRg($respData['rg']);
                        $pessoaResponsavel->setTelefone(preg_replace('/[^0-9]/', '', $respData['telefone']));

                        $this->entityManager->persist($pessoaResponsavel);
                    }

                    $paciente->addResponsavel($pessoaResponsavel);
                }

                $this->entityManager->flush();

                $this->flashMessenger()->addSuccessMessage('Paciente atualizado com sucesso!');
                return $this->redirect()->toRoute('paciente');
            }
        } else {
            $pessoaData = [
                'id' => $paciente->getId(),
                'nome' => $paciente->getPessoa()->getNome(),
                'nascimento' => $paciente->getPessoa()->getNascimento()->format('d/m/Y'),
                'cpf' => $paciente->getPessoa()->getCpf(),
                'rg' => $paciente->getPessoa()->getRg(),
                'telefone' => $paciente->getPessoa()->getTelefone(),
            ];

            $enderecoData = [
                'cep' => $paciente->getEndereco()->getCep(),
                'rua' => $paciente->getEndereco()->getRua(),
                'numero' => $paciente->getEndereco()->getNumero(),
                'estado' => $paciente->getEndereco()->getEstado()->getId(),
                'cidade' => $paciente->getEndereco()->getCidade()->getId(),
            ];

            $responsaveisData = [];
            foreach ($paciente->getResponsaveis() as $responsavel) {
                $responsaveisData[] = [
                    'nome' => $responsavel->getNome(),
                    'cpf' => $responsavel->getCpf(),
                    'rg' => $responsavel->getRg(),
                    'telefone' => $responsavel->getTelefone(),
                ];
            }

            $leitoIdAtual = $paciente->getLeito() ? $paciente->getLeito()->getId() : null;

            $formData = array_merge(
                $pessoaData,
                ['endereco' => $enderecoData],
                ['responsaveis' => $responsaveisData],
                ['leito' => $leitoIdAtual]
            );

            $form->setData($formData);

            if ($leitoAtual = $paciente->getLeito()) {

                $leitoSelect = $form->get('leito');
                $options = $leitoSelect->getValueOptions();

                if (!isset($options[$leitoAtual->getId()])) {
                    $options[$leitoAtual->getId()] = sprintf('%s - %s (Atual)', $leitoAtual->getSetor(), $leitoAtual->getNumero());
                    uksort($options, function($a, $b) use ($options) { return strnatcmp($options[$a], $options[$b]); });
                    $leitoSelect->setValueOptions($options);
                }
            }
        }

        $viewModel = new ViewModel([
            'form' => $form,
            'id' => $id,
            'title' => 'Editar Paciente',
            'paciente' => $paciente,
        ]);

        $viewModel->setTemplate('application/paciente/form');

        return $viewModel;
    }

    public function excluirAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        $paciente = $this->entityManager->find(Paciente::class, $id);

        if ($paciente) {
            try {
                $this->entityManager->remove($paciente);
                $this->entityManager->flush();

                $this->flashMessenger()->addSuccessMessage('Paciente excluído com sucesso!');
            } catch (\Exception $e) {
                $this->flashMessenger()->addErrorMessage('Não foi possível excluir o paciente.');
            }
        } else {
            $this->flashMessenger()->addErrorMessage('Paciente não encontrado.');
        }

        return $this->redirect()->toRoute('paciente');
    }
}