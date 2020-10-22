<?php

namespace App\Entity\Marprime;

use Doctrine\ORM\Mapping as ORM;

/**
 * ReportGroupboxes
 *
 * @ORM\Table(name="report_groupboxes")
 * @ORM\Entity
 */
class ReportGroupboxes
{
    /**
     * @var int
     *
     * @ORM\Column(name="ReportID", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $reportid;

    /**
     * @var int
     *
     * @ORM\Column(name="Box", type="smallint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $box;

    /**
     * @var int
     *
     * @ORM\Column(name="Tab", type="smallint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $tab;

    /**
     * @var string
     *
     * @ORM\Column(name="Name", type="text", length=65535, nullable=false)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="FieldCount", type="integer", nullable=false)
     */
    private $fieldcount;

    /**
     * @var int
     *
     * @ORM\Column(name="Top", type="integer", nullable=false)
     */
    private $top;

    /**
     * @var int
     *
     * @ORM\Column(name="Height", type="integer", nullable=false)
     */
    private $height;

    public function getReportid(): ?string
    {
        return $this->reportid;
    }

    public function getBox(): ?int
    {
        return $this->box;
    }

    public function getTab(): ?int
    {
        return $this->tab;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getFieldcount(): ?int
    {
        return $this->fieldcount;
    }

    public function setFieldcount(int $fieldcount): self
    {
        $this->fieldcount = $fieldcount;

        return $this;
    }

    public function getTop(): ?int
    {
        return $this->top;
    }

    public function setTop(int $top): self
    {
        $this->top = $top;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;

        return $this;
    }


}
