<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="pessoa")
 */
class Pessoa
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $nome;

    /**
     * @ORM\Column(type="string", length=14, unique=true)
     */
    private string $cpf;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private \DateTime $nascimento;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private ?string $rg;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $telefone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $foto = null;

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

    public function getCpf(): string
    {
        return $this->cpf;
    }

    public function setCpf(string $cpf): void
    {
        $this->cpf = $cpf;
    }

    public function getNascimento(): \DateTime
    {
        return $this->nascimento;
    }

    public function setNascimento(\DateTime $nascimento): void
    {
        $this->nascimento = $nascimento;
    }

    public function getRg(): ?string
    {
        return $this->rg;
    }

    public function setRg(?string $rg): void
    {
        $this->rg = $rg;
    }

    public function getFoto(): ?string
    {
        return $this->foto;
    }

    public function setFoto(?string $foto): void
    {
        $this->foto = $foto;
    }

    public function getTelefone(): string
    {
        return $this->telefone;
    }

    public function setTelefone(string $telefone): void
    {
        $this->telefone = $telefone;
    }

}