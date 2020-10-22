<?php

namespace App\Entity\Marprime;

use Doctrine\ORM\Mapping as ORM;

/**
 * MpdHistory
 *
 * @ORM\Table(name="mpd_history")
 * @ORM\Entity
 */
class MpdHistory
{
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
     * @ORM\Column(name="MarPrime_SerialNo", type="string", length=20, nullable=false, options={"default"="-1"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $marprimeSerialno = '-1';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    private $date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="MeasurementTime", type="datetime", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $measurementtime;

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

    /**
     * @var float
     *
     * @ORM\Column(name="angle_pmax", type="float", precision=10, scale=0, nullable=false)
     */
    private $anglePmax = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="pcomp_rel_pscav", type="float", precision=10, scale=0, nullable=false)
     */
    private $pcompRelPscav = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="leakage", type="float", precision=10, scale=0, nullable=false)
     */
    private $leakage = '0';

    public function getCylNo(): ?bool
    {
        return $this->cylNo;
    }

    public function getMarprimeSerialno(): ?string
    {
        return $this->marprimeSerialno;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getMeasurementtime(): ?\DateTimeInterface
    {
        return $this->measurementtime;
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

    public function getAnglePmax(): ?float
    {
        return $this->anglePmax;
    }

    public function setAnglePmax(float $anglePmax): self
    {
        $this->anglePmax = $anglePmax;

        return $this;
    }

    public function getPcompRelPscav(): ?float
    {
        return $this->pcompRelPscav;
    }

    public function setPcompRelPscav(float $pcompRelPscav): self
    {
        $this->pcompRelPscav = $pcompRelPscav;

        return $this;
    }

    public function getLeakage(): ?float
    {
        return $this->leakage;
    }

    public function setLeakage(float $leakage): self
    {
        $this->leakage = $leakage;

        return $this;
    }


}
