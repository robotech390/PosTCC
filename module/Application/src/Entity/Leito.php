<?php
// Arquivo: module/Application/src/Entity/Leito.php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="leito")
 */
class Leito
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * Número/Identificador do leito (ex: "101-A").
     * @ORM\Column(type="string", length=50)
     */
    private string $numero;

    /**
     * Localização do leito (ex: "UTI Coronariana", "Enfermaria 3").
     * @ORM\Column(type="string", length=100)
     */
    private string $setor;

    /**
     * @ORM\OneToOne(targetEntity="Pino", inversedBy="leito")
     * @ORM\JoinColumn(name="pino_id", referencedColumnName="id", nullable=false, unique=true, onDelete="RESTRICT")
     */
    private Pino $pino;

    /**
     * @ORM\OneToOne(targetEntity="Paciente", inversedBy="leito")
     * @ORM\JoinColumn(name="paciente_id", referencedColumnName="id", nullable=true, unique=true, onDelete="SET NULL")
     */
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