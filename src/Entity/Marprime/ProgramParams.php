<?php

namespace App\Entity\Marprime;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProgramParams
 *
 * @ORM\Table(name="program_params")
 * @ORM\Entity
 */
class ProgramParams
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
     * @ORM\Column(name="MarPrime_SerialNo", type="string", length=20, nullable=false, options={"default"="-1"})
     */
    private $marprimeSerialno = '-1';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="installpath", type="text", length=65535, nullable=false)
     */
    private $installpath;

    /**
     * @var string
     *
     * @ORM\Column(name="company", type="string", length=25, nullable=false)
     */
    private $company;

    /**
     * @var string
     *
     * @ORM\Column(name="product", type="string", length=25, nullable=false)
     */
    private $product;

    /**
     * @var string
     *
     * @ORM\Column(name="sw_version", type="string", length=25, nullable=false)
     */
    private $swVersion;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", length=65535, nullable=false)
     */
    private $comment;

    /**
     * @var string
     *
     * @ORM\Column(name="reg_root_key", type="text", length=65535, nullable=false)
     */
    private $regRootKey;

    /**
     * @var string
     *
     * @ORM\Column(name="datadir", type="text", length=65535, nullable=false)
     */
    private $datadir;

    /**
     * @var string
     *
     * @ORM\Column(name="rawdatadir", type="text", length=65535, nullable=false)
     */
    private $rawdatadir;

    /**
     * @var string
     *
     * @ORM\Column(name="configdir", type="text", length=65535, nullable=false)
     */
    private $configdir;

    /**
     * @var string
     *
     * @ORM\Column(name="reportdir", type="text", length=65535, nullable=false)
     */
    private $reportdir;

    /**
     * @var string
     *
     * @ORM\Column(name="statisticsdir", type="text", length=65535, nullable=false)
     */
    private $statisticsdir;

    /**
     * @var string
     *
     * @ORM\Column(name="serial", type="string", length=25, nullable=false)
     */
    private $serial;

    /**
     * @var string
     *
     * @ORM\Column(name="key", type="string", length=35, nullable=false)
     */
    private $key;

    /**
     * @var int
     *
     * @ORM\Column(name="reg_mod", type="smallint", nullable=false)
     */
    private $regMod;

    /**
     * @var int
     *
     * @ORM\Column(name="auto_modules", type="smallint", nullable=false)
     */
    private $autoModules;

    /**
     * @var int
     *
     * @ORM\Column(name="inst_modules", type="smallint", nullable=false)
     */
    private $instModules;

    /**
     * @var int
     *
     * @ORM\Column(name="disp_modules", type="smallint", nullable=false)
     */
    private $dispModules;

    /**
     * @var int
     *
     * @ORM\Column(name="active_modules", type="smallint", nullable=false)
     */
    private $activeModules;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="elapse_time", type="datetime", nullable=false)
     */
    private $elapseTime;

    /**
     * @var int
     *
     * @ORM\Column(name="current_eng", type="smallint", nullable=false)
     */
    private $currentEng;

    /**
     * @var int
     *
     * @ORM\Column(name="licence_type", type="smallint", nullable=false)
     */
    private $licenceType;

    /**
     * @var int
     *
     * @ORM\Column(name="debug_mode", type="smallint", nullable=false)
     */
    private $debugMode;

    /**
     * @var int
     *
     * @ORM\Column(name="press_unit", type="smallint", nullable=false)
     */
    private $pressUnit;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    private $status;

    public function getMarprimeSerialno(): ?string
    {
        return $this->marprimeSerialno;
    }

    public function setMarprimeSerialno(string $marprimeSerialno): self
    {
        $this->marprimeSerialno = $marprimeSerialno;

        return $this;
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

    public function getInstallpath(): ?string
    {
        return $this->installpath;
    }

    public function setInstallpath(string $installpath): self
    {
        $this->installpath = $installpath;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(string $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getProduct(): ?string
    {
        return $this->product;
    }

    public function setProduct(string $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getSwVersion(): ?string
    {
        return $this->swVersion;
    }

    public function setSwVersion(string $swVersion): self
    {
        $this->swVersion = $swVersion;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getRegRootKey(): ?string
    {
        return $this->regRootKey;
    }

    public function setRegRootKey(string $regRootKey): self
    {
        $this->regRootKey = $regRootKey;

        return $this;
    }

    public function getDatadir(): ?string
    {
        return $this->datadir;
    }

    public function setDatadir(string $datadir): self
    {
        $this->datadir = $datadir;

        return $this;
    }

    public function getRawdatadir(): ?string
    {
        return $this->rawdatadir;
    }

    public function setRawdatadir(string $rawdatadir): self
    {
        $this->rawdatadir = $rawdatadir;

        return $this;
    }

    public function getConfigdir(): ?string
    {
        return $this->configdir;
    }

    public function setConfigdir(string $configdir): self
    {
        $this->configdir = $configdir;

        return $this;
    }

    public function getReportdir(): ?string
    {
        return $this->reportdir;
    }

    public function setReportdir(string $reportdir): self
    {
        $this->reportdir = $reportdir;

        return $this;
    }

    public function getStatisticsdir(): ?string
    {
        return $this->statisticsdir;
    }

    public function setStatisticsdir(string $statisticsdir): self
    {
        $this->statisticsdir = $statisticsdir;

        return $this;
    }

    public function getSerial(): ?string
    {
        return $this->serial;
    }

    public function setSerial(string $serial): self
    {
        $this->serial = $serial;

        return $this;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    public function getRegMod(): ?int
    {
        return $this->regMod;
    }

    public function setRegMod(int $regMod): self
    {
        $this->regMod = $regMod;

        return $this;
    }

    public function getAutoModules(): ?int
    {
        return $this->autoModules;
    }

    public function setAutoModules(int $autoModules): self
    {
        $this->autoModules = $autoModules;

        return $this;
    }

    public function getInstModules(): ?int
    {
        return $this->instModules;
    }

    public function setInstModules(int $instModules): self
    {
        $this->instModules = $instModules;

        return $this;
    }

    public function getDispModules(): ?int
    {
        return $this->dispModules;
    }

    public function setDispModules(int $dispModules): self
    {
        $this->dispModules = $dispModules;

        return $this;
    }

    public function getActiveModules(): ?int
    {
        return $this->activeModules;
    }

    public function setActiveModules(int $activeModules): self
    {
        $this->activeModules = $activeModules;

        return $this;
    }

    public function getElapseTime(): ?\DateTimeInterface
    {
        return $this->elapseTime;
    }

    public function setElapseTime(\DateTimeInterface $elapseTime): self
    {
        $this->elapseTime = $elapseTime;

        return $this;
    }

    public function getCurrentEng(): ?int
    {
        return $this->currentEng;
    }

    public function setCurrentEng(int $currentEng): self
    {
        $this->currentEng = $currentEng;

        return $this;
    }

    public function getLicenceType(): ?int
    {
        return $this->licenceType;
    }

    public function setLicenceType(int $licenceType): self
    {
        $this->licenceType = $licenceType;

        return $this;
    }

    public function getDebugMode(): ?int
    {
        return $this->debugMode;
    }

    public function setDebugMode(int $debugMode): self
    {
        $this->debugMode = $debugMode;

        return $this;
    }

    public function getPressUnit(): ?int
    {
        return $this->pressUnit;
    }

    public function setPressUnit(int $pressUnit): self
    {
        $this->pressUnit = $pressUnit;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }
}
