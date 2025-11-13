<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'cidade')]
#[ORM\Entity]
class Cidade
{
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(name: 'nome', type: 'text', length: 100, nullable: false)]
    private string $nome;

    #[ORM\ManyToOne(targetEntity: Estado::class)]
    #[ORM\JoinColumn(name: 'estado_id', referencedColumnName: 'id', nullable: false)]
    private Estado $estado;

    public function getId(): int
    {
        return $this->id;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function setNome(string $nome): void
    {
        $this->nome = $nome;
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