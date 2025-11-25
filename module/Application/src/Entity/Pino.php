<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'pino')]
#[ORM\Entity(repositoryClass: 'Application\Repository\PinoRepository')]
class Pino
{
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: ESP32::class, inversedBy: 'pinos')]
    #[ORM\JoinColumn(name: 'esp32_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ESP32 $esp32;

    #[ORM\Column(name: 'numero_pino', type: 'integer')]
    private int $numeroPino;

    #[ORM\OneToOne(mappedBy: 'pino', targetEntity: Leito::class)]
    private ?Leito $leito = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getEsp32(): ESP32
    {
        return $this->esp32;
    }

    public function setEsp32(ESP32 $esp32): void
    {
        $this->esp32 = $esp32;
    }

    public function getNumeroPino(): int
    {
        return $this->numeroPino;
    }

    public function setNumeroPino(int $numeroPino): void
    {
        $this->numeroPino = $numeroPino;
    }

    public function getLeito(): ?Leito
    {
        return $this->leito;
    }
}
