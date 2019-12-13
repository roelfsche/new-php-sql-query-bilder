<?php

namespace App\Entity\UsrWeb71;

use Doctrine\ORM\Mapping as ORM;

/**
 * SpecFuelPics
 *
 * @ORM\Table(name="spec_fuel_pics")
 * @ORM\Entity
 */
class SpecFuelPics
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="div_x", type="integer", nullable=false)
     */
    private $divX = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="div_y", type="integer", nullable=false)
     */
    private $divY = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="text", length=65535, nullable=false)
     */
    private $path;

    /**
     * @var float
     *
     * @ORM\Column(name="y_min", type="float", precision=10, scale=0, nullable=false)
     */
    private $yMin = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="y_pic_diff", type="float", precision=10, scale=0, nullable=false)
     */
    private $yPicDiff = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="y_delta", type="float", precision=10, scale=0, nullable=false)
     */
    private $yDelta = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="x_min", type="float", precision=10, scale=0, nullable=false)
     */
    private $xMin = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="x_pic_diff", type="float", precision=10, scale=0, nullable=false)
     */
    private $xPicDiff = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="x_delta", type="float", precision=10, scale=0, nullable=false)
     */
    private $xDelta = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text", length=65535, nullable=false)
     */
    private $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDivX(): ?int
    {
        return $this->divX;
    }

    public function setDivX(int $divX): self
    {
        $this->divX = $divX;

        return $this;
    }

    public function getDivY(): ?int
    {
        return $this->divY;
    }

    public function setDivY(int $divY): self
    {
        $this->divY = $divY;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getYMin(): ?float
    {
        return $this->yMin;
    }

    public function setYMin(float $yMin): self
    {
        $this->yMin = $yMin;

        return $this;
    }

    public function getYPicDiff(): ?float
    {
        return $this->yPicDiff;
    }

    public function setYPicDiff(float $yPicDiff): self
    {
        $this->yPicDiff = $yPicDiff;

        return $this;
    }

    public function getYDelta(): ?float
    {
        return $this->yDelta;
    }

    public function setYDelta(float $yDelta): self
    {
        $this->yDelta = $yDelta;

        return $this;
    }

    public function getXMin(): ?float
    {
        return $this->xMin;
    }

    public function setXMin(float $xMin): self
    {
        $this->xMin = $xMin;

        return $this;
    }

    public function getXPicDiff(): ?float
    {
        return $this->xPicDiff;
    }

    public function setXPicDiff(float $xPicDiff): self
    {
        $this->xPicDiff = $xPicDiff;

        return $this;
    }

    public function getXDelta(): ?float
    {
        return $this->xDelta;
    }

    public function setXDelta(float $xDelta): self
    {
        $this->xDelta = $xDelta;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }


}
