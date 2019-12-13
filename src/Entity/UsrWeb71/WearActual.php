<?php

namespace App\Entity\UsrWeb71;

use Doctrine\ORM\Mapping as ORM;

/**
 * WearActual
 *
 * @ORM\Table(name="wear_actual", indexes={@ORM\Index(name="id", columns={"id"})})
 * @ORM\Entity
 */
class WearActual
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="ship_table_id", type="integer", nullable=false)
     */
    private $shipTableId = '0';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="measure_date", type="date", nullable=true)
     */
    private $measureDate;

    /**
     * @var bool
     *
     * @ORM\Column(name="cyl_no", type="boolean", nullable=false)
     */
    private $cylNo = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="wear_reserve", type="boolean", nullable=false)
     */
    private $wearReserve = '0';

    /**
     * @var float|null
     *
     * @ORM\Column(name="startwert", type="float", precision=10, scale=0, nullable=true)
     */
    private $startwert;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShipTableId(): ?int
    {
        return $this->shipTableId;
    }

    public function setShipTableId(int $shipTableId): self
    {
        $this->shipTableId = $shipTableId;

        return $this;
    }

    public function getMeasureDate(): ?\DateTimeInterface
    {
        return $this->measureDate;
    }

    public function setMeasureDate(?\DateTimeInterface $measureDate): self
    {
        $this->measureDate = $measureDate;

        return $this;
    }

    public function getCylNo(): ?bool
    {
        return $this->cylNo;
    }

    public function setCylNo(bool $cylNo): self
    {
        $this->cylNo = $cylNo;

        return $this;
    }

    public function getWearReserve(): ?bool
    {
        return $this->wearReserve;
    }

    public function setWearReserve(bool $wearReserve): self
    {
        $this->wearReserve = $wearReserve;

        return $this;
    }

    public function getStartwert(): ?float
    {
        return $this->startwert;
    }

    public function setStartwert(?float $startwert): self
    {
        $this->startwert = $startwert;

        return $this;
    }


}
