<?php

namespace App\Entity\Marprime;

use Doctrine\ORM\Mapping as ORM;

/**
 * EngineParams
 *
 * @ORM\Table(name="engine_params")
 * @ORM\Entity(repositoryClass="App\Repository\Marprime\EngineParamsRepository")
 */
class EngineParams
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
     * @var string
     *
     * @ORM\Column(name="engine_name", type="string", length=200, nullable=false)
     */
    private $engineName;

    /**
     * @var string
     *
     * @ORM\Column(name="engine_type", type="string", length=200, nullable=false)
     */
    private $engineType;

    /**
     * @var bool
     *
     * @ORM\Column(name="cyl_count", type="boolean", nullable=false)
     */
    private $cylCount = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="fire_angle", type="string", length=200, nullable=false)
     */
    private $fireAngle;

    /**
     * @var bool
     *
     * @ORM\Column(name="strokes", type="boolean", nullable=false)
     */
    private $strokes = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="speed", type="float", precision=10, scale=0, nullable=false)
     */
    private $speed = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="power", type="float", precision=10, scale=0, nullable=false)
     */
    private $power = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="stroke", type="float", precision=10, scale=0, nullable=false)
     */
    private $stroke = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="bore", type="float", precision=10, scale=0, nullable=false)
     */
    private $bore = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="connection_ratio", type="float", precision=10, scale=0, nullable=false)
     */
    private $connectionRatio = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="compression_ratio", type="float", precision=10, scale=0, nullable=false)
     */
    private $compressionRatio = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="inlet_open", type="integer", nullable=false)
     */
    private $inletOpen = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="inlet_close", type="integer", nullable=false)
     */
    private $inletClose = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="outlet_open", type="integer", nullable=false)
     */
    private $outletOpen = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="outlet_close", type="integer", nullable=false)
     */
    private $outletClose = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="cappa_correction", type="float", precision=10, scale=0, nullable=false)
     */
    private $cappaCorrection = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_change", type="datetime", nullable=false)
     */
    private $lastChange;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_ts", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $createTs = 'CURRENT_TIMESTAMP';

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
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

    public function getEngineName(): ?string
    {
        return $this->engineName;
    }

    public function setEngineName(string $engineName): self
    {
        $this->engineName = $engineName;

        return $this;
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

    public function getCylCount(): ?bool
    {
        return $this->cylCount;
    }

    public function setCylCount(bool $cylCount): self
    {
        $this->cylCount = $cylCount;

        return $this;
    }

    public function getFireAngle(): ?string
    {
        return $this->fireAngle;
    }

    public function setFireAngle(string $fireAngle): self
    {
        $this->fireAngle = $fireAngle;

        return $this;
    }

    public function getStrokes(): ?bool
    {
        return $this->strokes;
    }

    public function setStrokes(bool $strokes): self
    {
        $this->strokes = $strokes;

        return $this;
    }

    public function getSpeed(): ?float
    {
        return $this->speed;
    }

    public function setSpeed(float $speed): self
    {
        $this->speed = $speed;

        return $this;
    }

    public function getPower(): ?float
    {
        return $this->power;
    }

    public function setPower(float $power): self
    {
        $this->power = $power;

        return $this;
    }

    public function getStroke(): ?float
    {
        return $this->stroke;
    }

    public function setStroke(float $stroke): self
    {
        $this->stroke = $stroke;

        return $this;
    }

    public function getBore(): ?float
    {
        return $this->bore;
    }

    public function setBore(float $bore): self
    {
        $this->bore = $bore;

        return $this;
    }

    public function getConnectionRatio(): ?float
    {
        return $this->connectionRatio;
    }

    public function setConnectionRatio(float $connectionRatio): self
    {
        $this->connectionRatio = $connectionRatio;

        return $this;
    }

    public function getCompressionRatio(): ?float
    {
        return $this->compressionRatio;
    }

    public function setCompressionRatio(float $compressionRatio): self
    {
        $this->compressionRatio = $compressionRatio;

        return $this;
    }

    public function getInletOpen(): ?int
    {
        return $this->inletOpen;
    }

    public function setInletOpen(int $inletOpen): self
    {
        $this->inletOpen = $inletOpen;

        return $this;
    }

    public function getInletClose(): ?int
    {
        return $this->inletClose;
    }

    public function setInletClose(int $inletClose): self
    {
        $this->inletClose = $inletClose;

        return $this;
    }

    public function getOutletOpen(): ?int
    {
        return $this->outletOpen;
    }

    public function setOutletOpen(int $outletOpen): self
    {
        $this->outletOpen = $outletOpen;

        return $this;
    }

    public function getOutletClose(): ?int
    {
        return $this->outletClose;
    }

    public function setOutletClose(int $outletClose): self
    {
        $this->outletClose = $outletClose;

        return $this;
    }

    public function getCappaCorrection(): ?float
    {
        return $this->cappaCorrection;
    }

    public function setCappaCorrection(float $cappaCorrection): self
    {
        $this->cappaCorrection = $cappaCorrection;

        return $this;
    }

    public function getLastChange(): ?\DateTimeInterface
    {
        return $this->lastChange;
    }

    public function setLastChange(\DateTimeInterface $lastChange): self
    {
        $this->lastChange = $lastChange;

        return $this;
    }

    public function getCreateTs(): ?\DateTimeInterface
    {
        return $this->createTs;
    }

    public function setCreateTs(\DateTimeInterface $createTs): self
    {
        $this->createTs = $createTs;

        return $this;
    }


}
