<?php

namespace Application\Form;

use Doctrine\ORM\EntityManager;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Form\Element;
use Application\Entity\Estado;

class EnderecoFieldset extends Fieldset implements InputFilterProviderInterface
{

    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {

        $this->entityManager = $entityManager;

        parent::__construct('endereco');
        $this->setLabel('Endereço');
    }

    public function init()
    {
        $this->add([
            'name' => 'rua',
            'type' => Element\Text::class,
            'options' => ['label' => 'Rua'],
            'attributes' => ['class' => 'form-control', 'required' => true],
        ]);

        $this->add([
            'name' => 'numero',
            'type' => Element\Text::class,
            'options' => ['label' => 'Número'],
            'attributes' => ['class' => 'form-control', 'required' => true],
        ]);

        $this->add([
            'name' => 'cep',
            'type' => Element\Text::class,
            'options' => ['label' => 'CEP'],
            'attributes' => ['class' => 'form-control', 'required' => true, 'data-mask' => 'cep'],
            'data-mask' => 'cep'
        ]);

        $this->add([
            'name' => 'estado',
            'type' => Element\Select::class,
            'options' => [
                'label' => 'Estado',
                'empty_option' => 'Selecione um Estado...',
                'value_options' => $this->getStateOptions(),
            ],
            'attributes' => ['class' => 'form-select', 'required' => true, 'id' => 'state-select'],
        ]);

        $this->add([
            'name' => 'cidade',
            'type' => Element\Select::class,
            'options' => [
                'label' => 'Cidade',
                'empty_option' => 'Selecione um Estado primeiro...',
                'value_options' => [],
            ],
            'attributes' => [
                'class' => 'form-select',
                'required' => true,
                'disabled' => true,
                'id' => 'city-select',
            ],
        ]);
    }

    private function getStateOptions(): array
    {
        $options = [];
        $estados = $this->entityManager->getRepository(Estado::class)->findBy([], ['nome' => 'ASC']);

        /** @var Estado $estado */
        foreach ($estados as $estado) {
            $options[$estado->getId()] = $estado->getNome();
        }

        return $options;
    }

    public function getInputFilterSpecification(): array
    {
        return [
            'rua' => ['required' => true],
            'numero' => ['required' => true],
            'cep' => ['required' => true],
            'estado' => ['required' => true],
            'cidade' => ['required' => true],
        ];
    }
}