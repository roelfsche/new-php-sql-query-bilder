<?php

namespace App\Entity\UsrWeb71;

use Doctrine\ORM\Mapping as ORM;

/**
 * CpaHistory
 *
 * @ORM\Table(name="cpa_history")
 * @ORM\Entity
 */
class CpaHistory
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="measure_date", type="datetime", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $measureDate;

    /**
     * @var bool
     *
     * @ORM\Column(name="cyl_no", type="boolean", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $cylNo = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="CDS_SerialNo", type="string", length=10, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $cdsSerialno = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="revolution", type="float", precision=5, scale=1, nullable=false, options={"default"="0.0"})
     */
    private $revolution = '0.0';

    /**
     * @var float
     *
     * @ORM\Column(name="scav_air", type="float", precision=10, scale=4, nullable=false, options={"default"="0.0000"})
     */
    private $scavAir = '0.0000';

    /**
     * @var float
     *
     * @ORM\Column(name="comp_pressure", type="float", precision=10, scale=2, nullable=false, options={"default"="0.00"})
     */
    private $compPressure = '0.00';

    /**
     * @var float
     *
     * @ORM\Column(name="max_pressure", type="float", precision=10, scale=2, nullable=false, options={"default"="0.00"})
     */
    private $maxPressure = '0.00';

    /**
     * @var float
     *
     * @ORM\Column(name="mean_ind_pressure", type="float", precision=10, scale=4, nullable=false, options={"default"="0.0000"})
     */
    private $meanIndPressure = '0.0000';

    /**
     * @var float
     *
     * @ORM\Column(name="ind_power", type="float", precision=10, scale=2, nullable=false, options={"default"="0.00"})
     */
    private $indPower = '0.00';

    public function getMeasureDate(): ?\DateTimeInterface
    {
        return $this->measureDate;
    }

    public function getCylNo(): ?bool
    {
        return $this->cylNo;
    }

    public function getCdsSerialno(): ?string
    {
        return $this->cdsSerialno;
    }

    public function getRevolution(): ?float
    {
        return $this->revolution;
    }

    public function setRevolution(float $revolution): self
    {
        $this->revolution = $revolution;

        return $this;
    }

    public function getScavAir(): ?float
    {
        return $this->scavAir;
    }

    public function setScavAir(float $scavAir): self
    {
        $this->scavAir = $scavAir;

        return $this;
    }

    public function getCompPressure(): ?float
    {
        return $this->compPressure;
    }

    public function setCompPressure(float $compPressure): self
    {
        $this->compPressure = $compPressure;

        return $this;
    }

    public function getMaxPressure(): ?float
    {
        return $this->maxPressure;
    }

    public function setMaxPressure(float $maxPressure): self
    {
        $this->maxPressure = $maxPressure;

        return $this;
    }

    public function getMeanIndPressure(): ?float
    {
        return $this->meanIndPressure;
    }

    public function setMeanIndPressure(float $meanIndPressure): self
    {
        $this->meanIndPressure = $meanIndPressure;

        return $this;
    }

    public function getIndPower(): ?float
    {
        return $this->indPower;
    }

    public function setIndPower(float $indPower): self
    {
        $this->indPower = $indPower;

        return $this;
    }


}
