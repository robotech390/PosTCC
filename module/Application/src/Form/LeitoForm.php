<?php
namespace Application\Form;

use Doctrine\ORM\EntityManager;
use Laminas\Form\Form;
use Laminas\Form\Element;
use Application\Entity\ESP32;

class LeitoForm extends Form
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct('leito_form');


        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'needs-validation');
        $this->setAttribute('novalidate', true);

        $this->add([
            'name' => 'id',
            'type' => Element\Hidden::class,
        ]);

        $this->add([
            'name' => 'numero',
            'type' => Element\Text::class,
            'options' => ['label' => 'Número do Leito'],
            'attributes' => ['class' => 'form-control', 'required' => true],
        ]);

        $this->add([
            'name' => 'setor',
            'type' => Element\Text::class,
            'options' => ['label' => 'Setor/Ala'],
            'attributes' => ['class' => 'form-control', 'required' => true],
        ]);

        $this->add([
            'name' => 'esp32',
            'type' => Element\Select::class,
            'options' => [
                'label' => 'Dispositivo Associado',
                'empty_option' => 'Selecione um dispositivo...',
                'value_options' => $this->getEsp32Options(),
            ],
            'attributes' => ['class' => 'form-select', 'required' => true, 'id' => 'esp32-select'],
        ]);

        $this->add([
            'name' => 'pino',
            'type' => Element\Select::class,
            'options' => [
                'label' => 'Pino Registrado',
                'empty_option' => 'Selecione um dispositivo primeiro...',
                'value_options' => [],
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'class' => 'form-select',
                'required' => true,
                'id' => 'pino-select',
                'disabled' => true,
            ],
        ]);

        $this->add([
            'name' => 'submit',
            'type' => Element\Submit::class,
            'attributes' => ['value' => 'Salvar Leito', 'class' => 'btn btn-success mt-3'],
        ]);
    }


    private function getEsp32Options(): array
    {
        $options = [];
        $dispositivos = $this->entityManager->getRepository(ESP32::class)
            ->findBy([], ['nomeAmigavel' => 'ASC', 'macAddress' => 'ASC']);

        /** @var ESP32 $esp */
        foreach ($dispositivos as $esp) {
            $friendlyName = $esp->getNomeAmigavel()
                ? sprintf('%s (%s)', $esp->getNomeAmigavel(), $esp->getMacAddress())
                : $esp->getMacAddress();

            $options[$esp->getId()] = $friendlyName;
        }
        return $options;
    }

    public function getInputFilterSpecification(): array
    {
        return [
            'id' => ['required' => false],
            'numero' => ['required' => true],
            'setor' => ['required' => true],
            'esp32' => ['required' => true],
            'pino' => ['required' => true],
        ];
    }
}