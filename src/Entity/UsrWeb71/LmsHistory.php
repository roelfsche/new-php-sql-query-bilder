<?php

namespace App\Entity\UsrWeb71;

use Doctrine\ORM\Mapping as ORM;

/**
 * LmsHistory
 *
 * @ORM\Table(name="lms_history", indexes={@ORM\Index(name="edit_set_no", columns={"edit_set_no"}), @ORM\Index(name="id", columns={"id"})})
 * @ORM\Entity
 */
class LmsHistory
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
     * @ORM\Column(name="running_hours", type="float", precision=20, scale=2, nullable=false, options={"default"="0.00"})
     */
    private $runningHours = '0.00';

    /**
     * @var float
     *
     * @ORM\Column(name="revolution", type="float", precision=10, scale=0, nullable=false)
     */
    private $revolution = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="leakage", type="float", precision=10, scale=0, nullable=false)
     */
    private $leakage = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="cyl_no", type="integer", nullable=false)
     */
    private $cylNo = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="edit_set_no", type="bigint", nullable=false, options={"default"="-1"})
     */
    private $editSetNo = '-1';

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

    public function getLeakage(): ?float
    {
        return $this->leakage;
    }

    public function setLeakage(float $leakage): self
    {
        $this->leakage = $leakage;

        return $this;
    }

    public function getCylNo(): ?int
    {
        return $this->cylNo;
    }

    public function setCylNo(int $cylNo): self
    {
        $this->cylNo = $cylNo;

        return $this;
    }

    public function getEditSetNo(): ?string
    {
        return $this->editSetNo;
    }

    public function setEditSetNo(string $editSetNo): self
    {
        $this->editSetNo = $editSetNo;

        return $this;
    }


}
