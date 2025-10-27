<?php
// Arquivo: module/Application/src/Entity/ESP32.php
namespace Application\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="esp32", uniqueConstraints={@ORM\UniqueConstraint(name="mac_address_idx", columns={"mac_address"})})
 */
class ESP32
{
    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=17, unique=true, name="mac_address")
     */
    private string $macAddress;

    /**
     * @ORM\Column(type="string", length=100, name="nome_amigavel", nullable=true)
     */
    private ?string $nomeAmigavel = null;

    /**
     * @ORM\OneToMany(targetEntity="Pino", mappedBy="esp32", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private Collection $pinos;

    public function __construct()
    {
        $this->pinos = new ArrayCollection();
    }

    public function getId(): int {
        return $this->id;
    }

    public function getMacAddress(): string {
        return $this->macAddress;
    }

    public function setMacAddress(string $macAddress): void {
        $this->macAddress = $macAddress;
    }

    public function getNomeAmigavel(): ?string {
        return $this->nomeAmigavel;
    }

    public function setNomeAmigavel(?string $nomeAmigavel): void {
        $this->nomeAmigavel = $nomeAmigavel;
    }

    /** @return Collection|Pino[] */
    public function getPinos(): Collection
    {
        return $this->pinos;
    }

    public function addPino(Pino $pino): void
    {
        if (!$this->pinos->contains($pino)) {
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