<?php

namespace Entity\Marprime;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProgramParams
 *
 * @ORM\Table(name="program_params")
 * @ORM\Entity
 */
class ProgramParams
{
    /**
     * @var string
     *
     * @ORM\Column(name="MarPrime_SerialNo", type="string", length=20, nullable=false, options={"default"="-1"})
     */
    private $marprimeSerialno = '-1';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="installpath", type="text", length=65535, nullable=false)
     */
    private $installpath;

    /**
     * @var string
     *
     * @ORM\Column(name="company", type="string", length=25, nullable=false)
     */
    private $company;

    /**
     * @var string
     *
     * @ORM\Column(name="product", type="string", length=25, nullable=false)
     */
    private $product;

    /**
     * @var string
     *
     * @ORM\Column(name="sw_version", type="string", length=25, nullable=false)
     */
    private $swVersion;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", length=65535, nullable=false)
     */
    private $comment;

    /**
     * @var string
     *
     * @ORM\Column(name="reg_root_key", type="text", length=65535, nullable=false)
     */
    private $regRootKey;

    /**
     * @var string
     *
     * @ORM\Column(name="datadir", type="text", length=65535, nullable=false)
     */
    private $datadir;

    /**
     * @var string
     *
     * @ORM\Column(name="rawdatadir", type="text", length=65535, nullable=false)
     */
    private $rawdatadir;

    /**
     * @var string
     *
     * @ORM\Column(name="configdir", type="text", length=65535, nullable=false)
     */
    private $configdir;

    /**
     * @var string
     *
     * @ORM\Column(name="reportdir", type="text", length=65535, nullable=false)
     */
    private $reportdir;

    /**
     * @var string
     *
     * @ORM\Column(name="statisticsdir", type="text", length=65535, nullable=false)
     */
    private $statisticsdir;

    /**
     * @var string
     *
     * @ORM\Column(name="serial", type="string", length=25, nullable=false)
     */
    private $serial;

    /**
     * @var string
     *
     * @ORM\Column(name="key", type="string", length=35, nullable=false)
     */
    private $key;

    /**
     * @var int
     *
     * @ORM\Column(name="reg_mod", type="smallint", nullable=false)
     */
    private $regMod;

    /**
     * @var int
     *
     * @ORM\Column(name="auto_modules", type="smallint", nullable=false)
     */
    private $autoModules;

    /**
     * @var int
     *
     * @ORM\Column(name="inst_modules", type="smallint", nullable=false)
     */
    private $instModules;

    /**
     * @var int
     *
     * @ORM\Column(name="disp_modules", type="smallint", nullable=false)
     */
    private $dispModules;

    /**
     * @var int
     *
     * @ORM\Column(name="active_modules", type="smallint", nullable=false)
     */
    private $activeModules;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="elapse_time", type="datetime", nullable=false)
     */
    private $elapseTime;

    /**
     * @var int
     *
     * @ORM\Column(name="current_eng", type="smallint", nullable=false)
     */
    private $currentEng;

    /**
     * @var int
     *
     * @ORM\Column(name="licence_type", type="smallint", nullable=false)
     */
    private $licenceType;

    /**
     * @var int
     *
     * @ORM\Column(name="debug_mode", type="smallint", nullable=false)
     */
    private $debugMode;

    /**
     * @var int
     *
     * @ORM\Column(name="press_unit", type="smallint", nullable=false)
     */
    private $pressUnit;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    private $status;


}
