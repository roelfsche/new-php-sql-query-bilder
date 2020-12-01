<?php

namespace App\Entity\Marprime;

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

    public function getReportid(): ?string
    {
        return $this->reportid;
    }

    public function getFieldid(): ?string
    {
        return $this->fieldid;
    }

    public function getBox(): ?int
    {
        return $this->box;
    }

    public function getTab(): ?int
    {
        return $this->tab;
    }

    public function getTop(): ?int
    {
        return $this->top;
    }

    public function setTop(int $top): self
    {
        $this->top = $top;

        return $this;
    }

    public function getLeftpoint(): ?int
    {
        return $this->leftpoint;
    }

    public function setLeftpoint(int $leftpoint): self
    {
        $this->leftpoint = $leftpoint;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getName(): ?int
    {
        return $this->name;
    }

    public function setName(int $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getBoldfont(): ?int
    {
        return $this->boldfont;
    }

    public function setBoldfont(int $boldfont): self
    {
        $this->boldfont = $boldfont;

        return $this;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getHint(): ?string
    {
        return $this->hint;
    }

    public function setHint(string $hint): self
    {
        $this->hint = $hint;

        return $this;
    }

    public function getTaborder(): ?int
    {
        return $this->taborder;
    }

    public function setTaborder(int $taborder): self
    {
        $this->taborder = $taborder;

        return $this;
    }

    public function getMinval(): ?float
    {
        return $this->minval;
    }

    public function setMinval(float $minval): self
    {
        $this->minval = $minval;

        return $this;
    }

    public function getMaxval(): ?float
    {
        return $this->maxval;
    }

    public function setMaxval(float $maxval): self
    {
        $this->maxval = $maxval;

        return $this;
    }

    public function getMaxlength(): ?float
    {
        return $this->maxlength;
    }

    public function setMaxlength(float $maxlength): self
    {
        $this->maxlength = $maxlength;

        return $this;
    }

    public function getValueprecision(): ?float
    {
        return $this->valueprecision;
    }

    public function setValueprecision(float $valueprecision): self
    {
        $this->valueprecision = $valueprecision;

        return $this;
    }

    public function getItemindex(): ?int
    {
        return $this->itemindex;
    }

    public function setItemindex(int $itemindex): self
    {
        $this->itemindex = $itemindex;

        return $this;
    }


}
