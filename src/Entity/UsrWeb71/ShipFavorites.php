<?php

namespace App\Entity\UsrWeb71;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;

/**
 * ShipFavorites
 *
 * @ORM\Table(name="ship_favorites", indexes={@ORM\Index(name="user_id", columns={"user_id"})})
 * @ORM\Entity
 */
class ShipFavorites
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
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    private $userId;
    /**
     * Many favoriten have one user. This is the owning side.
     * @ManyToOne(targetEntity="Users", inversedBy="ship_favorites")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $users;

    /**
     * @var int
     *
     * @ORM\Column(name="ship_id", type="integer", nullable=false)
     */
    private $shipId;
    /**
     * Viele favoriten have one Schiff This is the owning side.
     * @ManyToOne(targetEntity="ShipTable", inversedBy="ship_favorites")
     * @JoinColumn(name="ship_id", referencedColumnName="id")
     */
    private $ships;

    /**
     * @var int
     *
     * @ORM\Column(name="create_ts", type="integer", nullable=false)
     */
    private $createTs;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getShipId(): ?int
    {
        return $this->shipId;
    }

    public function setShipId(int $shipId): self
    {
        $this->shipId = $shipId;

        return $this;
    }

    public function getCreateTs(): ?int
    {
        return $this->createTs;
    }

    public function setCreateTs(int $createTs): self
    {
        $this->createTs = $createTs;

        return $this;
    }

    public function getUsers(): ?Users
    {
        return $this->users;
    }

    public function setUsers(?Users $users): self
    {
        $this->users = $users;

        return $this;
    }

    public function getShips(): ?ShipTable
    {
        return $this->ships;
    }

    public function setShips(?ShipTable $ships): self
    {
        $this->ships = $ships;

        return $this;
    }


}
