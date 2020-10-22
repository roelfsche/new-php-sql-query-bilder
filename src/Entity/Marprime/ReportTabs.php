<?php

namespace App\Entity\Marprime;

use Doctrine\ORM\Mapping as ORM;

/**
 * ReportTabs
 *
 * @ORM\Table(name="report_tabs")
 * @ORM\Entity
 */
class ReportTabs
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
     * @ORM\Column(name="BoxCount", type="smallint", nullable=false)
     */
    private $boxcount;

    public function getReportid(): ?string
    {
        return $this->reportid;
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

    public function getBoxcount(): ?int
    {
        return $this->boxcount;
    }

    public function setBoxcount(int $boxcount): self
    {
        $this->boxcount = $boxcount;

        return $this;
    }


}
