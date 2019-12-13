<?php

namespace App\Entity\UsrWeb71;

use Doctrine\ORM\Mapping as ORM;

/**
 * CpaFilesData
 *
 * @ORM\Table(name="cpa_files_data")
 * @ORM\Entity
 */
class CpaFilesData
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="meas_time", type="datetime", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $measTime;

    /**
     * @var string
     *
     * @ORM\Column(name="cyl_no", type="string", length=3, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $cylNo;

    /**
     * @var bool
     *
     * @ORM\Column(name="measurement_id", type="boolean", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $measurementId;

    /**
     * @var string
     *
     * @ORM\Column(name="CDS_SerialNo", type="string", length=10, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $cdsSerialno;

    /**
     * @var int
     *
     * @ORM\Column(name="ipa_mode", type="integer", nullable=false)
     */
    private $ipaMode;

    /**
     * @var float
     *
     * @ORM\Column(name="sh_val", type="float", precision=10, scale=0, nullable=false)
     */
    private $shVal;

    /**
     * @var float
     *
     * @ORM\Column(name="calc_fail", type="float", precision=10, scale=0, nullable=false)
     */
    private $calcFail;

    /**
     * @var int
     *
     * @ORM\Column(name="count_cycles", type="integer", nullable=false)
     */
    private $countCycles;

    /**
     * @var float
     *
     * @ORM\Column(name="cycle_length_cpa", type="float", precision=10, scale=0, nullable=false)
     */
    private $cycleLengthCpa;

    /**
     * @var float
     *
     * @ORM\Column(name="cycle_length_ipa", type="float", precision=10, scale=0, nullable=false)
     */
    private $cycleLengthIpa;

    /**
     * @var int
     *
     * @ORM\Column(name="data_status", type="integer", nullable=false)
     */
    private $dataStatus;

    /**
     * @var int
     *
     * @ORM\Column(name="ipa_start", type="integer", nullable=false)
     */
    private $ipaStart;

    /**
     * @var float
     *
     * @ORM\Column(name="ipa_schritt", type="float", precision=10, scale=0, nullable=false)
     */
    private $ipaSchritt;

    /**
     * @var int
     *
     * @ORM\Column(name="ot_korrektur", type="integer", nullable=false)
     */
    private $otKorrektur;

    /**
     * @var int
     *
     * @ORM\Column(name="spuelluft", type="integer", nullable=false)
     */
    private $spuelluft;

    /**
     * @var int
     *
     * @ORM\Column(name="pfeiffenschwingungskorrektur", type="integer", nullable=false)
     */
    private $pfeiffenschwingungskorrektur;

    /**
     * @var float
     *
     * @ORM\Column(name="revolution", type="float", precision=10, scale=3, nullable=false)
     */
    private $revolution;

    /**
     * @var float
     *
     * @ORM\Column(name="ind_power", type="float", precision=10, scale=3, nullable=false)
     */
    private $indPower;

    /**
     * @var float
     *
     * @ORM\Column(name="v_dp", type="float", precision=10, scale=3, nullable=false)
     */
    private $vDp;

    /**
     * @var float
     *
     * @ORM\Column(name="pil", type="float", precision=10, scale=3, nullable=false)
     */
    private $pil;

    /**
     * @var float
     *
     * @ORM\Column(name="pmax", type="float", precision=10, scale=3, nullable=false)
     */
    private $pmax;

    /**
     * @var float
     *
     * @ORM\Column(name="apmax", type="float", precision=10, scale=3, nullable=false)
     */
    private $apmax;

    /**
     * @var float
     *
     * @ORM\Column(name="pim", type="float", precision=10, scale=3, nullable=false)
     */
    private $pim;

    /**
     * @var float
     *
     * @ORM\Column(name="pilw", type="float", precision=10, scale=3, nullable=false)
     */
    private $pilw;

    /**
     * @var float
     *
     * @ORM\Column(name="p0", type="float", precision=10, scale=3, nullable=false)
     */
    private $p0;

    /**
     * @var float
     *
     * @ORM\Column(name="p36", type="float", precision=10, scale=3, nullable=false)
     */
    private $p36;

    /**
     * @var float
     *
     * @ORM\Column(name="pemax", type="float", precision=10, scale=3, nullable=false)
     */
    private $pemax;

    /**
     * @var float
     *
     * @ORM\Column(name="apemax", type="float", precision=10, scale=3, nullable=false)
     */
    private $apemax;

    /**
     * @var float
     *
     * @ORM\Column(name="peb", type="float", precision=10, scale=3, nullable=false)
     */
    private $peb;

    /**
     * @var float
     *
     * @ORM\Column(name="apeb", type="float", precision=10, scale=3, nullable=false)
     */
    private $apeb;

    /**
     * @var float
     *
     * @ORM\Column(name="apee", type="float", precision=10, scale=3, nullable=false)
     */
    private $apee;

    /**
     * @var float
     *
     * @ORM\Column(name="apfb", type="float", precision=10, scale=3, nullable=false)
     */
    private $apfb;

    /**
     * @var float
     *
     * @ORM\Column(name="apfe", type="float", precision=10, scale=3, nullable=false)
     */
    private $apfe;

    /**
     * @var float
     *
     * @ORM\Column(name="afd", type="float", precision=10, scale=3, nullable=false)
     */
    private $afd;

    /**
     * @var float
     *
     * @ORM\Column(name="azv", type="float", precision=10, scale=3, nullable=false)
     */
    private $azv;

    /**
     * @var float
     *
     * @ORM\Column(name="aev", type="float", precision=10, scale=3, nullable=false)
     */
    private $aev;

    /**
     * @var float
     *
     * @ORM\Column(name="scav_air", type="float", precision=10, scale=3, nullable=false)
     */
    private $scavAir;

    /**
     * @var float
     *
     * @ORM\Column(name="aed", type="float", precision=10, scale=3, nullable=false)
     */
    private $aed;

    /**
     * @var float
     *
     * @ORM\Column(name="pfb", type="float", precision=10, scale=3, nullable=false)
     */
    private $pfb;

    /**
     * @var float
     *
     * @ORM\Column(name="avb", type="float", precision=10, scale=3, nullable=false)
     */
    private $avb;

    /**
     * @var float
     *
     * @ORM\Column(name="cappa", type="float", precision=10, scale=3, nullable=false)
     */
    private $cappa;

    public function getMeasTime(): ?\DateTimeInterface
    {
        return $this->measTime;
    }

    public function getCylNo(): ?string
    {
        return $this->cylNo;
    }

    public function getMeasurementId(): ?bool
    {
        return $this->measurementId;
    }

    public function getCdsSerialno(): ?string
    {
        return $this->cdsSerialno;
    }

    public function getIpaMode(): ?int
    {
        return $this->ipaMode;
    }

    public function setIpaMode(int $ipaMode): self
    {
        $this->ipaMode = $ipaMode;

        return $this;
    }

    public function getShVal(): ?float
    {
        return $this->shVal;
    }

    public function setShVal(float $shVal): self
    {
        $this->shVal = $shVal;

        return $this;
    }

    public function getCalcFail(): ?float
    {
        return $this->calcFail;
    }

    public function setCalcFail(float $calcFail): self
    {
        $this->calcFail = $calcFail;

        return $this;
    }

    public function getCountCycles(): ?int
    {
        return $this->countCycles;
    }

    public function setCountCycles(int $countCycles): self
    {
        $this->countCycles = $countCycles;

        return $this;
    }

    public function getCycleLengthCpa(): ?float
    {
        return $this->cycleLengthCpa;
    }

    public function setCycleLengthCpa(float $cycleLengthCpa): self
    {
        $this->cycleLengthCpa = $cycleLengthCpa;

        return $this;
    }

    public function getCycleLengthIpa(): ?float
    {
        return $this->cycleLengthIpa;
    }

    public function setCycleLengthIpa(float $cycleLengthIpa): self
    {
        $this->cycleLengthIpa = $cycleLengthIpa;

        return $this;
    }

    public function getDataStatus(): ?int
    {
        return $this->dataStatus;
    }

    public function setDataStatus(int $dataStatus): self
    {
        $this->dataStatus = $dataStatus;

        return $this;
    }

    public function getIpaStart(): ?int
    {
        return $this->ipaStart;
    }

    public function setIpaStart(int $ipaStart): self
    {
        $this->ipaStart = $ipaStart;

        return $this;
    }

    public function getIpaSchritt(): ?float
    {
        return $this->ipaSchritt;
    }

    public function setIpaSchritt(float $ipaSchritt): self
    {
        $this->ipaSchritt = $ipaSchritt;

        return $this;
    }

    public function getOtKorrektur(): ?int
    {
        return $this->otKorrektur;
    }

    public function setOtKorrektur(int $otKorrektur): self
    {
        $this->otKorrektur = $otKorrektur;

        return $this;
    }

    public function getSpuelluft(): ?int
    {
        return $this->spuelluft;
    }

    public function setSpuelluft(int $spuelluft): self
    {
        $this->spuelluft = $spuelluft;

        return $this;
    }

    public function getPfeiffenschwingungskorrektur(): ?int
    {
        return $this->pfeiffenschwingungskorrektur;
    }

    public function setPfeiffenschwingungskorrektur(int $pfeiffenschwingungskorrektur): self
    {
        $this->pfeiffenschwingungskorrektur = $pfeiffenschwingungskorrektur;

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

    public function getIndPower(): ?float
    {
        return $this->indPower;
    }

    public function setIndPower(float $indPower): self
    {
        $this->indPower = $indPower;

        return $this;
    }

    public function getVDp(): ?float
    {
        return $this->vDp;
    }

    public function setVDp(float $vDp): self
    {
        $this->vDp = $vDp;

        return $this;
    }

    public function getPil(): ?float
    {
        return $this->pil;
    }

    public function setPil(float $pil): self
    {
        $this->pil = $pil;

        return $this;
    }

    public function getPmax(): ?float
    {
        return $this->pmax;
    }

    public function setPmax(float $pmax): self
    {
        $this->pmax = $pmax;

        return $this;
    }

    public function getApmax(): ?float
    {
        return $this->apmax;
    }

    public function setApmax(float $apmax): self
    {
        $this->apmax = $apmax;

        return $this;
    }

    public function getPim(): ?float
    {
        return $this->pim;
    }

    public function setPim(float $pim): self
    {
        $this->pim = $pim;

        return $this;
    }

    public function getPilw(): ?float
    {
        return $this->pilw;
    }

    public function setPilw(float $pilw): self
    {
        $this->pilw = $pilw;

        return $this;
    }

    public function getP0(): ?float
    {
        return $this->p0;
    }

    public function setP0(float $p0): self
    {
        $this->p0 = $p0;

        return $this;
    }

    public function getP36(): ?float
    {
        return $this->p36;
    }

    public function setP36(float $p36): self
    {
        $this->p36 = $p36;

        return $this;
    }

    public function getPemax(): ?float
    {
        return $this->pemax;
    }

    public function setPemax(float $pemax): self
    {
        $this->pemax = $pemax;

        return $this;
    }

    public function getApemax(): ?float
    {
        return $this->apemax;
    }

    public function setApemax(float $apemax): self
    {
        $this->apemax = $apemax;

        return $this;
    }

    public function getPeb(): ?float
    {
        return $this->peb;
    }

    public function setPeb(float $peb): self
    {
        $this->peb = $peb;

        return $this;
    }

    public function getApeb(): ?float
    {
        return $this->apeb;
    }

    public function setApeb(float $apeb): self
    {
        $this->apeb = $apeb;

        return $this;
    }

    public function getApee(): ?float
    {
        return $this->apee;
    }

    public function setApee(float $apee): self
    {
        $this->apee = $apee;

        return $this;
    }

    public function getApfb(): ?float
    {
        return $this->apfb;
    }

    public function setApfb(float $apfb): self
    {
        $this->apfb = $apfb;

        return $this;
    }

    public function getApfe(): ?float
    {
        return $this->apfe;
    }

    public function setApfe(float $apfe): self
    {
        $this->apfe = $apfe;

        return $this;
    }

    public function getAfd(): ?float
    {
        return $this->afd;
    }

    public function setAfd(float $afd): self
    {
        $this->afd = $afd;

        return $this;
    }

    public function getAzv(): ?float
    {
        return $this->azv;
    }

    public function setAzv(float $azv): self
    {
        $this->azv = $azv;

        return $this;
    }

    public function getAev(): ?float
    {
        return $this->aev;
    }

    public function setAev(float $aev): self
    {
        $this->aev = $aev;

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

    public function getAed(): ?float
    {
        return $this->aed;
    }

    public function setAed(float $aed): self
    {
        $this->aed = $aed;

        return $this;
    }

    public function getPfb(): ?float
    {
        return $this->pfb;
    }

    public function setPfb(float $pfb): self
    {
        $this->pfb = $pfb;

        return $this;
    }

    public function getAvb(): ?float
    {
        return $this->avb;
    }

    public function setAvb(float $avb): self
    {
        $this->avb = $avb;

        return $this;
    }

    public function getCappa(): ?float
    {
        return $this->cappa;
    }

    public function setCappa(float $cappa): self
    {
        $this->cappa = $cappa;

        return $this;
    }


}
