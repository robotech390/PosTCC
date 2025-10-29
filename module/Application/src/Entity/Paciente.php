<?php

namespace Application\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity
 * @ORM\Table(name="paciente")
 */
class Paciente
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\OneToOne(targetEntity="Application\Entity\Pessoa", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="pessoa_id", referencedColumnName="id", nullable=false)
     */
    private Pessoa $pessoa;


    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Endereco")
     * @ORM\JoinColumn(name="endereco_id", referencedColumnName="id", nullable=false)
     */
    private Endereco $endereco;

    /**
     * @ORM\ManyToMany(targetEntity="Pessoa", cascade={"persist"})
     * @ORM\JoinTable(name="paciente_responsaveis",
     * joinColumns={@ORM\JoinColumn(name="paciente_id", referencedColumnName="id")}, inverseJoinColumns={@ORM\JoinColumn(name="responsavel_pessoa_id", referencedColumnName="id")})
     */
    private Collection $responsaveis;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private \DateTime $nascimento;

    /**
     * @ORM\OneToOne(targetEntity="Leito", mappedBy="paciente")
     */
    private ?Leito $leito = null;

    public function __construct()
    {
        $this->responsaveis = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPessoa(): Pessoa
    {
        return $this->pessoa;
    }

    public function setPessoa(Pessoa $pessoa): void
    {
        $this->pessoa = $pessoa;
    }

    public function getEndereco(): Endereco
    {
        return $this->endereco;
    }

    public function setEndereco(Endereco $endereco): void
    {
        $this->endereco = $endereco;
    }

    public function getResponsaveis(): Collection
    {
        return $this->responsaveis;
    }

    public function getLeito(): ?Leito
    {
        return $this->leito;
    }

    public function setLeito(Leito $leito): void
    {
        $this->leito = $leito;
    }

    public function addResponsavel(Pessoa $pessoa): void
    {
        if (! $this->responsaveis->contains($pessoa)) {
            $this->responsaveis->add($pessoa);
        }
    }

    public function removeResponsavel(Pessoa $pessoa): void
    {
        $this->responsaveis->removeElement($pessoa);
    }

}