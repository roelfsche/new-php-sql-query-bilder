<?php

namespace App\Entity\UsrWeb71;

use Doctrine\ORM\Mapping as ORM;

/**
 * CpaCurveData
 *
 * @ORM\Table(name="cpa_curve_data")
 * @ORM\Entity
 */
class CpaCurveData
{
    /**
     * @var string
     *
     * @ORM\Column(name="cyl_no", type="string", length=2, nullable=false)
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
     * @ORM\Column(name="CDS_SerialNo", type="string", length=10, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $cdsSerialno;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="MeasTime", type="datetime", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $meastime;

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

    public function getCylNo(): ?string
    {
        return $this->cylNo;
    }

    public function getXVal(): ?int
    {
        return $this->xVal;
    }

    public function getCdsSerialno(): ?string
    {
        return $this->cdsSerialno;
    }

    public function getMeastime(): ?\DateTimeInterface
    {
        return $this->meastime;
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
