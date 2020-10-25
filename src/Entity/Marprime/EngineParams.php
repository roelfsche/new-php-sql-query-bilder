<?php

namespace App\Entity\Marprime;

use Doctrine\ORM\Mapping as ORM;

/**
 * EngineParams
 *
 * @ORM\Table(name="engine_params")
 * @ORM\Entity
 */
class EngineParams
{

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="MarPrime_SerialNo", type="string", length=20, nullable=false, options={"default"="-1"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $marprimeSerialno = '-1';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="MeasurementTime", type="datetime", nullable=false)
     */
    private $measurementtime;

    /**
     * @var string
     *
     * @ORM\Column(name="engine_name", type="string", length=200, nullable=false)
     */
    private $engineName;

    /**
     * @var string
     *
     * @ORM\Column(name="engine_type", type="string", length=200, nullable=false)
     */
    private $engineType;

    /**
     * @var bool
     *
     * @ORM\Column(name="cyl_count", type="boolean", nullable=false)
     */
    private $cylCount = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="fire_angle", type="string", length=200, nullable=false)
     */
    private $fireAngle;

    /**
     * @var bool
     *
     * @ORM\Column(name="strokes", type="boolean", nullable=false)
     */
    private $strokes = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="speed", type="float", precision=10, scale=0, nullable=false)
     */
    private $speed = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="power", type="float", precision=10, scale=0, nullable=false)
     */
    private $power = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="stroke", type="float", precision=10, scale=0, nullable=false)
     */
    private $stroke = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="bore", type="float", precision=10, scale=0, nullable=false)
     */
    private $bore = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="connection_ratio", type="float", precision=10, scale=0, nullable=false)
     */
    private $connectionRatio = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="compression_ratio", type="float", precision=10, scale=0, nullable=false)
     */
    private $compressionRatio = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="inlet_open", type="integer", nullable=false)
     */
    private $inletOpen = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="inlet_close", type="integer", nullable=false)
     */
    private $inletClose = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="outlet_open", type="integer", nullable=false)
     */
    private $outletOpen = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="outlet_close", type="integer", nullable=false)
     */
    private $outletClose = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="cappa_correction", type="float", precision=10, scale=0, nullable=false)
     */
    private $cappaCorrection = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_change", type="datetime", nullable=false)
     */
    private $lastChange;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_ts", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $createTs = 'CURRENT_TIMESTAMP';


}
