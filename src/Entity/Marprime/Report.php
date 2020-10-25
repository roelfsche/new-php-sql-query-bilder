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


}
