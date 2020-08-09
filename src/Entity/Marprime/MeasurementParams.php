<?php

namespace Entity\Marprime;

use Doctrine\ORM\Mapping as ORM;

/**
 * MeasurementParams
 *
 * @ORM\Table(name="measurement_params")
 * @ORM\Entity
 */
class MeasurementParams
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
     * @var float
     *
     * @ORM\Column(name="convergence", type="float", precision=10, scale=0, nullable=false)
     */
    private $convergence;

    /**
     * @var float
     *
     * @ORM\Column(name="alpha1", type="float", precision=10, scale=0, nullable=false)
     */
    private $alpha1;

    /**
     * @var float
     *
     * @ORM\Column(name="alpha2", type="float", precision=10, scale=0, nullable=false)
     */
    private $alpha2;

    /**
     * @var int
     *
     * @ORM\Column(name="monitor", type="integer", nullable=false)
     */
    private $monitor;

    /**
     * @var int
     *
     * @ORM\Column(name="leakage_left_start", type="integer", nullable=false)
     */
    private $leakageLeftStart;

    /**
     * @var int
     *
     * @ORM\Column(name="leakage_left_end", type="integer", nullable=false)
     */
    private $leakageLeftEnd;

    /**
     * @var int
     *
     * @ORM\Column(name="leakage_right_start", type="integer", nullable=false)
     */
    private $leakageRightStart;

    /**
     * @var int
     *
     * @ORM\Column(name="leakage_right_end", type="integer", nullable=false)
     */
    private $leakageRightEnd;

    /**
     * @var float
     *
     * @ORM\Column(name="normalisation_limit_start", type="float", precision=10, scale=0, nullable=false)
     */
    private $normalisationLimitStart;

    /**
     * @var float
     *
     * @ORM\Column(name="normalisation_limit_end", type="float", precision=10, scale=0, nullable=false)
     */
    private $normalisationLimitEnd;

    /**
     * @var float
     *
     * @ORM\Column(name="diagnostic_range_start", type="float", precision=10, scale=0, nullable=false)
     */
    private $diagnosticRangeStart;

    /**
     * @var float
     *
     * @ORM\Column(name="diagnostic_range_end", type="float", precision=10, scale=0, nullable=false)
     */
    private $diagnosticRangeEnd;

    /**
     * @var float
     *
     * @ORM\Column(name="diagnostic_weight_start", type="float", precision=10, scale=0, nullable=false)
     */
    private $diagnosticWeightStart;

    /**
     * @var float
     *
     * @ORM\Column(name="diagnostic_weight_end", type="float", precision=10, scale=0, nullable=false)
     */
    private $diagnosticWeightEnd;

    /**
     * @var float
     *
     * @ORM\Column(name="ae_amplification", type="float", precision=10, scale=0, nullable=false)
     */
    private $aeAmplification;

    /**
     * @var float
     *
     * @ORM\Column(name="limit_pmax", type="float", precision=10, scale=0, nullable=false)
     */
    private $limitPmax;

    /**
     * @var float
     *
     * @ORM\Column(name="limit_pcomp", type="float", precision=10, scale=0, nullable=false)
     */
    private $limitPcomp;

    /**
     * @var float
     *
     * @ORM\Column(name="limit_power", type="float", precision=10, scale=0, nullable=false)
     */
    private $limitPower;


}
