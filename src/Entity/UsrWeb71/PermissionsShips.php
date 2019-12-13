<?php

namespace App\Entity\UsrWeb71;

use Doctrine\ORM\Mapping as ORM;

/**
 * PermissionsShips
 *
 * @ORM\Table(name="permissions_ships", indexes={@ORM\Index(name="permission_id", columns={"permission_id"})})
 * @ORM\Entity
 */
class PermissionsShips
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
     * @ORM\Column(name="permission_id", type="integer", nullable=false)
     */
    private $permissionId;

    /**
     * @var string
     *
     * @ORM\Column(name="ship_id", type="string", length=8, nullable=false)
     */
    private $shipId;

    /**
     * @var int
     *
     * @ORM\Column(name="create_user_id", type="integer", nullable=false)
     */
    private $createUserId;

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

    public function getPermissionId(): ?int
    {
        return $this->permissionId;
    }

    public function setPermissionId(int $permissionId): self
    {
        $this->permissionId = $permissionId;

        return $this;
    }

    public function getShipId(): ?string
    {
        return $this->shipId;
    }

    public function setShipId(string $shipId): self
    {
        $this->shipId = $shipId;

        return $this;
    }

    public function getCreateUserId(): ?int
    {
        return $this->createUserId;
    }

    public function setCreateUserId(int $createUserId): self
    {
        $this->createUserId = $createUserId;

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


}
