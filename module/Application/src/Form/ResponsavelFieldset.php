<?php

namespace Application\Form;

use Laminas\Form\Element\Hidden;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Form\Element;
use Laminas\Filter;
use Laminas\Validator;
use Laminas\Validator\Regex;

class ResponsavelFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct($name = null, $options = [])
    {
        parent::__construct($name, $options);

        $this->setLabel('Dados do Responsável');
    }

    public function init()
    {

        $this->add([
            'name' => 'id',
            'type' => Hidden::class,
        ]);

        $this->add([
            'name' => 'nome',
            'type' => Element\Text::class,
            'options' => [
                'label' => 'Nome Completo do Responsável',
                'layout_options' => [
                    'wrapper_class' => 'col-12 mb-3'
                ]
            ],
            'attributes' => [
                'class'    => 'form-control',
                'required' => true,
            ],
        ]);

        $this->add([
            'name' => 'rg',
            'type' => Element\Text::class,
            'options' => [
                'label' => 'RG',
                'layout_options' => [
                    'wrapper_class' => 'col-md-6 mb-3'
                ]
            ],
            'attributes' => [
                'class' => 'form-control',
                'data-mask' => 'rg',
            ],
        ]);

        $this->add([
            'name' => 'cpf',
            'type' => Element\Text::class,
            'options' => [
                'label' => 'CPF',
                'layout_options' => [
                    'wrapper_class' => 'col-md-6 mb-3'
                ]
            ],
            'attributes' => [
                'class'    => 'form-control',
                'required' => true,
                'data-mask' => 'cpf',
            ],
        ]);

        $this->add([
            'name' => 'telefone',
            'type' => Element\Text::class,
            'options' => [
                'label' => 'Telefone de Contato',
                'layout_options' => [
                    'wrapper_class' => 'col-12 mb-3'
                ]
            ],
            'attributes' => [
                'class'    => 'form-control',
                'data-mask' => 'phone',
                'required' => true,
            ],
        ]);
    }

    public function getInputFilterSpecification() :array
    {
        return [
            'nome' => [
                'required' => true,
                'filters'  => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\StringLength::class,
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min'      => 3,
                            'max'      => 100,
                        ],
                    ],
                ],
            ],

            'cpf' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'Regex',
                        'options' => [
                            'pattern' => '/^\d{3}\.\d{3}\.\d{3}-\d{2}$/',
                            'messages' => [
                                Regex::NOT_MATCH => 'O CPF deve estar no formato 000.000.000-00.',
                            ],
                        ],
                    ],
                ],
            ],
            'telefone' => [
                'required' => true,
                'filters'  => [
                    ['name' => Filter\Digits::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\StringLength::class,
                        'options' => [
                            'min' => 10,
                            'max' => 11,
                        ],
                    ],
                ],
            ],
        ];
    }
}