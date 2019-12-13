<?php

namespace App\Entity\UsrWeb71;

use Doctrine\ORM\Mapping as ORM;

/**
 * DlAnalysisTable
 *
 * @ORM\Table(name="dl_analysis_table", indexes={@ORM\Index(name="id", columns={"id"})})
 * @ORM\Entity
 */
class DlAnalysisTable
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
     * @ORM\Column(name="reederei", type="text", length=65535, nullable=false)
     */
    private $reederei;

    /**
     * @var int
     *
     * @ORM\Column(name="ship_table_id", type="integer", nullable=false)
     */
    private $shipTableId = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="ship_no", type="text", length=65535, nullable=false)
     */
    private $shipNo;

    /**
     * @var string|null
     *
     * @ORM\Column(name="year", type="text", length=65535, nullable=true)
     */
    private $year;

    /**
     * @var string|null
     *
     * @ORM\Column(name="type", type="text", length=65535, nullable=true)
     */
    private $type;

    /**
     * @var string|null
     *
     * @ORM\Column(name="dat_name", type="text", length=65535, nullable=true)
     */
    private $datName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="link_name", type="text", length=65535, nullable=true)
     */
    private $linkName;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReederei(): ?string
    {
        return $this->reederei;
    }

    public function setReederei(string $reederei): self
    {
        $this->reederei = $reederei;

        return $this;
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

    public function getShipNo(): ?string
    {
        return $this->shipNo;
    }

    public function setShipNo(string $shipNo): self
    {
        $this->shipNo = $shipNo;

        return $this;
    }

    public function getYear(): ?string
    {
        return $this->year;
    }

    public function setYear(?string $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDatName(): ?string
    {
        return $this->datName;
    }

    public function setDatName(?string $datName): self
    {
        $this->datName = $datName;

        return $this;
    }

    public function getLinkName(): ?string
    {
        return $this->linkName;
    }

    public function setLinkName(?string $linkName): self
    {
        $this->linkName = $linkName;

        return $this;
    }


}
