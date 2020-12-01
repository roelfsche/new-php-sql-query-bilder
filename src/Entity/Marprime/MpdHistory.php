<?php

namespace Entity\Marprime;

use Doctrine\ORM\Mapping as ORM;

/**
 * MpdHistory
 *
 * @ORM\Table(name="mpd_history")
 * @ORM\Entity
 */
class MpdHistory
{
    /**
     * @var bool
     *
     * @ORM\Column(name="cyl_no", type="boolean", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $cylNo = '0';

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
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    private $date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="MeasurementTime", type="datetime", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $measurementtime;

    /**
     * @var float
     *
     * @ORM\Column(name="revolution", type="float", precision=5, scale=1, nullable=false, options={"default"="0.0"})
     */
    private $revolution = '0.0';

    /**
     * @var float
     *
     * @ORM\Column(name="scav_air", type="float", precision=10, scale=4, nullable=false, options={"default"="0.0000"})
     */
    private $scavAir = '0.0000';

    /**
     * @var float
     *
     * @ORM\Column(name="comp_pressure", type="float", precision=10, scale=2, nullable=false, options={"default"="0.00"})
     */
    private $compPressure = '0.00';

    /**
     * @var float
     *
     * @ORM\Column(name="max_pressure", type="float", precision=10, scale=2, nullable=false, options={"default"="0.00"})
     */
    private $maxPressure = '0.00';

    /**
     * @var float
     *
     * @ORM\Column(name="mean_ind_pressure", type="float", precision=10, scale=4, nullable=false, options={"default"="0.0000"})
     */
    private $meanIndPressure = '0.0000';

    /**
     * @var float
     *
     * @ORM\Column(name="ind_power", type="float", precision=10, scale=2, nullable=false, options={"default"="0.00"})
     */
    private $indPower = '0.00';

    /**
     * @var float
     *
     * @ORM\Column(name="angle_pmax", type="float", precision=10, scale=0, nullable=false)
     */
    private $anglePmax = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="pcomp_rel_pscav", type="float", precision=10, scale=0, nullable=false)
     */
    private $pcompRelPscav = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="leakage", type="float", precision=10, scale=0, nullable=false)
     */
    private $leakage = '0';


}
