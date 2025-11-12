<?php

namespace Application\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'esp32')]
#[ORM\Entity]
class ESP32
{
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(name: 'mac_address', type: 'string', length: 17, unique: true)]
    private string $macAddress;

    #[ORM\Column(name: 'nome_amigavel', type: 'string', length: 100, nullable: true)]
    private ?string $nomeAmigavel = null;

    #[ORM\OneToMany(mappedBy: 'esp32', targetEntity: Pino::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $pinos;

    public function __construct()
    {
        $this->pinos = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMacAddress(): string
    {
        return $this->macAddress;
    }

    public function setMacAddress(string $macAddress): void
    {
        $this->macAddress = $macAddress;
    }

    public function getNomeAmigavel(): ?string
    {
        return $this->nomeAmigavel;
    }

    public function setNomeAmigavel(?string $nomeAmigavel): void
    {
        $this->nomeAmigavel = $nomeAmigavel;
    }

    /** @return Collection|Pino[] */
    public function getPinos(): Collection
    {
        return $this->pinos;
    }

    public function addPino(Pino $pino): void
    {
        if (! $this->pinos->contains($pino)) {
            $this->pinos->add($pino);
            $pino->setEsp32($this);
        }
    }

    public function removePino(Pino $pino): void
    {
        if ($this->pinos->contains($pino)) {
            $this->pinos->removeElement($pino);
        }
    }
}
