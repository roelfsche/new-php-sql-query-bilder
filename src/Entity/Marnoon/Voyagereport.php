<?php

namespace App\Entity\Marnoon;

use App\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;
use App\Kohana\Arr;

/**
 * Voyagereport
 *
 * @ORM\Table(name="voyagereport")
 * @ORM\Entity(repositoryClass="App\Repository\Marnoon\VoyagereportsRepository")
 */
class Voyagereport extends BaseEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="ID", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="IMO", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $imo;

    /**
     * @var string|null
     *
     * @ORM\Column(name="Vessel", type="string", length=100, nullable=true)
     */
    private $vessel;

    /**
     * @var string|null
     *
     * @ORM\Column(name="EngineTyp", type="string", length=100, nullable=true)
     */
    private $enginetyp;

    /**
     * @var string|null
     *
     * @ORM\Column(name="LastEntry", type="string", length=100, nullable=true)
     */
    private $lastentry;

    /**
     * @var string|null
     *
     * @ORM\Column(name="LastDataSend", type="string", length=100, nullable=true)
     */
    private $lastdatasend;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="date", type="date", nullable=true)
     */
    private $date;

    /**
     * @var int
     *
     * @ORM\Column(name="date_ID", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $dateId = '0';

    /**
     * @var string|null
     *
     * @ORM\Column(name="Captain", type="string", length=100, nullable=true)
     */
    private $captain;

    /**
     * @var string|null
     *
     * @ORM\Column(name="ChiefEng", type="string", length=100, nullable=true)
     */
    private $chiefeng;

    /**
     * @var string|null
     *
     * @ORM\Column(name="VoyNumber", type="string", length=100, nullable=true)
     */
    private $voynumber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="VoyFrom", type="string", length=100, nullable=true)
     */
    private $voyfrom;

    /**
     * @var string|null
     *
     * @ORM\Column(name="VoyTo", type="string", length=100, nullable=true)
     */
    private $voyto;

    /**
     * @var string|null
     *
     * @ORM\Column(name="Arrival", type="string", length=100, nullable=true)
     */
    private $arrival;

    /**
     * @var string|null
     *
     * @ORM\Column(name="Departure", type="string", length=100, nullable=true)
     */
    private $departure;

    /**
     * @var string|null
     *
     * @ORM\Column(name="VoyStart", type="string", length=100, nullable=true)
     */
    private $voystart;

    /**
     * @var string|null
     *
     * @ORM\Column(name="VoyEnd", type="string", length=100, nullable=true)
     */
    private $voyend;

    /**
     * @var float|null
     *
     * @ORM\Column(name="TimeAtSea", type="float", precision=10, scale=0, nullable=true)
     */
    private $timeatsea;

    /**
     * @var float|null
     *
     * @ORM\Column(name="TimeAtRiver", type="float", precision=10, scale=0, nullable=true)
     */
    private $timeatriver;

    /**
     * @var float|null
     *
     * @ORM\Column(name="TimeAtPort", type="float", precision=10, scale=0, nullable=true)
     */
    private $timeatport;

    /**
     * @var string|null
     *
     * @ORM\Column(name="NoonPos", type="string", length=100, nullable=true)
     */
    private $noonpos;

    /**
     * @var int|null
     *
     * @ORM\Column(name="RiverMiles", type="integer", nullable=true)
     */
    private $rivermiles;

    /**
     * @var int|null
     *
     * @ORM\Column(name="SeaMiles", type="integer", nullable=true)
     */
    private $seamiles;

    /**
     * @var int|null
     *
     * @ORM\Column(name="MilesCount", type="integer", nullable=true)
     */
    private $milescount;

    /**
     * @var float|null
     *
     * @ORM\Column(name="TheoMiles", type="float", precision=10, scale=0, nullable=true)
     */
    private $theomiles;

    /**
     * @var int|null
     *
     * @ORM\Column(name="MilesThroughWater", type="integer", nullable=true)
     */
    private $milesthroughwater;

    /**
     * @var float|null
     *
     * @ORM\Column(name="SpeedThroughWater", type="float", precision=10, scale=0, nullable=true)
     */
    private $speedthroughwater;

    /**
     * @var float|null
     *
     * @ORM\Column(name="SpeedOverGround", type="float", precision=10, scale=0, nullable=true)
     */
    private $speedoverground;

    /**
     * @var float|null
     *
     * @ORM\Column(name="SlipThroughWater", type="float", precision=10, scale=0, nullable=true)
     */
    private $slipthroughwater;

    /**
     * @var float|null
     *
     * @ORM\Column(name="Slip", type="float", precision=10, scale=0, nullable=true)
     */
    private $slip;

    /**
     * @var float|null
     *
     * @ORM\Column(name="CargoTotal", type="float", precision=10, scale=0, nullable=true)
     */
    private $cargototal;

    /**
     * @var string|null
     *
     * @ORM\Column(name="CargoInHold", type="string", length=100, nullable=true)
     */
    private $cargoinhold;

    /**
     * @var string|null
     *
     * @ORM\Column(name="CargoType", type="string", length=100, nullable=true)
     */
    private $cargotype;

    /**
     * @var float|null
     *
     * @ORM\Column(name="DraftFore", type="float", precision=10, scale=0, nullable=true)
     */
    private $draftfore;

    /**
     * @var float|null
     *
     * @ORM\Column(name="DraftAft", type="float", precision=10, scale=0, nullable=true)
     */
    private $draftaft;

    /**
     * @var string|null
     *
     * @ORM\Column(name="WindForce", type="string", length=100, nullable=true)
     */
    private $windforce;

    /**
     * @var int|null
     *
     * @ORM\Column(name="WindDirToVessel", type="integer", nullable=true)
     */
    private $winddirtovessel;

    /**
     * @var string|null
     *
     * @ORM\Column(name="SeaScale", type="string", length=100, nullable=true)
     */
    private $seascale;

    /**
     * @var int|null
     *
     * @ORM\Column(name="SeaScaleToVessel", type="integer", nullable=true)
     */
    private $seascaletovessel;

    /**
     * @var int|null
     *
     * @ORM\Column(name="MERevCount", type="integer", nullable=true)
     */
    private $merevcount;

    /**
     * @var float|null
     *
     * @ORM\Column(name="MESpeedAvg", type="float", precision=10, scale=0, nullable=true)
     */
    private $mespeedavg;

    /**
     * @var int|null
     *
     * @ORM\Column(name="MEPowerCount", type="integer", nullable=true)
     */
    private $mepowercount;

    /**
     * @var float|null
     *
     * @ORM\Column(name="MEPowerAvg", type="float", precision=10, scale=0, nullable=true)
     */
    private $mepoweravg;

    /**
     * @var int|null
     *
     * @ORM\Column(name="MEFuelCount", type="integer", nullable=true)
     */
    private $mefuelcount;

    /**
     * @var float|null
     *
     * @ORM\Column(name="MEFuelDensity", type="float", precision=10, scale=0, nullable=true)
     */
    private $mefueldensity;

    /**
     * @var float|null
     *
     * @ORM\Column(name="MEFuelOilConsum", type="float", precision=10, scale=0, nullable=true)
     */
    private $mefueloilconsum;

    /**
     * @var float|null
     *
     * @ORM\Column(name="MESFOC", type="float", precision=10, scale=0, nullable=true)
     */
    private $mesfoc;

    /**
     * @var string
     *
     * @ORM\Column(name="MEFuelType", type="string", length=20, nullable=false)
     */
    private $mefueltype;

    /**
     * @var int|null
     *
     * @ORM\Column(name="MECylOilInput", type="integer", nullable=true)
     */
    private $mecyloilinput;

    /**
     * @var float|null
     *
     * @ORM\Column(name="MECylOilDensity", type="float", precision=10, scale=0, nullable=true)
     */
    private $mecyloildensity;

    /**
     * @var float|null
     *
     * @ORM\Column(name="MECylOilConsum", type="float", precision=10, scale=0, nullable=true)
     */
    private $mecyloilconsum;

    /**
     * @var int|null
     *
     * @ORM\Column(name="MELubOilInput", type="integer", nullable=true)
     */
    private $meluboilinput;

    /**
     * @var int|null
     *
     * @ORM\Column(name="FuelPumpIndex", type="integer", nullable=true)
     */
    private $fuelpumpindex;

    /**
     * @var int|null
     *
     * @ORM\Column(name="METurboRpm", type="integer", nullable=true)
     */
    private $meturborpm;

    /**
     * @var float|null
     *
     * @ORM\Column(name="Pitch", type="float", precision=10, scale=0, nullable=true)
     */
    private $pitch;

    /**
     * @var string|null
     *
     * @ORM\Column(name="AEinUse", type="string", length=100, nullable=true)
     */
    private $aeinuse;

    /**
     * @var int|null
     *
     * @ORM\Column(name="AEPower", type="integer", nullable=true)
     */
    private $aepower;

    /**
     * @var int|null
     *
     * @ORM\Column(name="AEFuelInput", type="integer", nullable=true)
     */
    private $aefuelinput;

    /**
     * @var int|null
     *
     * @ORM\Column(name="AEFuelOutput", type="integer", nullable=true)
     */
    private $aefueloutput;

    /**
     * @var float|null
     *
     * @ORM\Column(name="AEFuelDensity", type="float", precision=10, scale=0, nullable=true)
     */
    private $aefueldensity;

    /**
     * @var float|null
     *
     * @ORM\Column(name="AEFuelOilConsum", type="float", precision=10, scale=0, nullable=true)
     */
    private $aefueloilconsum;

    /**
     * @var float|null
     *
     * @ORM\Column(name="AESFOC", type="float", precision=10, scale=0, nullable=true)
     */
    private $aesfoc;

    /**
     * @var int|null
     *
     * @ORM\Column(name="AELubOilInput", type="integer", nullable=true)
     */
    private $aeluboilinput;

    /**
     * @var string
     *
     * @ORM\Column(name="AEFuelType", type="string", length=20, nullable=false)
     */
    private $aefueltype;

    /**
     * @var int|null
     *
     * @ORM\Column(name="BoilerFuelCount", type="integer", nullable=true)
     */
    private $boilerfuelcount;

    /**
     * @var float|null
     *
     * @ORM\Column(name="BoilerFuelDensity", type="float", precision=10, scale=0, nullable=true)
     */
    private $boilerfueldensity;

    /**
     * @var float|null
     *
     * @ORM\Column(name="BoilerFuelConsum", type="float", precision=10, scale=0, nullable=true)
     */
    private $boilerfuelconsum;

    /**
     * @var string
     *
     * @ORM\Column(name="BoilerFuelType", type="string", length=20, nullable=false)
     */
    private $boilerfueltype;

    /**
     * @var float|null
     *
     * fülle ich selber über get/set
     * initial DB expr: (MEFuelOilConsum + AEFuelOilConsum + BoilerFuelConsum) / 1000
     */
    private $overallfueloilconsumption = 0.0;

    /**
     * @var int
     *
     * fülle ich selber über get/set
     * initial DB expr: (SeaMiles + RiverMiles)
     */
    private $overallseamiles = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImo(): ?int
    {
        return $this->imo;
    }

    public function getVessel(): ?string
    {
        return $this->vessel;
    }

    public function setVessel(?string $vessel): self
    {
        $this->vessel = $vessel;

        return $this;
    }

    public function getEnginetyp(): ?string
    {
        return $this->enginetyp;
    }

    public function setEnginetyp(?string $enginetyp): self
    {
        $this->enginetyp = $enginetyp;

        return $this;
    }

    public function getLastentry(): ?string
    {
        return $this->lastentry;
    }

    public function setLastentry(?string $lastentry): self
    {
        $this->lastentry = $lastentry;

        return $this;
    }

    public function getLastdatasend(): ?string
    {
        return $this->lastdatasend;
    }

    public function setLastdatasend(?string $lastdatasend): self
    {
        $this->lastdatasend = $lastdatasend;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDateId(): ?int
    {
        return $this->dateId;
    }

    public function getCaptain(): ?string
    {
        return $this->captain;
    }

    public function setCaptain(?string $captain): self
    {
        $this->captain = $captain;

        return $this;
    }

    public function getChiefeng(): ?string
    {
        return $this->chiefeng;
    }

    public function setChiefeng(?string $chiefeng): self
    {
        $this->chiefeng = $chiefeng;

        return $this;
    }

    public function getVoynumber(): ?string
    {
        return $this->voynumber;
    }

    public function setVoynumber(?string $voynumber): self
    {
        $this->voynumber = $voynumber;

        return $this;
    }

    public function getVoyfrom(): ?string
    {
        return $this->voyfrom;
    }

    public function setVoyfrom(?string $voyfrom): self
    {
        $this->voyfrom = $voyfrom;

        return $this;
    }

    public function getVoyto(): ?string
    {
        return $this->voyto;
    }

    public function setVoyto(?string $voyto): self
    {
        $this->voyto = $voyto;

        return $this;
    }

    public function getArrival(): ?string
    {
        return $this->arrival;
    }

    public function setArrival(?string $arrival): self
    {
        $this->arrival = $arrival;

        return $this;
    }

    public function getDeparture(): ?string
    {
        return $this->departure;
    }

    public function setDeparture(?string $departure): self
    {
        $this->departure = $departure;

        return $this;
    }

    public function getVoystart(): ?string
    {
        return $this->voystart;
    }

    public function setVoystart(?string $voystart): self
    {
        $this->voystart = $voystart;

        return $this;
    }

    public function getVoyend(): ?string
    {
        return $this->voyend;
    }

    public function setVoyend(?string $voyend): self
    {
        $this->voyend = $voyend;

        return $this;
    }

    public function getTimeatsea(): ?float
    {
        return $this->timeatsea;
    }

    public function setTimeatsea(?float $timeatsea): self
    {
        $this->timeatsea = $timeatsea;

        return $this;
    }

    public function addTimeatsea(?float $timeatsea): self
    {
        $this->timeatsea += $timeatsea;

        return $this;
    }

    public function getTimeatriver(): ?float
    {
        return $this->timeatriver;
    }

    public function setTimeatriver(?float $timeatriver): self
    {
        $this->timeatriver = $timeatriver;

        return $this;
    }

    public function addTimeatriver(?float $timeatriver): self
    {
        $this->timeatriver += $timeatriver;

        return $this;
    }

    public function getTimeatport(): ?float
    {
        return $this->timeatport;
    }

    public function setTimeatport(?float $timeatport): self
    {
        $this->timeatport = $timeatport;

        return $this;
    }

    public function addTimeatport(?float $timeatport): self
    {
        $this->timeatport += $timeatport;

        return $this;
    }

    public function getNoonpos(): ?string
    {
        return $this->noonpos;
    }

    public function setNoonpos(?string $noonpos): self
    {
        $this->noonpos = $noonpos;

        return $this;
    }

    public function getRivermiles(): ?int
    {
        return $this->rivermiles;
    }

    public function setRivermiles(?int $rivermiles): self
    {
        $this->rivermiles = $rivermiles;

        return $this;
    }
    public function addRivermiles(?int $rivermiles): self
    {
        $this->rivermiles += $rivermiles;

        return $this;
    }

    public function getSeamiles(): ?int
    {
        return $this->seamiles;
    }

    public function setSeamiles(?int $seamiles): self
    {
        $this->seamiles = $seamiles;

        return $this;
    }

    public function addSeamiles(?int $seamiles): self
    {
        $this->seamiles += $seamiles;

        return $this;
    }

    public function getMilescount(): ?int
    {
        return $this->milescount;
    }

    public function setMilescount(?int $milescount): self
    {
        $this->milescount = $milescount;

        return $this;
    }

    public function getTheomiles(): ?float
    {
        return $this->theomiles;
    }

    public function setTheomiles(?float $theomiles): self
    {
        $this->theomiles = $theomiles;

        return $this;
    }

    public function addTheomiles(?float $theomiles): self
    {
        $this->theomiles += $theomiles;

        return $this;
    }

    public function getMilesthroughwater(): ?int
    {
        return $this->milesthroughwater;
    }

    public function setMilesthroughwater(?int $milesthroughwater): self
    {
        $this->milesthroughwater = $milesthroughwater;

        return $this;
    }

    public function getSpeedthroughwater(): ?float
    {
        return $this->speedthroughwater;
    }

    public function setSpeedthroughwater(?float $speedthroughwater): self
    {
        $this->speedthroughwater = $speedthroughwater;

        return $this;
    }

    public function addSpeedthroughwater(?float $speedthroughwater): self
    {
        $this->speedthroughwater += $speedthroughwater;

        return $this;
    }

    public function getSpeedoverground(): ?float
    {
        return $this->speedoverground;
    }

    public function setSpeedoverground(?float $speedoverground): self
    {
        $this->speedoverground = $speedoverground;

        return $this;
    }

    public function getSlipthroughwater(): ?float
    {
        return $this->slipthroughwater;
    }

    public function setSlipthroughwater(?float $slipthroughwater): self
    {
        $this->slipthroughwater = $slipthroughwater;

        return $this;
    }
    public function addSlipthroughwater(?float $slipthroughwater): self
    {
        $this->slipthroughwater += $slipthroughwater;

        return $this;
    }

    public function getSlip(): ?float
    {
        return $this->slip;
    }

    public function setSlip(?float $slip): self
    {
        $this->slip = $slip;

        return $this;
    }

    public function getCargototal(): ?float
    {
        return $this->cargototal;
    }

    public function setCargototal(?float $cargototal): self
    {
        $this->cargototal = $cargototal;

        return $this;
    }

    public function getCargoinhold(): ?string
    {
        return $this->cargoinhold;
    }

    public function setCargoinhold(?string $cargoinhold): self
    {
        $this->cargoinhold = $cargoinhold;

        return $this;
    }

    public function getCargotype(): ?string
    {
        return $this->cargotype;
    }

    public function setCargotype(?string $cargotype): self
    {
        $this->cargotype = $cargotype;

        return $this;
    }

    public function getDraftfore(): ?float
    {
        return $this->draftfore;
    }

    public function setDraftfore(?float $draftfore): self
    {
        $this->draftfore = $draftfore;

        return $this;
    }

    public function getDraftaft(): ?float
    {
        return $this->draftaft;
    }

    public function setDraftaft(?float $draftaft): self
    {
        $this->draftaft = $draftaft;

        return $this;
    }

    public function getWindforce(): ?string
    {
        return $this->windforce;
    }

    public function setWindforce(?string $windforce): self
    {
        $this->windforce = $windforce;

        return $this;
    }

    public function getWinddirtovessel(): ?int
    {
        return $this->winddirtovessel;
    }

    public function setWinddirtovessel(?int $winddirtovessel): self
    {
        $this->winddirtovessel = $winddirtovessel;

        return $this;
    }

    public function getSeascale(): ?string
    {
        return $this->seascale;
    }

    public function setSeascale(?string $seascale): self
    {
        $this->seascale = $seascale;

        return $this;
    }

    public function getSeascaletovessel(): ?int
    {
        return $this->seascaletovessel;
    }

    public function setSeascaletovessel(?int $seascaletovessel): self
    {
        $this->seascaletovessel = $seascaletovessel;

        return $this;
    }

    public function getMerevcount(): ?int
    {
        return $this->merevcount;
    }

    public function setMerevcount(?int $merevcount): self
    {
        $this->merevcount = $merevcount;

        return $this;
    }

    public function getMespeedavg(): ?float
    {
        return $this->mespeedavg;
    }

    public function setMespeedavg(?float $mespeedavg): self
    {
        $this->mespeedavg = $mespeedavg;

        return $this;
    }

    public function getMepowercount(): ?int
    {
        return $this->mepowercount;
    }

    public function setMepowercount(?int $mepowercount): self
    {
        $this->mepowercount = $mepowercount;

        return $this;
    }

    public function getMepoweravg(): ?float
    {
        return $this->mepoweravg;
    }

    public function setMepoweravg(?float $mepoweravg): self
    {
        $this->mepoweravg = $mepoweravg;

        return $this;
    }

    public function getMefuelcount(): ?int
    {
        return $this->mefuelcount;
    }

    public function setMefuelcount(?int $mefuelcount): self
    {
        $this->mefuelcount = $mefuelcount;

        return $this;
    }

    public function getMefueldensity(): ?float
    {
        return $this->mefueldensity;
    }

    public function setMefueldensity(?float $mefueldensity): self
    {
        $this->mefueldensity = $mefueldensity;

        return $this;
    }

    public function getMefueloilconsum(): ?float
    {
        return $this->mefueloilconsum;
    }

    public function setMefueloilconsum(?float $mefueloilconsum): self
    {
        $this->mefueloilconsum = $mefueloilconsum;

        return $this;
    }
    public function addMefueloilconsum(?float $mefueloilconsum): self
    {
        $this->mefueloilconsum += $mefueloilconsum;

        return $this;
    }

    public function getMesfoc(): ?float
    {
        return $this->mesfoc;
    }

    public function setMesfoc(?float $mesfoc): self
    {
        $this->mesfoc = $mesfoc;

        return $this;
    }

    public function getMefueltype(): ?string
    {
        return $this->mefueltype;
    }

    public function setMefueltype(string $mefueltype): self
    {
        $this->mefueltype = $mefueltype;

        return $this;
    }

    public function getMecyloilinput(): ?int
    {
        return $this->mecyloilinput;
    }

    public function setMecyloilinput(?int $mecyloilinput): self
    {
        $this->mecyloilinput = $mecyloilinput;

        return $this;
    }

    public function addMecyloilinput(?int $mecyloilinput): self
    {
        $this->mecyloilinput += $mecyloilinput;

        return $this;
    }

    public function getMecyloildensity(): ?float
    {
        return $this->mecyloildensity;
    }

    public function setMecyloildensity(?float $mecyloildensity): self
    {
        $this->mecyloildensity = $mecyloildensity;

        return $this;
    }

    public function getMecyloilconsum(): ?float
    {
        return $this->mecyloilconsum;
    }

    public function setMecyloilconsum(?float $mecyloilconsum): self
    {
        $this->mecyloilconsum = $mecyloilconsum;

        return $this;
    }

    public function getMeluboilinput(): ?int
    {
        return $this->meluboilinput;
    }

    public function setMeluboilinput(?int $meluboilinput): self
    {
        $this->meluboilinput = $meluboilinput;

        return $this;
    }

    public function getFuelpumpindex(): ?int
    {
        return $this->fuelpumpindex;
    }

    public function setFuelpumpindex(?int $fuelpumpindex): self
    {
        $this->fuelpumpindex = $fuelpumpindex;

        return $this;
    }

    public function getMeturborpm(): ?int
    {
        return $this->meturborpm;
    }

    public function setMeturborpm(?int $meturborpm): self
    {
        $this->meturborpm = $meturborpm;

        return $this;
    }

    public function getPitch(): ?float
    {
        return $this->pitch;
    }

    public function setPitch(?float $pitch): self
    {
        $this->pitch = $pitch;

        return $this;
    }

    public function getAeinuse(): ?string
    {
        return $this->aeinuse;
    }

    public function setAeinuse(?string $aeinuse): self
    {
        $this->aeinuse = $aeinuse;

        return $this;
    }

    public function getAepower(): ?int
    {
        return $this->aepower;
    }

    public function setAepower(?int $aepower): self
    {
        $this->aepower = $aepower;

        return $this;
    }

    public function getAefuelinput(): ?int
    {
        return $this->aefuelinput;
    }

    public function setAefuelinput(?int $aefuelinput): self
    {
        $this->aefuelinput = $aefuelinput;

        return $this;
    }

    public function getAefueloutput(): ?int
    {
        return $this->aefueloutput;
    }

    public function setAefueloutput(?int $aefueloutput): self
    {
        $this->aefueloutput = $aefueloutput;

        return $this;
    }

    public function getAefueldensity(): ?float
    {
        return $this->aefueldensity;
    }

    public function setAefueldensity(?float $aefueldensity): self
    {
        $this->aefueldensity = $aefueldensity;

        return $this;
    }

    public function getAefueloilconsum(): ?float
    {
        return $this->aefueloilconsum;
    }

    public function setAefueloilconsum(?float $aefueloilconsum): self
    {
        $this->aefueloilconsum = $aefueloilconsum;

        return $this;
    }

    public function getAesfoc(): ?float
    {
        return $this->aesfoc;
    }

    public function setAesfoc(?float $aesfoc): self
    {
        $this->aesfoc = $aesfoc;

        return $this;
    }

    public function getAeluboilinput(): ?int
    {
        return $this->aeluboilinput;
    }

    public function setAeluboilinput(?int $aeluboilinput): self
    {
        $this->aeluboilinput = $aeluboilinput;

        return $this;
    }

    public function addAeluboilinput(?int $aeluboilinput): self
    {
        $this->aeluboilinput += $aeluboilinput;

        return $this;
    }

    public function getAefueltype(): ?string
    {
        return $this->aefueltype;
    }

    public function setAefueltype(string $aefueltype): self
    {
        $this->aefueltype = $aefueltype;

        return $this;
    }

    public function getBoilerfuelcount(): ?int
    {
        return $this->boilerfuelcount;
    }

    public function setBoilerfuelcount(?int $boilerfuelcount): self
    {
        $this->boilerfuelcount = $boilerfuelcount;

        return $this;
    }

    public function getBoilerfueldensity(): ?float
    {
        return $this->boilerfueldensity;
    }

    public function setBoilerfueldensity(?float $boilerfueldensity): self
    {
        $this->boilerfueldensity = $boilerfueldensity;

        return $this;
    }

    public function getBoilerfuelconsum(): ?float
    {
        return $this->boilerfuelconsum;
    }

    public function setBoilerfuelconsum(?float $boilerfuelconsum): self
    {
        $this->boilerfuelconsum = $boilerfuelconsum;

        return $this;
    }

    public function getBoilerfueltype(): ?string
    {
        return $this->boilerfueltype;
    }

    public function setBoilerfueltype(string $boilerfueltype): self
    {
        $this->boilerfueltype = $boilerfueltype;

        return $this;
    }

    public function getOverallFuelOilConsumption(): ?float
    {
        if ($this->overallfueloilconsumption === 0.0) {
            $this->overallfueloilconsumption = ($this->mefueloilconsum + $this->aefueloilconsum + $this->boilerfuelconsum) / 1000;
        }
        return $this->overallfueloilconsumption;
    }

    public function setOverallFuelOilConsumtpion($floatValue)
    {
        $this->overallfueloilconsumption = $floatValue;
        return $this;
    }

    public function addOverallFuelOilConsumption($floatValue)
    {
        $this->overallfueloilconsumption = $this->getOverallFuelOilConsumption() + $floatValue;
        return $this;
    }

    public function getOverallSeaMiles(): ?int
    {
        if ($this->overallseamiles === 0) {
            $this->overallseamiles = $this->seamiles + $this->rivermiles;
        }
        return $this->overallseamiles;
    }

    public function setOverallSeaMiles($intValue)
    {
        $this->overallseamiles = $intValue;
        return $this;
    }
    public function addOverallSeaMiles($intValue)
    {
        echo $intValue . "\n";
        $this->overallseamiles = $this->getOverallSeaMiles() + $intValue;
        return $this;
    }
    /**
     * extrahiert aus den Werten für me|ae|boiler_fuel_type den Wert.
     * Typischerweise steht immer was drin wie: LPG=3.114 o.ä
     * @param $strValue
     * @return mixed
     */
    public function matchType($strValue)
    {
        $arrMatches = array();
        preg_match('/[0-9\.]+$/', $strValue, $arrMatches);
        return (float) Arr::get($arrMatches, 0, 0.0);
    }

}
