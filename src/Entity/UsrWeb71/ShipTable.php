<?php

namespace App\Entity\UsrWeb71;

use App\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * ShipTable
 *
 * @ORM\Table(name="ship_table", uniqueConstraints={@ORM\UniqueConstraint(name="id_2", columns={"id"})}, indexes={@ORM\Index(name="id", columns={"id"})})
 * @ORM\Entity(repositoryClass="App\Repository\UsrWeb71\ShipTableRepository")
 */
class ShipTable extends BaseEntity
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
     * @ORM\Column(name="CDS_SerialNo", type="string", length=11, nullable=false, options={"default"="-1"})
     */
    private $cdsSerialno = '-1';

    /**
     * @var string
     *
     * @ORM\Column(name="MarPrime_SerialNo", type="string", length=20, nullable=false, options={"default"="0.0.0"})
     */
    private $marprimeSerialno = '0.0.0';

    /**
     * @var string
     *
     * @ORM\Column(name="IMO_No", type="string", length=10, nullable=false)
     */
    private $imoNo;

    /**
     * @var string|null
     *
     * @ORM\Column(name="state", type="text", length=65535, nullable=true)
     */
    private $state;

    /**
     * @var string|null
     *
     * @ORM\Column(name="load_value", type="decimal", precision=4, scale=1, nullable=true)
     */
    private $loadValue;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="load_date", type="date", nullable=true)
     */
    private $loadDate;

    /**
     * @var string
     *
     * @ORM\Column(name="reederei", type="text", length=65535, nullable=false)
     * @ManyToOne(targetEntity="Reederei", inversedBy="ships")
     * @JoinColumn(name="reederei", referencedColumnName="name")
     */
    private $reederei;

    /**
     * @var int|null
     *
     * @ORM\Column(name="ship_no", type="integer", nullable=true)
     */
    private $shipNo;

    /**
     * @var string|null
     *
     * @ORM\Column(name="mail", type="text", length=65535, nullable=true)
     */
    private $mail;

    /**
     * @var string|null
     *
     * @ORM\Column(name="taufname", type="text", length=65535, nullable=true)
     */
    private $taufname;

    /**
     * @var string|null
     *
     * @ORM\Column(name="akt_name", type="text", length=65535, nullable=true)
     */
    private $aktName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="zusatz", type="text", length=65535, nullable=true)
     */
    private $zusatz;

    /**
     * @var string|null
     *
     * @ORM\Column(name="hull_no", type="text", length=65535, nullable=true)
     */
    private $hullNo;

    /**
     * @var string|null
     *
     * @ORM\Column(name="yard", type="text", length=65535, nullable=true)
     */
    private $yard;

    /**
     * @var string|null
     *
     * @ORM\Column(name="engine", type="text", length=65535, nullable=true)
     */
    private $engine;

    /**
     * @var string|null
     *
     * @ORM\Column(name="seatrial", type="text", length=65535, nullable=true)
     */
    private $seatrial;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="last_measurement", type="date", nullable=true)
     */
    private $lastMeasurement;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="last_update", type="date", nullable=true)
     */
    private $lastUpdate;

    /**
     * @var string|null
     *
     * @ORM\Column(name="cds_inhalt", type="text", length=65535, nullable=true)
     */
    private $cdsInhalt;

    /**
     * @var string|null
     *
     * @ORM\Column(name="tms_inhalt", type="text", length=65535, nullable=true)
     */
    private $tmsInhalt;

    /**
     * @var string|null
     *
     * @ORM\Column(name="cds_no", type="text", length=65535, nullable=true)
     */
    private $cdsNo;

    /**
     * @var string|null
     *
     * @ORM\Column(name="tms_no", type="text", length=65535, nullable=true)
     */
    private $tmsNo;

    /**
     * @var string|null
     *
     * @ORM\Column(name="cds_state", type="text", length=65535, nullable=true)
     */
    private $cdsState;

    /**
     * @var string|null
     *
     * @ORM\Column(name="tms_state", type="text", length=65535, nullable=true)
     */
    private $tmsState;

    /**
     * @var string|null
     *
     * @ORM\Column(name="prm_picture", type="text", length=0, nullable=true)
     */
    private $prmPicture;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="cyl_count", type="boolean", nullable=true)
     */
    private $cylCount;

    /**
     * @var int
     *
     * @ORM\Column(name="ring_count", type="integer", nullable=false, options={"default"="4"})
     */
    private $ringCount = '4';

    /**
     * @var string|null
     *
     * @ORM\Column(name="tech_desc_top_ring", type="text", length=65535, nullable=true)
     */
    private $techDescTopRing;

    /**
     * @var string|null
     *
     * @ORM\Column(name="spec_fuel", type="text", length=65535, nullable=true)
     */
    private $specFuel;

    /**
     * @var string|null
     *
     * @ORM\Column(name="spec_date", type="text", length=65535, nullable=true)
     */
    private $specDate;

    /**
     * @var string|null
     *
     * @ORM\Column(name="spec_n", type="text", length=65535, nullable=true)
     */
    private $specN;

    /**
     * @var bool
     *
     * @ORM\Column(name="spec_pic", type="boolean", nullable=false)
     */
    private $specPic = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="vorschau", type="boolean", nullable=false)
     */
    private $vorschau = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="abo", type="boolean", nullable=false)
     */
    private $abo = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="gl", type="boolean", nullable=false)
     */
    private $gl = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="build_year", type="integer", nullable=false)
     */
    private $buildYear = '0';

    /**
     * @var string|null
     *
     * @ORM\Column(name="inspection", type="text", length=65535, nullable=true)
     */
    private $inspection;

    /**
     * @var string
     *
     * @ORM\Column(name="color", type="text", length=65535, nullable=false)
     */
    private $color;

    /**
     * @var float
     *
     * @ORM\Column(name="pra_liner_temp_alarm", type="float", precision=10, scale=0, nullable=false, options={"default"="130"})
     */
    private $praLinerTempAlarm = '130';

    /**
     * @var float
     *
     * @ORM\Column(name="pra_liner_temp_critical", type="float", precision=10, scale=0, nullable=false, options={"default"="125"})
     */
    private $praLinerTempCritical = '125';

    /**
     * @var bool
     *
     * @ORM\Column(name="pra_thermal_load_alarm", type="boolean", nullable=false, options={"default"="80"})
     */
    private $praThermalLoadAlarm = '80';

    /**
     * @var bool
     *
     * @ORM\Column(name="pra_thermal_load_critical", type="boolean", nullable=false, options={"default"="60"})
     */
    private $praThermalLoadCritical = '60';

    /**
     * @var float|null
     *
     * @ORM\Column(name="max_power", type="float", precision=10, scale=0, nullable=true)
     */
    private $maxPower;

    /**
     * @var float
     *
     * @ORM\Column(name="rpm100", type="float", precision=10, scale=0, nullable=false)
     */
    private $rpm100 = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="CDS_aktiv", type="boolean", nullable=false)
     */
    private $cdsAktiv = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="lms_aktiv", type="boolean", nullable=false)
     */
    private $lmsAktiv = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="pra_aktiv", type="boolean", nullable=false)
     */
    private $praAktiv = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="run_aktiv", type="boolean", nullable=false)
     */
    private $runAktiv = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="cpa_aktiv", type="boolean", nullable=false)
     */
    private $cpaAktiv = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="ipa_aktiv", type="boolean", nullable=false)
     */
    private $ipaAktiv = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="MarPrime_aktiv", type="boolean", nullable=false, options={"default"="1"})
     */
    private $marprimeAktiv = '1';

    /**
     * @var bool
     *
     * @ORM\Column(name="Liwat_aktiv", type="boolean", nullable=false)
     */
    private $liwatAktiv = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="MarTorque_aktiv", type="boolean", nullable=false)
     */
    private $martorqueAktiv = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="new_ring", type="float", precision=10, scale=2, nullable=false, options={"default"="25.00"})
     */
    private $newRing = '25.00';

    /**
     * @var float
     *
     * @ORM\Column(name="worn_ring", type="float", precision=10, scale=2, nullable=false, options={"default"="21.00"})
     */
    private $wornRing = '21.00';

    /**
     * @var float
     *
     * @ORM\Column(name="Sulzer_new_ring", type="float", precision=10, scale=0, nullable=false, options={"default"="0.5"})
     */
    private $sulzerNewRing = '0.5';

    /**
     * @var float
     *
     * @ORM\Column(name="ring_korrektur", type="float", precision=10, scale=2, nullable=false, options={"default"="1.00"})
     */
    private $ringKorrektur = '1.00';

    /**
     * @var float
     *
     * @ORM\Column(name="korrekturfaktor", type="float", precision=6, scale=3, nullable=false, options={"default"="0.350"})
     */
    private $korrekturfaktor = '0.350';

    /**
     * @var float
     *
     * @ORM\Column(name="cpa_deviation_comp_pressure", type="float", precision=10, scale=0, nullable=false, options={"default"="3"})
     */
    private $cpaDeviationCompPressure = '3';

    /**
     * @var float
     *
     * @ORM\Column(name="cpa_deviation_max_press", type="float", precision=10, scale=0, nullable=false, options={"default"="3"})
     */
    private $cpaDeviationMaxPress = '3';

    /**
     * @var float
     *
     * @ORM\Column(name="cpa_deviation_mean_ind_press", type="float", precision=10, scale=0, nullable=false, options={"default"="3"})
     */
    private $cpaDeviationMeanIndPress = '3';

    /**
     * @var float
     *
     * @ORM\Column(name="cpa_deviation_ind_power", type="float", precision=10, scale=0, nullable=false, options={"default"="3"})
     */
    private $cpaDeviationIndPower = '3';

    /**
     * @var float
     *
     * @ORM\Column(name="cpa_deviation_scav_air_press", type="float", precision=10, scale=0, nullable=false, options={"default"="10"})
     */
    private $cpaDeviationScavAirPress = '10';

    /**
     * @var float
     *
     * @ORM\Column(name="load_balance_max", type="float", precision=10, scale=0, nullable=false, options={"default"="0.023"})
     */
    private $loadBalanceMax = '0.023';

    /**
     * @var bool
     *
     * @ORM\Column(name="load_balance_alarm", type="boolean", nullable=false, options={"default"="20"})
     */
    private $loadBalanceAlarm = '20';

    /**
     * @var binary
     *
     * @ORM\Column(name="show_diagrams", type="binary", nullable=false, options={"default"="11111111111111111111111111111111"})
     */
    private $showDiagrams = '11111111111111111111111111111111';

    /**
     * @var binary
     *
     * @ORM\Column(name="suspended_cylinder", type="binary", nullable=false, options={"default"="0000000000000000"})
     */
    private $suspendedCylinder = '0000000000000000';

    /**
     * @var int
     *
     * @ORM\Column(name="liwat_critical", type="integer", nullable=false, options={"default"="100"})
     */
    private $liwatCritical = '100';

    /**
     * @var int
     *
     * @ORM\Column(name="liwat_alarm", type="integer", nullable=false, options={"default"="150"})
     */
    private $liwatAlarm = '150';

    /**
     * @var string
     *
     * @ORM\Column(name="classification_society", type="string", length=20, nullable=false)
     */
    private $classificationSociety;

    /**
     * @var int
     *
     * @ORM\Column(name="class_register_no", type="integer", nullable=false)
     */
    private $classRegisterNo = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="component", type="string", length=50, nullable=false)
     */
    private $component;

    /**
     * @var string
     *
     * @ORM\Column(name="component_licencer", type="string", length=50, nullable=false)
     */
    private $componentLicencer;

    /**
     * @var string
     *
     * @ORM\Column(name="component_maker", type="string", length=100, nullable=false)
     */
    private $componentMaker;

    /**
     * @var string
     *
     * @ORM\Column(name="component_type", type="string", length=50, nullable=false)
     */
    private $componentType;

    /**
     * @var string
     *
     * @ORM\Column(name="component_serial_no", type="string", length=50, nullable=false)
     */
    private $componentSerialNo;

    /**
     * @var string
     *
     * @ORM\Column(name="vessel_name", type="string", length=255, nullable=false, options={"comment"="Schiffsname vom GL"})
     */
    private $vesselName;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCdsSerialno(): ?string
    {
        return $this->cdsSerialno;
    }

    public function setCdsSerialno(string $cdsSerialno): self
    {
        $this->cdsSerialno = $cdsSerialno;

        return $this;
    }

    public function getMarprimeSerialno(): ?string
    {
        return $this->marprimeSerialno;
    }

    public function setMarprimeSerialno(string $marprimeSerialno): self
    {
        $this->marprimeSerialno = $marprimeSerialno;

        return $this;
    }

    public function getImoNo(): ?string
    {
        return $this->imoNo;
    }

    public function setImoNo(string $imoNo): self
    {
        $this->imoNo = $imoNo;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getLoadValue(): ?string
    {
        return $this->loadValue;
    }

    public function setLoadValue(?string $loadValue): self
    {
        $this->loadValue = $loadValue;

        return $this;
    }

    public function getLoadDate(): ?\DateTimeInterface
    {
        return $this->loadDate;
    }

    public function setLoadDate(?\DateTimeInterface $loadDate): self
    {
        $this->loadDate = $loadDate;

        return $this;
    }

    public function getReederei()//: ?string
    {
        return $this->reederei;
    }

    public function setReederei(string $reederei): self
    {
        $this->reederei = $reederei;

        return $this;
    }

    public function getShipNo(): ?int
    {
        return $this->shipNo;
    }

    public function setShipNo(?int $shipNo): self
    {
        $this->shipNo = $shipNo;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(?string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getTaufname(): ?string
    {
        return $this->taufname;
    }

    public function setTaufname(?string $taufname): self
    {
        $this->taufname = $taufname;

        return $this;
    }

    public function getAktName(): ?string
    {
        return $this->aktName;
    }

    public function setAktName(?string $aktName): self
    {
        $this->aktName = $aktName;

        return $this;
    }

    public function getZusatz(): ?string
    {
        return $this->zusatz;
    }

    public function setZusatz(?string $zusatz): self
    {
        $this->zusatz = $zusatz;

        return $this;
    }

    public function getHullNo(): ?string
    {
        return $this->hullNo;
    }

    public function setHullNo(?string $hullNo): self
    {
        $this->hullNo = $hullNo;

        return $this;
    }

    public function getYard(): ?string
    {
        return $this->yard;
    }

    public function setYard(?string $yard): self
    {
        $this->yard = $yard;

        return $this;
    }

    public function getEngine(): ?string
    {
        return $this->engine;
    }

    public function setEngine(?string $engine): self
    {
        $this->engine = $engine;

        return $this;
    }

    public function getSeatrial(): ?string
    {
        return $this->seatrial;
    }

    public function setSeatrial(?string $seatrial): self
    {
        $this->seatrial = $seatrial;

        return $this;
    }

    public function getLastMeasurement(): ?\DateTimeInterface
    {
        return $this->lastMeasurement;
    }

    public function setLastMeasurement(?\DateTimeInterface $lastMeasurement): self
    {
        $this->lastMeasurement = $lastMeasurement;

        return $this;
    }

    public function getLastUpdate(): ?\DateTimeInterface
    {
        return $this->lastUpdate;
    }

    public function setLastUpdate(?\DateTimeInterface $lastUpdate): self
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

    public function getCdsInhalt(): ?string
    {
        return $this->cdsInhalt;
    }

    public function setCdsInhalt(?string $cdsInhalt): self
    {
        $this->cdsInhalt = $cdsInhalt;

        return $this;
    }

    public function getTmsInhalt(): ?string
    {
        return $this->tmsInhalt;
    }

    public function setTmsInhalt(?string $tmsInhalt): self
    {
        $this->tmsInhalt = $tmsInhalt;

        return $this;
    }

    public function getCdsNo(): ?string
    {
        return $this->cdsNo;
    }

    public function setCdsNo(?string $cdsNo): self
    {
        $this->cdsNo = $cdsNo;

        return $this;
    }

    public function getTmsNo(): ?string
    {
        return $this->tmsNo;
    }

    public function setTmsNo(?string $tmsNo): self
    {
        $this->tmsNo = $tmsNo;

        return $this;
    }

    public function getCdsState(): ?string
    {
        return $this->cdsState;
    }

    public function setCdsState(?string $cdsState): self
    {
        $this->cdsState = $cdsState;

        return $this;
    }

    public function getTmsState(): ?string
    {
        return $this->tmsState;
    }

    public function setTmsState(?string $tmsState): self
    {
        $this->tmsState = $tmsState;

        return $this;
    }

    public function getPrmPicture(): ?string
    {
        return $this->prmPicture;
    }

    public function setPrmPicture(?string $prmPicture): self
    {
        $this->prmPicture = $prmPicture;

        return $this;
    }

    public function getCylCount(): ?int
    {
        return $this->cylCount;
    }

    public function setCylCount(?int $cylCount): self
    {
        $this->cylCount = $cylCount;

        return $this;
    }

    public function getRingCount(): ?int
    {
        return $this->ringCount;
    }

    public function setRingCount(int $ringCount): self
    {
        $this->ringCount = $ringCount;

        return $this;
    }

    public function getTechDescTopRing(): ?string
    {
        return $this->techDescTopRing;
    }

    public function setTechDescTopRing(?string $techDescTopRing): self
    {
        $this->techDescTopRing = $techDescTopRing;

        return $this;
    }

    public function getSpecFuel(): ?string
    {
        return $this->specFuel;
    }

    public function setSpecFuel(?string $specFuel): self
    {
        $this->specFuel = $specFuel;

        return $this;
    }

    public function getSpecDate(): ?string
    {
        return $this->specDate;
    }

    public function setSpecDate(?string $specDate): self
    {
        $this->specDate = $specDate;

        return $this;
    }

    public function getSpecN(): ?string
    {
        return $this->specN;
    }

    public function setSpecN(?string $specN): self
    {
        $this->specN = $specN;

        return $this;
    }

    public function getSpecPic(): ?int
    {
        return $this->specPic;
    }

    public function setSpecPic($specPic): self
    {
        $this->specPic = $specPic;

        return $this;
    }

    public function getVorschau(): ?int
    {
        return $this->vorschau;
    }

    public function setVorschau(int $vorschau): self
    {
        $this->vorschau = $vorschau;

        return $this;
    }

    public function getAbo(): ?int
    {
        return $this->abo;
    }

    public function setAbo(int $abo): self
    {
        $this->abo = $abo;

        return $this;
    }

    public function getGl(): ?int
    {
        return $this->gl;
    }

    public function setGl($gl): self
    {
        $this->gl = $gl;

        return $this;
    }

    public function getBuildYear(): ?int
    {
        return $this->buildYear;
    }

    public function setBuildYear(int $buildYear): self
    {
        $this->buildYear = $buildYear;

        return $this;
    }

    public function getInspection(): ?string
    {
        return $this->inspection;
    }

    public function setInspection(?string $inspection): self
    {
        $this->inspection = $inspection;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getPraLinerTempAlarm(): ?float
    {
        return $this->praLinerTempAlarm;
    }

    public function setPraLinerTempAlarm(float $praLinerTempAlarm): self
    {
        $this->praLinerTempAlarm = $praLinerTempAlarm;

        return $this;
    }

    public function getPraLinerTempCritical(): ?float
    {
        return $this->praLinerTempCritical;
    }

    public function setPraLinerTempCritical(float $praLinerTempCritical): self
    {
        $this->praLinerTempCritical = $praLinerTempCritical;

        return $this;
    }

    public function getPraThermalLoadAlarm(): ?int//bool

    {
        return $this->praThermalLoadAlarm;
    }

    public function setPraThermalLoadAlarm(int $praThermalLoadAlarm): self
    {
        $this->praThermalLoadAlarm = $praThermalLoadAlarm;

        return $this;
    }

    public function getPraThermalLoadCritical(): ?int//bool

    {
        return $this->praThermalLoadCritical;
    }

    public function setPraThermalLoadCritical(int $praThermalLoadCritical): self
    {
        $this->praThermalLoadCritical = $praThermalLoadCritical;

        return $this;
    }

    public function getMaxPower(): ?float
    {
        return $this->maxPower;
    }

    public function setMaxPower(?float $maxPower): self
    {
        $this->maxPower = $maxPower;

        return $this;
    }

    public function getRpm100(): ?float
    {
        return $this->rpm100;
    }

    public function setRpm100(float $rpm100): self
    {
        $this->rpm100 = $rpm100;

        return $this;
    }

    public function getCdsAktiv(): ?bool
    {
        return $this->cdsAktiv;
    }

    public function setCdsAktiv(bool $cdsAktiv): self
    {
        $this->cdsAktiv = $cdsAktiv;

        return $this;
    }

    public function getLmsAktiv(): ?bool
    {
        return $this->lmsAktiv;
    }

    public function setLmsAktiv(bool $lmsAktiv): self
    {
        $this->lmsAktiv = $lmsAktiv;

        return $this;
    }

    public function getPraAktiv(): ?bool
    {
        return $this->praAktiv;
    }

    public function setPraAktiv(bool $praAktiv): self
    {
        $this->praAktiv = $praAktiv;

        return $this;
    }

    public function getRunAktiv(): ?bool
    {
        return $this->runAktiv;
    }

    public function setRunAktiv(bool $runAktiv): self
    {
        $this->runAktiv = $runAktiv;

        return $this;
    }

    public function getCpaAktiv(): ?bool
    {
        return $this->cpaAktiv;
    }

    public function setCpaAktiv(bool $cpaAktiv): self
    {
        $this->cpaAktiv = $cpaAktiv;

        return $this;
    }

    public function getIpaAktiv(): ?bool
    {
        return $this->ipaAktiv;
    }

    public function setIpaAktiv(bool $ipaAktiv): self
    {
        $this->ipaAktiv = $ipaAktiv;

        return $this;
    }

    public function getMarprimeAktiv(): ?bool
    {
        return $this->marprimeAktiv;
    }

    public function setMarprimeAktiv(bool $marprimeAktiv): self
    {
        $this->marprimeAktiv = $marprimeAktiv;

        return $this;
    }

    public function getLiwatAktiv(): ?bool
    {
        return $this->liwatAktiv;
    }

    public function setLiwatAktiv(bool $liwatAktiv): self
    {
        $this->liwatAktiv = $liwatAktiv;

        return $this;
    }

    public function getMartorqueAktiv(): ?bool
    {
        return $this->martorqueAktiv;
    }

    public function setMartorqueAktiv(bool $martorqueAktiv): self
    {
        $this->martorqueAktiv = $martorqueAktiv;

        return $this;
    }

    public function getNewRing(): ?float
    {
        return $this->newRing;
    }

    public function setNewRing(float $newRing): self
    {
        $this->newRing = $newRing;

        return $this;
    }

    public function getWornRing(): ?float
    {
        return $this->wornRing;
    }

    public function setWornRing(float $wornRing): self
    {
        $this->wornRing = $wornRing;

        return $this;
    }

    public function getSulzerNewRing(): ?float
    {
        return $this->sulzerNewRing;
    }

    public function setSulzerNewRing(float $sulzerNewRing): self
    {
        $this->sulzerNewRing = $sulzerNewRing;

        return $this;
    }

    public function getRingKorrektur(): ?float
    {
        return $this->ringKorrektur;
    }

    public function setRingKorrektur(float $ringKorrektur): self
    {
        $this->ringKorrektur = $ringKorrektur;

        return $this;
    }

    public function getKorrekturfaktor(): ?float
    {
        return $this->korrekturfaktor;
    }

    public function setKorrekturfaktor(float $korrekturfaktor): self
    {
        $this->korrekturfaktor = $korrekturfaktor;

        return $this;
    }

    public function getCpaDeviationCompPressure(): ?float
    {
        return $this->cpaDeviationCompPressure;
    }

    public function setCpaDeviationCompPressure(float $cpaDeviationCompPressure): self
    {
        $this->cpaDeviationCompPressure = $cpaDeviationCompPressure;

        return $this;
    }

    public function getCpaDeviationMaxPress(): ?float
    {
        return $this->cpaDeviationMaxPress;
    }

    public function setCpaDeviationMaxPress(float $cpaDeviationMaxPress): self
    {
        $this->cpaDeviationMaxPress = $cpaDeviationMaxPress;

        return $this;
    }

    public function getCpaDeviationMeanIndPress(): ?float
    {
        return $this->cpaDeviationMeanIndPress;
    }

    public function setCpaDeviationMeanIndPress(float $cpaDeviationMeanIndPress): self
    {
        $this->cpaDeviationMeanIndPress = $cpaDeviationMeanIndPress;

        return $this;
    }

    public function getCpaDeviationIndPower(): ?float
    {
        return $this->cpaDeviationIndPower;
    }

    public function setCpaDeviationIndPower(float $cpaDeviationIndPower): self
    {
        $this->cpaDeviationIndPower = $cpaDeviationIndPower;

        return $this;
    }

    public function getCpaDeviationScavAirPress(): ?float
    {
        return $this->cpaDeviationScavAirPress;
    }

    public function setCpaDeviationScavAirPress(float $cpaDeviationScavAirPress): self
    {
        $this->cpaDeviationScavAirPress = $cpaDeviationScavAirPress;

        return $this;
    }

    public function getLoadBalanceMax(): ?float
    {
        return $this->loadBalanceMax;
    }

    public function setLoadBalanceMax(float $loadBalanceMax): self
    {
        $this->loadBalanceMax = $loadBalanceMax;

        return $this;
    }

    public function getLoadBalanceAlarm(): ?bool
    {
        return $this->loadBalanceAlarm;
    }

    public function setLoadBalanceAlarm(bool $loadBalanceAlarm): self
    {
        $this->loadBalanceAlarm = $loadBalanceAlarm;

        return $this;
    }

    public function getShowDiagrams()
    {
        return $this->showDiagrams;
    }

    public function setShowDiagrams($showDiagrams): self
    {
        $this->showDiagrams = $showDiagrams;

        return $this;
    }

    public function getSuspendedCylinder()
    {
        return $this->suspendedCylinder;
    }

    public function setSuspendedCylinder($suspendedCylinder): self
    {
        $this->suspendedCylinder = $suspendedCylinder;

        return $this;
    }

    public function getLiwatCritical(): ?int
    {
        return $this->liwatCritical;
    }

    public function setLiwatCritical(int $liwatCritical): self
    {
        $this->liwatCritical = $liwatCritical;

        return $this;
    }

    public function getLiwatAlarm(): ?int
    {
        return $this->liwatAlarm;
    }

    public function setLiwatAlarm(int $liwatAlarm): self
    {
        $this->liwatAlarm = $liwatAlarm;

        return $this;
    }

    public function getClassificationSociety(): ?string
    {
        return $this->classificationSociety;
    }

    public function setClassificationSociety(string $classificationSociety): self
    {
        $this->classificationSociety = $classificationSociety;

        return $this;
    }

    public function getClassRegisterNo(): ?int
    {
        return $this->classRegisterNo;
    }

    public function setClassRegisterNo(int $classRegisterNo): self
    {
        $this->classRegisterNo = $classRegisterNo;

        return $this;
    }

    public function getComponent(): ?string
    {
        return $this->component;
    }

    public function setComponent(string $component): self
    {
        $this->component = $component;

        return $this;
    }

    public function getComponentLicencer(): ?string
    {
        return $this->componentLicencer;
    }

    public function setComponentLicencer(string $componentLicencer): self
    {
        $this->componentLicencer = $componentLicencer;

        return $this;
    }

    public function getComponentMaker(): ?string
    {
        return $this->componentMaker;
    }

    public function setComponentMaker(string $componentMaker): self
    {
        $this->componentMaker = $componentMaker;

        return $this;
    }

    public function getComponentType(): ?string
    {
        return $this->componentType;
    }

    public function setComponentType(string $componentType): self
    {
        $this->componentType = $componentType;

        return $this;
    }

    public function getComponentSerialNo(): ?string
    {
        return $this->componentSerialNo;
    }

    public function setComponentSerialNo(string $componentSerialNo): self
    {
        $this->componentSerialNo = $componentSerialNo;

        return $this;
    }

    public function getVesselName(): ?string
    {
        return $this->vesselName;
    }

    public function setVesselName(string $vesselName): self
    {
        $this->vesselName = $vesselName;

        return $this;
    }

    /**
     * haben in der DB leider mal ein \n am Ende einiger nummern :-(
     * @return string
     */
    public function getCleanMarprimeSerialNumber()
    {
        return preg_replace('/[^0-9\.]/', '', $this->getMarprimeSerialno());
    }

    // public function getShippingCompany()
    // {
    //     return;
    // }
}
