<?php

namespace App\Entity\Marprime;

use Doctrine\ORM\Mapping as ORM;

/**
 * ReportStringgrids
 *
 * @ORM\Table(name="report_stringgrids")
 * @ORM\Entity
 */
class ReportStringgrids
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
     * @ORM\Column(name="Tab", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $tab;

    /**
     * @var int
     *
     * @ORM\Column(name="Box", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $box;

    /**
     * @var int
     *
     * @ORM\Column(name="Field", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $field;

    /**
     * @var int
     *
     * @ORM\Column(name="ColNum", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $colnum;

    /**
     * @var string
     *
     * @ORM\Column(name="Values", type="text", length=65535, nullable=false)
     */
    private $values;

    public function getReportid(): ?string
    {
        return $this->reportid;
    }

    public function getTab(): ?int
    {
        return $this->tab;
    }

    public function getBox(): ?int
    {
        return $this->box;
    }

    public function getField(): ?int
    {
        return $this->field;
    }

    public function getColnum(): ?int
    {
        return $this->colnum;
    }

    public function getValues(): ?string
    {
        return $this->values;
    }

    public function setValues(string $values): self
    {
        $this->values = $values;

        return $this;
    }


}
