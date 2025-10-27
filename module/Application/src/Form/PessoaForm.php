<?php

namespace Application\Form;

use Doctrine\ORM\EntityManager;
use Laminas\Form\Element\Collection;
use Laminas\Form\Element\Email;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\Regex;

class PessoaForm extends Form implements InputFilterProviderInterface
{

    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager, $name = null)
    {

        $this->entityManager = $entityManager;

        parent::__construct('');

        $this->setAttributes([
            'class' => 'needs-validation',
            'novalidate' => true,
            'method' => 'post',
            'enctype' => 'multipart/form-data',
        ]);

        $this->add([
            'name' => 'id',
            'type' => Hidden::class,
        ]);

        $this->add([
            'name' => 'nome',
            'type' => Text::class,
            'options' => [
                'label' => 'Nome',
            ],
            'attributes' => [
                'required' => true,
                'class' => 'form-control',
                'placeholder' => 'Digite o nome completo...',
            ],
        ]);

        $this->add([
            'name' => 'nascimento',
            'type' => Text::class,
            'options' => [
                'label' => 'Data de Nascimento',
            ],
            'attributes' => [
                'required' => true,
                'class' => 'form-control',
                'placeholder' => '99/99/9999',
                'id' => 'patient-birthdate',
                'data-mask' => 'date',
            ],
        ]);

        $this->add([
            'name' => 'cpf',
            'type' => Text::class,
            'options' => [
                'label' => 'CPF',
            ],
            'attributes' => [
                'required' => true,
                'class' => 'form-control',
                'placeholder' => '000.000.000-00',
                'id' => 'patient-cpf',
                'data-mask' => 'cpf',
            ],
        ]);

        $this->add([
            'name' => 'rg',
            'type' => Text::class,
            'options' => [
                'label' => 'RG',
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => '000000000',
                'id' => 'patient-rg',
                'data-mask' => 'rg',
            ],
        ]);

        $this->add([
            'name' => 'telefone',
            'type' => Text::class,
            'options' => [
                'label' => 'Telefone',
            ],
            'attributes' => [
                'required' => false,
                'class' => 'form-control',
                'id' => 'patient-phone',
                'data-mask' => 'phone',
            ],
        ]);

        $this->add([
            'name' => 'foto',
            'type' => File::class,
            'options' => [
                'label' => 'Foto',
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $enderecoFieldset = new EnderecoFieldset($this->entityManager);
        $enderecoFieldset->init();
        $this->add($enderecoFieldset);

        $this->add([
            'type' => Collection::class,
            'name' => 'responsaveis',
            'options' => [
                'label' => 'Responsáveis do Paciente',
                'count' => 0,
                'allow_add' => true,
                'should_create_template' => true,
                'target_element' => [
                    'type' => ResponsavelFieldset::class,
                ],
            ],
        ]);

        $this->add([
            'name' => 'submit',
            'type' => Submit::class,
            'attributes' => [
                'value' => 'Cadastrar',
                'class' => 'btn btn-success',
            ],
        ]);
    }

    public function getInputFilterSpecification(): array
    {
        return [
            'nome' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ],
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages' => [
                                NotEmpty::IS_EMPTY => 'O nome é obrigatório.',
                            ],
                        ],
                    ],
                ],
            ],

            'nascimento' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => \Laminas\Validator\Date::class,
                        'options' => [
                            'format' => 'd/m/Y',
                            'messages' => [
                                \Laminas\Validator\Date::INVALID_DATE => 'A data de nascimento não é válida.',
                            ],
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
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
            ],
        ];
    }
}
