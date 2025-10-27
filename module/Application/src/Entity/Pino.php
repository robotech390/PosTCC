<?php
// Arquivo: module/Application/src/Entity/Pino.php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="pino", uniqueConstraints={
 * @ORM\UniqueConstraint(name="esp32_num_pino_idx", columns={"esp32_id", "numero_pino"})
 * })
 */
class Pino
{
    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="ESP32", inversedBy="pinos")
     * @ORM\JoinColumn(name="esp32_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private ESP32 $esp32;

    /**
     * @ORM\Column(type="integer", name="numero_pino")
     */
    private int $numeroPino;

    /**
     * @ORM\OneToOne(targetEntity="Leito", mappedBy="pino")
     */
    private ?Leito $leito = null;

    public function getId(): int {
        return $this->id;
    }

    public function getEsp32(): ESP32 {
        return $this->esp32;
    }

    public function setEsp32(ESP32 $esp32): void {
        $this->esp32 = $esp32;
    }

    public function getNumeroPino(): int {
        return $this->numeroPino;
    }

    public function setNumeroPino(int $numeroPino): void {
        $this->numeroPino = $numeroPino;
    }

    public function getLeito(): ?Leito {
        return $this->leito;
    }
}