<?php

namespace App\Entity\UsrWeb71;

use Doctrine\ORM\Mapping as ORM;

/**
 * WearStart
 *
 * @ORM\Table(name="wear_start")
 * @ORM\Entity
 */
class WearStart
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="ship_table_id", type="bigint", nullable=false, options={"default"="-1"})
     */
    private $shipTableId = '-1';

    /**
     * @var int
     *
     * @ORM\Column(name="cyl_no", type="integer", nullable=false, options={"default"="-1"})
     */
    private $cylNo = '-1';

    /**
     * @var float
     *
     * @ORM\Column(name="value", type="float", precision=10, scale=0, nullable=false, options={"default"="-1"})
     */
    private $value = '-1';

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getShipTableId(): ?string
    {
        return $this->shipTableId;
    }

    public function setShipTableId(string $shipTableId): self
    {
        $this->shipTableId = $shipTableId;

        return $this;
    }

    public function getCylNo(): ?int
    {
        return $this->cylNo;
    }

    public function setCylNo(int $cylNo): self
    {
        $this->cylNo = $cylNo;

        return $this;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setValue(float $value): self
    {
        $this->value = $value;

        return $this;
    }


}
