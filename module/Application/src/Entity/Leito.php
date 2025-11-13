<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'leito')]
#[ORM\Entity]
class Leito
{
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;


    #[ORM\Column(type: 'string', length: 50)]
    private string $numero;

    #[ORM\Column(type: 'string', length: 100)]
    private string $setor;

    #[ORM\OneToOne(inversedBy: 'leito', targetEntity: Pino::class)]
    #[ORM\JoinColumn(name: 'pino_id', referencedColumnName: 'id', unique: true, nullable: false, onDelete: 'RESTRICT')]
    private Pino $pino;

    #[ORM\OneToOne(targetEntity: Paciente::class, inversedBy: 'leito')]
    #[ORM\JoinColumn(name: 'paciente_id', referencedColumnName: 'id', nullable: true, unique: true, onDelete: 'SET NULL')]
    private ?Paciente $paciente = null;


    public function getId(): int
    {
        return $this->id;
    }

    public function getNumero(): string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): void
    {
        $this->numero = $numero;
    }

    public function getSetor(): string
    {
        return $this->setor;
    }

    public function setSetor(string $setor): void
    {
        $this->setor = $setor;
    }


    public function getPino(): Pino
    {
        return $this->pino;
    }

    public function setPino(Pino $pino): void
    {
        $this->pino = $pino;
    }

    public function getPaciente(): ?Paciente
    {
        return $this->paciente;
    }

    public function setPaciente(?Paciente $paciente): void
    {
        $this->paciente = $paciente;
    }
}