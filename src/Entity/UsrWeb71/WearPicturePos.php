<?php

namespace App\Entity\UsrWeb71;

use Doctrine\ORM\Mapping as ORM;

/**
 * WearPicturePos
 *
 * @ORM\Table(name="wear_picture_pos", indexes={@ORM\Index(name="id", columns={"id"})})
 * @ORM\Entity
 */
class WearPicturePos
{
    /**
     * @var bool
     *
     * @ORM\Column(name="cyl_count", type="boolean", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $cylCount = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="dataset", type="text", length=65535, nullable=false)
     */
    private $dataset;

    public function getCylCount(): ?bool
    {
        return $this->cylCount;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getDataset(): ?string
    {
        return $this->dataset;
    }

    public function setDataset(string $dataset): self
    {
        $this->dataset = $dataset;

        return $this;
    }


}
