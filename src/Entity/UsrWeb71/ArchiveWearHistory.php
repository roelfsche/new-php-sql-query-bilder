<?php

namespace App\Entity\UsrWeb71;

use Doctrine\ORM\Mapping as ORM;

/**
 * ArchiveWearHistory
 *
 * @ORM\Table(name="archive_wear_history")
 * @ORM\Entity
 */
class ArchiveWearHistory
{
    /**
     * @var int
     *
     * @ORM\Column(name="ship_table_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $shipTableId = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="measure_date", type="date", nullable=false, options={"default"="0000-00-00"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $measureDate = '0000-00-00';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="measure_time", type="time", nullable=false, options={"default"="00:00:00"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $measureTime = '00:00:00';

    /**
     * @var bool
     *
     * @ORM\Column(name="cyl_no", type="boolean", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $cylNo = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="running_hours", type="float", precision=10, scale=0, nullable=false)
     */
    private $runningHours = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="wear_reserve", type="float", precision=10, scale=0, nullable=false)
     */
    private $wearReserve = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="prm_temperature", type="float", precision=10, scale=0, nullable=false)
     */
    private $prmTemperature = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="revolution", type="float", precision=10, scale=0, nullable=false)
     */
    private $revolution = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="therm_load", type="float", precision=10, scale=0, nullable=false)
     */
    private $thermLoad = '0';

    public function getShipTableId(): ?int
    {
        return $this->shipTableId;
    }

    public function getMeasureDate(): ?\DateTimeInterface
    {
        return $this->measureDate;
    }

    public function getMeasureTime(): ?\DateTimeInterface
    {
        return $this->measureTime;
    }

    public function getCylNo(): ?bool
    {
        return $this->cylNo;
    }

    public function getRunningHours(): ?float
    {
        return $this->runningHours;
    }

    public function setRunningHours(float $runningHours): self
    {
        $this->runningHours = $runningHours;

        return $this;
    }

    public function getWearReserve(): ?float
    {
        return $this->wearReserve;
    }

    public function setWearReserve(float $wearReserve): self
    {
        $this->wearReserve = $wearReserve;

        return $this;
    }

    public function getPrmTemperature(): ?float
    {
        return $this->prmTemperature;
    }

    public function setPrmTemperature(float $prmTemperature): self
    {
        $this->prmTemperature = $prmTemperature;

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

    public function getThermLoad(): ?float
    {
        return $this->thermLoad;
    }

    public function setThermLoad(float $thermLoad): self
    {
        $this->thermLoad = $thermLoad;

        return $this;
    }


}
