<?php

namespace App\Entity\Marprime;

use Doctrine\ORM\Mapping as ORM;

/**
 * Report
 *
 * @ORM\Table(name="report", indexes={@ORM\Index(name="ReportID", columns={"ReportID"})})
 * @ORM\Entity
 */
class Report
{
    /**
     * @var string
     *
     * @ORM\Column(name="MarPrimeSerial", type="string", length=20, nullable=false, options={"default"="-1"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $marprimeserial = '-1';

    /**
     * @var int
     *
     * @ORM\Column(name="ReportID", type="bigint", nullable=false)
     */
    private $reportid;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="Date", type="datetime", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="MPDFileName", type="text", length=65535, nullable=false)
     */
    private $mpdfilename;

    /**
     * @var string
     *
     * @ORM\Column(name="EngineName", type="text", length=65535, nullable=false)
     */
    private $enginename;

    /**
     * @var string
     *
     * @ORM\Column(name="EngineType", type="text", length=65535, nullable=false)
     */
    private $enginetype;

    /**
     * @var string
     *
     * @ORM\Column(name="ReportName", type="text", length=65535, nullable=false)
     */
    private $reportname;

    /**
     * @var string
     *
     * @ORM\Column(name="ReportVersion", type="string", length=10, nullable=false)
     */
    private $reportversion;

    /**
     * @var string
     *
     * @ORM\Column(name="XMLVersion", type="string", length=10, nullable=false)
     */
    private $xmlversion;

    /**
     * @var int
     *
     * @ORM\Column(name="Strokes", type="smallint", nullable=false)
     */
    private $strokes;

    /**
     * @var int
     *
     * @ORM\Column(name="TabCount", type="smallint", nullable=false)
     */
    private $tabcount;

    public function getMarprimeserial(): ?string
    {
        return $this->marprimeserial;
    }

    public function getReportid(): ?string
    {
        return $this->reportid;
    }

    public function setReportid(string $reportid): self
    {
        $this->reportid = $reportid;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function getMpdfilename(): ?string
    {
        return $this->mpdfilename;
    }

    public function setMpdfilename(string $mpdfilename): self
    {
        $this->mpdfilename = $mpdfilename;

        return $this;
    }

    public function getEnginename(): ?string
    {
        return $this->enginename;
    }

    public function setEnginename(string $enginename): self
    {
        $this->enginename = $enginename;

        return $this;
    }

    public function getEnginetype(): ?string
    {
        return $this->enginetype;
    }

    public function setEnginetype(string $enginetype): self
    {
        $this->enginetype = $enginetype;

        return $this;
    }

    public function getReportname(): ?string
    {
        return $this->reportname;
    }

    public function setReportname(string $reportname): self
    {
        $this->reportname = $reportname;

        return $this;
    }

    public function getReportversion(): ?string
    {
        return $this->reportversion;
    }

    public function setReportversion(string $reportversion): self
    {
        $this->reportversion = $reportversion;

        return $this;
    }

    public function getXmlversion(): ?string
    {
        return $this->xmlversion;
    }

    public function setXmlversion(string $xmlversion): self
    {
        $this->xmlversion = $xmlversion;

        return $this;
    }

    public function getStrokes(): ?int
    {
        return $this->strokes;
    }

    public function setStrokes(int $strokes): self
    {
        $this->strokes = $strokes;

        return $this;
    }

    public function getTabcount(): ?int
    {
        return $this->tabcount;
    }

    public function setTabcount(int $tabcount): self
    {
        $this->tabcount = $tabcount;

        return $this;
    }


}
