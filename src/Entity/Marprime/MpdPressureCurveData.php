<?php

namespace App\Entity\Marprime;

use Doctrine\ORM\Mapping as ORM;

/**
 * MpdPressureCurveData
 *
 * @ORM\Table(name="mpd_pressure_curve_data")
 * @ORM\Entity(repositoryClass="App\Repository\Marprime\MpdPressureCurveDataRepository")
 */
class MpdPressureCurveData
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $date;

    /**
     * @var bool
     *
     * @ORM\Column(name="cyl_no", type="boolean", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $cylNo;

    /**
     * @var int
     *
     * @ORM\Column(name="x_val", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $xVal;

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
     * @ORM\Column(name="MeasurementTime", type="datetime", nullable=false)
     */
    private $measurementtime;

    /**
     * @var float
     *
     * @ORM\Column(name="y_val", type="float", precision=10, scale=0, nullable=false)
     */
    private $yVal;

    /**
     * @var float
     *
     * @ORM\Column(name="revolution", type="float", precision=10, scale=3, nullable=false)
     */
    private $revolution;

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function getCylNo(): ?bool
    {
        return $this->cylNo;
    }

    public function getXVal(): ?int
    {
        return $this->xVal;
    }

    public function getMarprimeSerialno(): ?string
    {
        return $this->marprimeSerialno;
    }

    public function getMeasurementtime(): ?\DateTimeInterface
    {
        return $this->measurementtime;
    }

    public function setMeasurementtime(\DateTimeInterface $measurementtime): self
    {
        $this->measurementtime = $measurementtime;

        return $this;
    }

    public function getYVal(): ?float
    {
        return $this->yVal;
    }

    public function setYVal(float $yVal): self
    {
        $this->yVal = $yVal;

        return $this;
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


}
