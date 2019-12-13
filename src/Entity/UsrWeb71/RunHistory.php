<?php

namespace App\Entity\UsrWeb71;

use Doctrine\ORM\Mapping as ORM;

/**
 * RunHistory
 *
 * @ORM\Table(name="run_history", indexes={@ORM\Index(name="id", columns={"id"})})
 * @ORM\Entity
 */
class RunHistory
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
     * @var int
     *
     * @ORM\Column(name="ship_table_id", type="integer", nullable=false)
     */
    private $shipTableId = '0';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="measure_date", type="date", nullable=true)
     */
    private $measureDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="measure_time", type="time", nullable=false, options={"default"="00:00:00"})
     */
    private $measureTime = '00:00:00';

    /**
     * @var float
     *
     * @ORM\Column(name="running_hours", type="float", precision=10, scale=0, nullable=false)
     */
    private $runningHours = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="revolution", type="float", precision=10, scale=0, nullable=false)
     */
    private $revolution = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="load_balance", type="float", precision=10, scale=0, nullable=false)
     */
    private $loadBalance = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="uniformity", type="float", precision=10, scale=0, nullable=false)
     */
    private $uniformity = '0';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShipTableId(): ?int
    {
        return $this->shipTableId;
    }

    public function setShipTableId(int $shipTableId): self
    {
        $this->shipTableId = $shipTableId;

        return $this;
    }

    public function getMeasureDate(): ?\DateTimeInterface
    {
        return $this->measureDate;
    }

    public function setMeasureDate(?\DateTimeInterface $measureDate): self
    {
        $this->measureDate = $measureDate;

        return $this;
    }

    public function getMeasureTime(): ?\DateTimeInterface
    {
        return $this->measureTime;
    }

    public function setMeasureTime(\DateTimeInterface $measureTime): self
    {
        $this->measureTime = $measureTime;

        return $this;
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

    public function getRevolution(): ?float
    {
        return $this->revolution;
    }

    public function setRevolution(float $revolution): self
    {
        $this->revolution = $revolution;

        return $this;
    }

    public function getLoadBalance(): ?float
    {
        return $this->loadBalance;
    }

    public function setLoadBalance(float $loadBalance): self
    {
        $this->loadBalance = $loadBalance;

        return $this;
    }

    public function getUniformity(): ?float
    {
        return $this->uniformity;
    }

    public function setUniformity(float $uniformity): self
    {
        $this->uniformity = $uniformity;

        return $this;
    }


}
