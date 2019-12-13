<?php

namespace App\Entity\UsrWeb71;

use Doctrine\ORM\Mapping as ORM;

/**
 * AisData
 *
 * @ORM\Table(name="ais_data", indexes={@ORM\Index(name="imo", columns={"imo"})})
 * @ORM\Entity
 */
class AisData
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
     * @ORM\Column(name="imo", type="string", length=10, nullable=false)
     */
    private $imo;

    /**
     * @var string
     *
     * @ORM\Column(name="destination", type="string", length=254, nullable=false)
     */
    private $destination;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="string", length=254, nullable=false)
     */
    private $location;

    /**
     * @var int
     *
     * @ORM\Column(name="eta_ts", type="integer", nullable=false)
     */
    private $etaTs;

    /**
     * @var float
     *
     * @ORM\Column(name="heading", type="float", precision=10, scale=0, nullable=false)
     */
    private $heading;

    /**
     * @var float
     *
     * @ORM\Column(name="lat", type="float", precision=10, scale=6, nullable=false)
     */
    private $lat;

    /**
     * @var float
     *
     * @ORM\Column(name="long", type="float", precision=10, scale=6, nullable=false)
     */
    private $long;

    /**
     * @var string
     *
     * @ORM\Column(name="navigation_status", type="string", length=254, nullable=false)
     */
    private $navigationStatus;

    /**
     * @var int
     *
     * @ORM\Column(name="position_received_ts", type="integer", nullable=false)
     */
    private $positionReceivedTs;

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

    public function getImo(): ?string
    {
        return $this->imo;
    }

    public function setImo(string $imo): self
    {
        $this->imo = $imo;

        return $this;
    }

    public function getDestination(): ?string
    {
        return $this->destination;
    }

    public function setDestination(string $destination): self
    {
        $this->destination = $destination;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getEtaTs(): ?int
    {
        return $this->etaTs;
    }

    public function setEtaTs(int $etaTs): self
    {
        $this->etaTs = $etaTs;

        return $this;
    }

    public function getHeading(): ?float
    {
        return $this->heading;
    }

    public function setHeading(float $heading): self
    {
        $this->heading = $heading;

        return $this;
    }

    public function getLat(): ?float
    {
        return $this->lat;
    }

    public function setLat(float $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLong(): ?float
    {
        return $this->long;
    }

    public function setLong(float $long): self
    {
        $this->long = $long;

        return $this;
    }

    public function getNavigationStatus(): ?string
    {
        return $this->navigationStatus;
    }

    public function setNavigationStatus(string $navigationStatus): self
    {
        $this->navigationStatus = $navigationStatus;

        return $this;
    }

    public function getPositionReceivedTs(): ?int
    {
        return $this->positionReceivedTs;
    }

    public function setPositionReceivedTs(int $positionReceivedTs): self
    {
        $this->positionReceivedTs = $positionReceivedTs;

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
