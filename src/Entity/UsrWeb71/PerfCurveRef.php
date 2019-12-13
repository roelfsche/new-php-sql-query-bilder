<?php

namespace App\Entity\UsrWeb71;

use Doctrine\ORM\Mapping as ORM;

/**
 * PerfCurveRef
 *
 * @ORM\Table(name="perf_curve_ref", indexes={@ORM\Index(name="id", columns={"id"})})
 * @ORM\Entity
 */
class PerfCurveRef
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
     * @var string
     *
     * @ORM\Column(name="engine_type", type="text", length=255, nullable=false)
     */
    private $engineType;

    /**
     * @var bool
     *
     * @ORM\Column(name="percentage", type="boolean", nullable=false)
     */
    private $percentage = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="revolution", type="float", precision=10, scale=2, nullable=false, options={"default"="0.00"})
     */
    private $revolution = '0.00';

    /**
     * @var float
     *
     * @ORM\Column(name="scav_air", type="float", precision=10, scale=2, nullable=false, options={"default"="0.00"})
     */
    private $scavAir = '0.00';

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
     * @ORM\Column(name="mean_eff_pressure", type="float", precision=10, scale=2, nullable=false, options={"default"="0.00"})
     */
    private $meanEffPressure = '0.00';

    /**
     * @var float
     *
     * @ORM\Column(name="tc_inlet", type="float", precision=10, scale=2, nullable=false, options={"default"="0.00"})
     */
    private $tcInlet = '0.00';

    /**
     * @var float
     *
     * @ORM\Column(name="tc_outlet", type="float", precision=10, scale=2, nullable=false, options={"default"="0.00"})
     */
    private $tcOutlet = '0.00';

    /**
     * @var float
     *
     * @ORM\Column(name="sfoc", type="float", precision=10, scale=2, nullable=false, options={"default"="0.00"})
     */
    private $sfoc = '0.00';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEngineType(): ?string
    {
        return $this->engineType;
    }

    public function setEngineType(string $engineType): self
    {
        $this->engineType = $engineType;

        return $this;
    }

    public function getPercentage(): ?bool
    {
        return $this->percentage;
    }

    public function setPercentage(bool $percentage): self
    {
        $this->percentage = $percentage;

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

    public function getMeanEffPressure(): ?float
    {
        return $this->meanEffPressure;
    }

    public function setMeanEffPressure(float $meanEffPressure): self
    {
        $this->meanEffPressure = $meanEffPressure;

        return $this;
    }

    public function getTcInlet(): ?float
    {
        return $this->tcInlet;
    }

    public function setTcInlet(float $tcInlet): self
    {
        $this->tcInlet = $tcInlet;

        return $this;
    }

    public function getTcOutlet(): ?float
    {
        return $this->tcOutlet;
    }

    public function setTcOutlet(float $tcOutlet): self
    {
        $this->tcOutlet = $tcOutlet;

        return $this;
    }

    public function getSfoc(): ?float
    {
        return $this->sfoc;
    }

    public function setSfoc(float $sfoc): self
    {
        $this->sfoc = $sfoc;

        return $this;
    }


}
