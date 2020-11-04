<?php

namespace App\Entity\Marprime;

use Doctrine\ORM\Mapping as ORM;

/**
 * MeasurementParams
 *
 * @ORM\Table(name="measurement_params")
 * @ORM\Entity(repositoryClass="App\Repository\Marprime\MeasurementParamsRepository")
 */
class MeasurementParams
{
    /**
     * 2 oder 4
     */
    private $strokes = 2;
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
     * @var float
     *
     * @ORM\Column(name="convergence", type="float", precision=10, scale=0, nullable=false)
     */
    private $convergence;

    /**
     * @var float
     *
     * @ORM\Column(name="alpha1", type="float", precision=10, scale=0, nullable=false)
     */
    private $alpha1;

    /**
     * @var float
     *
     * @ORM\Column(name="alpha2", type="float", precision=10, scale=0, nullable=false)
     */
    private $alpha2;

    /**
     * @var int
     *
     * @ORM\Column(name="monitor", type="integer", nullable=false)
     */
    private $monitor;

    /**
     * @var int
     *
     * @ORM\Column(name="leakage_left_start", type="integer", nullable=false)
     */
    private $leakageLeftStart;

    /**
     * @var int
     *
     * @ORM\Column(name="leakage_left_end", type="integer", nullable=false)
     */
    private $leakageLeftEnd;

    /**
     * @var int
     *
     * @ORM\Column(name="leakage_right_start", type="integer", nullable=false)
     */
    private $leakageRightStart;

    /**
     * @var int
     *
     * @ORM\Column(name="leakage_right_end", type="integer", nullable=false)
     */
    private $leakageRightEnd;

    /**
     * @var float
     *
     * @ORM\Column(name="normalisation_limit_start", type="float", precision=10, scale=0, nullable=false)
     */
    private $normalisationLimitStart;

    /**
     * @var float
     *
     * @ORM\Column(name="normalisation_limit_end", type="float", precision=10, scale=0, nullable=false)
     */
    private $normalisationLimitEnd;

    /**
     * @var float
     *
     * @ORM\Column(name="diagnostic_range_start", type="float", precision=10, scale=0, nullable=false)
     */
    private $diagnosticRangeStart;

    /**
     * @var float
     *
     * @ORM\Column(name="diagnostic_range_end", type="float", precision=10, scale=0, nullable=false)
     */
    private $diagnosticRangeEnd;

    /**
     * @var float
     *
     * @ORM\Column(name="diagnostic_weight_start", type="float", precision=10, scale=0, nullable=false)
     */
    private $diagnosticWeightStart;

    /**
     * @var float
     *
     * @ORM\Column(name="diagnostic_weight_end", type="float", precision=10, scale=0, nullable=false)
     */
    private $diagnosticWeightEnd;

    /**
     * @var float
     *
     * @ORM\Column(name="ae_amplification", type="float", precision=10, scale=0, nullable=false)
     */
    private $aeAmplification;

    /**
     * @var float
     *
     * @ORM\Column(name="limit_pmax", type="float", precision=10, scale=0, nullable=false)
     */
    private $limitPmax;

    /**
     * @var float
     *
     * @ORM\Column(name="limit_pcomp", type="float", precision=10, scale=0, nullable=false)
     */
    private $limitPcomp;

    /**
     * @var float
     *
     * @ORM\Column(name="limit_power", type="float", precision=10, scale=0, nullable=false)
     */
    private $limitPower;

    public function getStrokes(): ?int 
    {
        return $this->strokes;
    }

