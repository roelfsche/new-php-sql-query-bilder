<?php

namespace App\Entity\UsrWeb71;

use Doctrine\ORM\Mapping as ORM;

/**
 * GeneratedReports
 *
 * @ORM\Table(name="generated_reports", indexes={@ORM\Index(name="fleet_hash", columns={"fleet_hash"}), @ORM\Index(name="ship_id", columns={"ship_id"})})
 * @ORM\Entity
 */
class GeneratedReports
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
     * @var int|null
     *
     * @ORM\Column(name="ship_id", type="integer", nullable=true)
     */
    private $shipId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="fleet_hash", type="string", length=32, nullable=true)
     */
    private $fleetHash;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=64, nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="period", type="string", length=32, nullable=false)
     */
    private $period;

    /**
     * @var int
     *
     * @ORM\Column(name="from_ts", type="integer", nullable=false)
     */
    private $fromTs;

    /**
     * @var int
     *
     * @ORM\Column(name="to_ts", type="integer", nullable=false)
     */
    private $toTs;

    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", length=254, nullable=false)
     */
    private $filename;

    /**
     * @var string|null
     *
     * @ORM\Column(name="data", type="text", length=65535, nullable=true)
     */
    private $data;

    /**
     * @var int
     *
     * @ORM\Column(name="modify_ts", type="integer", nullable=false)
     */
    private $modifyTs;

    /**
     * @var int
     *
     * @ORM\Column(name="create_ts", type="integer", nullable=false)
     */
    private $createTs;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShipId(): ?int
    {
        return $this->shipId;
    }

    public function setShipId(?int $shipId): self
    {
        $this->shipId = $shipId;

        return $this;
    }

    public function getFleetHash(): ?string
    {
        return $this->fleetHash;
    }

    public function setFleetHash(?string $fleetHash): self
    {
        $this->fleetHash = $fleetHash;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPeriod(): ?string
    {
        return $this->period;
    }

    public function setPeriod(string $period): self
    {
        $this->period = $period;

        return $this;
    }

    public function getFromTs(): ?int
    {
        return $this->fromTs;
    }

    public function setFromTs(int $fromTs): self
    {
        $this->fromTs = $fromTs;

        return $this;
    }

    public function getToTs(): ?int
    {
        return $this->toTs;
    }

    public function setToTs(int $toTs): self
    {
        $this->toTs = $toTs;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(?string $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getModifyTs(): ?int
    {
        return $this->modifyTs;
    }

    public function setModifyTs(int $modifyTs): self
    {
        $this->modifyTs = $modifyTs;

        return $this;
    }

    public function getCreateTs(): ?int
    {
        return $this->createTs;
    }

    public function setCreateTs(int $createTs): self
    {
        $this->createTs = $createTs;

        return $this;
    }


}
