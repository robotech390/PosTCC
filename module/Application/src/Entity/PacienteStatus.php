<?php

namespace Application\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'paciente_status')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class PacienteStatus
{
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Paciente::class)]
    #[ORM\JoinColumn(name: 'paciente_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Paciente $paciente;

    #[ORM\Column(type: 'string', length: 50)]
    private string $evento;

    #[ORM\Column(name: 'data_registro', type: 'datetime')]
    private DateTime $dataRegistro;

    #[ORM\Column(name: 'timestamp_dispositivo', type: 'integer', nullable: true)]
    private ?int $timestampDispositivo = null;

    #[ORM\ManyToOne(targetEntity: Pino::class)]
    #[ORM\JoinColumn(name: 'pino_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Pino $pinoOrigem = null;

    #[ORM\PrePersist]
    public function setDataRegistroOnPrePersist(): void
    {
        if (! isset($this->dataRegistro)) {
            $this->dataRegistro = new DateTime();
        }
    }

    public function getId(): int
    {
        return $this->id;
    }
    public function getPaciente(): Paciente
    {
        return $this->paciente;
    }
    public function setPaciente(Paciente $paciente): void
    {
        $this->paciente = $paciente;
    }
    public function getEvento(): string
    {
        return $this->evento;
    }
    public function setEvento(string $evento): void
    {
        $this->evento = $evento;
    }
    public function getDataRegistro(): DateTime
    {
        return $this->dataRegistro;
    }

    public function setDataRegistro(DateTime $dataRegistro): void
    {
        $this->dataRegistro = $dataRegistro;
    }

    public function getTimestampDispositivo(): ?int
    {
        return $this->timestampDispositivo;
    }
    public function setTimestampDispositivo(?int $timestampDispositivo): void
    {
        $this->timestampDispositivo = $timestampDispositivo;
    }
    public function getPinoOrigem(): ?Pino
    {
        return $this->pinoOrigem;
    }
    public function setPinoOrigem(?Pino $pinoOrigem): void
    {
        $this->pinoOrigem = $pinoOrigem;
    }
}
