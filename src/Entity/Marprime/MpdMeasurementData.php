<?php

namespace Entity\Marprime;

use Doctrine\ORM\Mapping as ORM;

/**
 * MpdMeasurementData
 *
 * @ORM\Table(name="mpd_measurement_data")
 * @ORM\Entity
 */
class MpdMeasurementData
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
     * @ORM\Column(name="measurement_num", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $measurementNum;

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
     * @ORM\Column(name="calc_fail", type="float", precision=10, scale=0, nullable=false)
     */
    private $calcFail;

    /**
     * @var int
     *
     * @ORM\Column(name="count_cycles", type="integer", nullable=false)
     */
    private $countCycles;

    /**
     * @var float
     *
     * @ORM\Column(name="cycle_length_cpa", type="float", precision=10, scale=0, nullable=false)
     */
    private $cycleLengthCpa;

    /**
     * @var float
     *
     * @ORM\Column(name="cycle_length_ipa", type="float", precision=10, scale=0, nullable=false)
     */
    private $cycleLengthIpa;

    /**
     * @var int
     *
     * @ORM\Column(name="data_status", type="integer", nullable=false)
     */
    private $dataStatus;

    /**
     * @var int
     *
     * @ORM\Column(name="ipa_start", type="integer", nullable=false)
     */
    private $ipaStart;

    /**
     * @var float
     *
     * @ORM\Column(name="ipa_schritt", type="float", precision=10, scale=0, nullable=false)
     */
    private $ipaSchritt;

    /**
     * @var int
     *
     * @ORM\Column(name="ot_korrektur", type="integer", nullable=false)
     */
    private $otKorrektur;

    /**
     * @var int
     *
     * @ORM\Column(name="spuelluft", type="integer", nullable=false)
     */
    private $spuelluft;

    /**
     * @var int
     *
     * @ORM\Column(name="pfeiffenschwingungskorrektur", type="integer", nullable=false)
     */
    private $pfeiffenschwingungskorrektur;

    /**
     * @var float
     *
     * @ORM\Column(name="revolution", type="float", precision=10, scale=3, nullable=false)
     */
    private $revolution;

    /**
     * @var float
     *
     * @ORM\Column(name="ind_power", type="float", precision=10, scale=3, nullable=false)
     */
    private $indPower;

    /**
     * @var float
     *
     * @ORM\Column(name="v_dp", type="float", precision=10, scale=3, nullable=false)
     */
    private $vDp;

    /**
     * @var float
     *
     * @ORM\Column(name="pil", type="float", precision=10, scale=3, nullable=false)
     */
    private $pil;

    /**
     * @var float
     *
     * @ORM\Column(name="pmax", type="float", precision=10, scale=3, nullable=false)
     */
    private $pmax;

    /**
     * @var float
     *
     * @ORM\Column(name="apmax", type="float", precision=10, scale=3, nullable=false)
     */
    private $apmax;

    /**
     * @var float
     *
     * @ORM\Column(name="pim", type="float", precision=10, scale=3, nullable=false)
     */
    private $pim;

    /**
     * @var float
     *
     * @ORM\Column(name="pilw", type="float", precision=10, scale=3, nullable=false)
     */
    private $pilw;

    /**
     * @var float
     *
     * @ORM\Column(name="p0", type="float", precision=10, scale=3, nullable=false)
     */
    private $p0;

    /**
     * @var float
     *
     * @ORM\Column(name="p36", type="float", precision=10, scale=3, nullable=false)
     */
    private $p36;

    /**
     * @var float
     *
     * @ORM\Column(name="pemax", type="float", precision=10, scale=3, nullable=false)
     */
    private $pemax;

    /**
     * @var float
     *
     * @ORM\Column(name="apemax", type="float", precision=10, scale=3, nullable=false)
     */
    private $apemax;

    /**
     * @var float
     *
     * @ORM\Column(name="peb", type="float", precision=10, scale=3, nullable=false)
     */
    private $peb;

    /**
     * @var float
     *
     * @ORM\Column(name="apeb", type="float", precision=10, scale=3, nullable=false)
     */
    private $apeb;

    /**
     * @var float
     *
     * @ORM\Column(name="apee", type="float", precision=10, scale=3, nullable=false)
     */
    private $apee;

    /**
     * @var float
     *
     * @ORM\Column(name="apfb", type="float", precision=10, scale=3, nullable=false)
     */
    private $apfb;

    /**
     * @var float
     *
     * @ORM\Column(name="apfe", type="float", precision=10, scale=3, nullable=false)
     */
    private $apfe;

    /**
     * @var float
     *
     * @ORM\Column(name="afd", type="float", precision=10, scale=3, nullable=false)
     */
    private $afd;

    /**
     * @var float
     *
     * @ORM\Column(name="azv", type="float", precision=10, scale=3, nullable=false)
     */
    private $azv;

    /**
     * @var float
     *
     * @ORM\Column(name="aev", type="float", precision=10, scale=3, nullable=false)
     */
    private $aev;

    /**
     * @var float
     *
     * @ORM\Column(name="scav_air", type="float", precision=10, scale=3, nullable=false)
     */
    private $scavAir;

    /**
     * @var float
     *
     * @ORM\Column(name="aed", type="float", precision=10, scale=3, nullable=false)
     */
    private $aed;

    /**
     * @var float
     *
     * @ORM\Column(name="pfb", type="float", precision=10, scale=3, nullable=false)
     */
    private $pfb;

    /**
     * @var float
     *
     * @ORM\Column(name="avb", type="float", precision=10, scale=3, nullable=false)
     */
    private $avb;

    /**
     * @var float
     *
     * @ORM\Column(name="cappa", type="float", precision=10, scale=3, nullable=false)
     */
    private $cappa;


}
