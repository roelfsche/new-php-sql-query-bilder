<?php

namespace App\Entity\UsrWeb71;

use Doctrine\ORM\Mapping as ORM;

/**
 * CylSpecSensorCoefficients
 *
 * @ORM\Table(name="cyl_spec_sensor_coefficients")
 * @ORM\Entity
 */
class CylSpecSensorCoefficients
{
    /**
     * @var bool
     *
     * @ORM\Column(name="cyl_no", type="boolean", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $cylNo;

    /**
     * @var string
     *
     * @ORM\Column(name="CDS_SerialNo", type="string", length=10, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $cdsSerialno;

    /**
     * @var float
     *
     * @ORM\Column(name="pra_sensor_ring_distance", type="float", precision=10, scale=8, nullable=false)
     */
    private $praSensorRingDistance;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="norm_time", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $normTime = '0000-00-00 00:00:00';

    public function getCylNo(): ?bool
    {
        return $this->cylNo;
    }

    public function getCdsSerialno(): ?string
    {
        return $this->cdsSerialno;
    }

    public function getPraSensorRingDistance(): ?float
    {
        return $this->praSensorRingDistance;
    }

    public function setPraSensorRingDistance(float $praSensorRingDistance): self
    {
        $this->praSensorRingDistance = $praSensorRingDistance;

        return $this;
    }

    public function getNormTime(): ?\DateTimeInterface
    {
        return $this->normTime;
    }

    public function setNormTime(\DateTimeInterface $normTime): self
    {
        $this->normTime = $normTime;

        return $this;
    }


}