    public function setStrokes($intStrokes) {
        $this->strokes = $intStrokes;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function getMarprimeSerialno(): ?string
    {
        return $this->marprimeSerialno;
    }

    public function getConvergence(): ?float
    {
        return $this->convergence;
    }

    public function setConvergence(float $convergence): self
    {
        $this->convergence = $convergence;

        return $this;
    }

    public function getAlpha1(): ?float
    {
        return $this->alpha1;
    }

    public function setAlpha1(float $alpha1): self
    {
        $this->alpha1 = $alpha1;

        return $this;
    }

    public function getAlpha2(): ?float
    {
        return $this->alpha2;
    }

    public function setAlpha2(float $alpha2): self
    {
        $this->alpha2 = $alpha2;

        return $this;
    }

    public function getMonitor(): ?int
    {
        return $this->monitor;
    }

    public function setMonitor(int $monitor): self
    {
        $this->monitor = $monitor;

        return $this;
    }

    public function getLeakageLeftStart(): ?int
    {
        return $this->leakageLeftStart;
    }

    public function setLeakageLeftStart(int $leakageLeftStart): self
    {
        $this->leakageLeftStart = $leakageLeftStart;

        return $this;
    }

    public function getLeakageLeftEnd(): ?int
    {
        return $this->leakageLeftEnd;
    }

    public function setLeakageLeftEnd(int $leakageLeftEnd): self
    {
        $this->leakageLeftEnd = $leakageLeftEnd;

        return $this;
    }

    public function getLeakageRightStart(): ?int
    {
        return $this->leakageRightStart;
    }

    public function setLeakageRightStart(int $leakageRightStart): self
    {
        $this->leakageRightStart = $leakageRightStart;

        return $this;
    }

    public function getLeakageRightEnd(): ?int
    {
        return $this->leakageRightEnd;
    }

    public function setLeakageRightEnd(int $leakageRightEnd): self
    {
        $this->leakageRightEnd = $leakageRightEnd;

        return $this;
    }

    public function getNormalisationLimitStart(): ?float
    {
        return $this->normalisationLimitStart;
    }

    public function setNormalisationLimitStart(float $normalisationLimitStart): self
    {
        $this->normalisationLimitStart = $normalisationLimitStart;

        return $this;
    }

    public function getNormalisationLimitEnd(): ?float
    {
        return $this->normalisationLimitEnd;
    }

    public function setNormalisationLimitEnd(float $normalisationLimitEnd): self
    {
        $this->normalisationLimitEnd = $normalisationLimitEnd;

        return $this;
    }

    public function getDiagnosticRangeStart(): ?float
    {
        return $this->diagnosticRangeStart;
    }

    public function setDiagnosticRangeStart(float $diagnosticRangeStart): self
    {
        $this->diagnosticRangeStart = $diagnosticRangeStart;

        return $this;
    }

    public function getDiagnosticRangeEnd(): ?float
    {
        return $this->diagnosticRangeEnd;
    }

    public function setDiagnosticRangeEnd(float $diagnosticRangeEnd): self
    {
        $this->diagnosticRangeEnd = $diagnosticRangeEnd;

        return $this;
    }

    public function getDiagnosticWeightStart(): ?float
    {
        return $this->diagnosticWeightStart;
    }

    public function setDiagnosticWeightStart(float $diagnosticWeightStart): self
    {
        $this->diagnosticWeightStart = $diagnosticWeightStart;

        return $this;
    }

    public function getDiagnosticWeightEnd(): ?float
    {
        return $this->diagnosticWeightEnd;
    }

    public function setDiagnosticWeightEnd(float $diagnosticWeightEnd): self
    {
        $this->diagnosticWeightEnd = $diagnosticWeightEnd;

        return $this;
    }

    public function getAeAmplification(): ?float
    {
        return $this->aeAmplification;
    }

    public function setAeAmplification(float $aeAmplification): self
    {
        $this->aeAmplification = $aeAmplification;

        return $this;
    }

    public function getLimitPmax(): ?float
    {
        return $this->limitPmax;
    }

    public function setLimitPmax(float $limitPmax): self
    {
        $this->limitPmax = $limitPmax;

        return $this;
    }

    public function getLimitPcomp(): ?float
    {
        return $this->limitPcomp;
    }

    public function setLimitPcomp(float $limitPcomp): self
    {
        $this->limitPcomp = $limitPcomp;

        return $this;
    }

    public function getLimitPower(): ?float
    {
        return $this->limitPower;
    }

    public function setLimitPower(float $limitPower): self
    {
        $this->limitPower = $limitPower;

        return $this;
    }


}
