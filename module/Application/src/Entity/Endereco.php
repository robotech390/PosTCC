<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'endereco')]
#[ORM\Entity]
class Endereco
{
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $rua;

    #[ORM\Column(type: 'string', length: 10)]
    private string $numero;

    #[ORM\Column(type: 'string', length: 9)]
    private string $cep;

    #[ORM\ManyToOne(targetEntity: Cidade::class)]
    #[ORM\JoinColumn(name: 'cidade_id', referencedColumnName: 'id', nullable: false)]
    private Cidade $cidade;

    #[ORM\ManyToOne(targetEntity: Estado::class)]
    #[ORM\JoinColumn(name: 'estado_id', referencedColumnName: 'id', nullable: false)]
    private Estado $estado;

    // Getters and Setters
    public function getId(): int
    {
        return $this->id;
    }

    public function getRua(): string
    {
        return $this->rua;
    }

    public function setRua(string $rua): void
    {
        $this->rua = $rua;
    }

    public function getNumero(): string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): void
    {
        $this->numero = $numero;
    }

    public function getCep(): string
    {
        return $this->cep;
    }

    public function setCep(string $cep): void
    {
        $this->cep = $cep;
    }

    public function getCidade(): Cidade
    {
        return $this->cidade;
    }

    public function setCidade(Cidade $cidade): void
    {
        $this->cidade = $cidade;
    }

    public function getEstado(): Estado
    {
        return $this->estado;
    }

    public function setEstado(Estado $estado): void
    {
        $this->estado = $estado;
    }
}