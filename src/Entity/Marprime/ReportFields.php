<?php

namespace Entity\Marprime;

use Doctrine\ORM\Mapping as ORM;

/**
 * ReportFields
 *
 * @ORM\Table(name="report_fields")
 * @ORM\Entity
 */
class ReportFields
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
     * @ORM\Column(name="FieldID", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $fieldid;

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
     * @ORM\Column(name="Tab", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $tab;

    /**
     * @var int
     *
     * @ORM\Column(name="Top", type="integer", nullable=false)
     */
    private $top;

    /**
     * @var int
     *
     * @ORM\Column(name="LeftPoint", type="integer", nullable=false)
     */
    private $leftpoint;

    /**
     * @var int
     *
     * @ORM\Column(name="Height", type="integer", nullable=false)
     */
    private $height;

    /**
     * @var int
     *
     * @ORM\Column(name="Width", type="integer", nullable=false)
     */
    private $width;

    /**
     * @var string
     *
     * @ORM\Column(name="Type", type="string", length=25, nullable=false)
     */
    private $type;

    /**
     * @var int
     *
     * @ORM\Column(name="Name", type="integer", nullable=false)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="BoldFont", type="integer", nullable=false)
     */
    private $boldfont;

    /**
     * @var int
     *
     * @ORM\Column(name="Value", type="integer", nullable=false)
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(name="Hint", type="text", length=65535, nullable=false)
     */
    private $hint;

    /**
     * @var int
     *
     * @ORM\Column(name="TabOrder", type="integer", nullable=false)
     */
    private $taborder;

    /**
     * @var float
     *
     * @ORM\Column(name="MinVal", type="float", precision=10, scale=0, nullable=false)
     */
    private $minval;

    /**
     * @var float
     *
     * @ORM\Column(name="MaxVal", type="float", precision=10, scale=0, nullable=false)
     */
    private $maxval;

    /**
     * @var float
     *
     * @ORM\Column(name="Maxlength", type="float", precision=10, scale=0, nullable=false)
     */
    private $maxlength;

    /**
     * @var float
     *
     * @ORM\Column(name="ValuePrecision", type="float", precision=10, scale=0, nullable=false)
     */
    private $valueprecision;

    /**
     * @var int
     *
     * @ORM\Column(name="ItemIndex", type="integer", nullable=false)
     */
    private $itemindex;


}
