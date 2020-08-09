<?php

namespace Entity\Marprime;

use Doctrine\ORM\Mapping as ORM;

/**
 * MpdAeCurveData
 *
 * @ORM\Table(name="mpd_ae_curve_data")
 * @ORM\Entity
 */
class MpdAeCurveData
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
     * @var bool
     *
     * @ORM\Column(name="cyl_no", type="boolean", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $cylNo;

    /**
     * @var int
     *
     * @ORM\Column(name="x_val", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $xVal;

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
     * @var float
     *
     * @ORM\Column(name="y_val", type="float", precision=10, scale=0, nullable=false)
     */
    private $yVal;

    /**
     * @var float
     *
     * @ORM\Column(name="revolution", type="float", precision=10, scale=3, nullable=false)
     */
    private $revolution;


}
