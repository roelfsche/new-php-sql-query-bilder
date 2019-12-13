<?php

namespace App\Entity\UsrWeb71;

use Doctrine\ORM\Mapping as ORM;

/**
 * Logs
 *
 * @ORM\Table(name="logs", indexes={@ORM\Index(name="object_id", columns={"object_id", "object_object_id"})})
 * @ORM\Entity
 */
class Logs
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
     * @var string
     *
     * @ORM\Column(name="object_type", type="string", length=32, nullable=false)
     */
    private $objectType;

    /**
     * @var int
     *
     * @ORM\Column(name="object_id", type="integer", nullable=false)
     */
    private $objectId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="object_object_id", type="integer", nullable=true)
     */
    private $objectObjectId;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=32, nullable=false)
     */
    private $action;

    /**
     * @var string
     *
     * @ORM\Column(name="data", type="text", length=65535, nullable=false)
     */
    private $data;

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

    public function getObjectType(): ?string
    {
        return $this->objectType;
    }

    public function setObjectType(string $objectType): self
    {
        $this->objectType = $objectType;

        return $this;
    }

    public function getObjectId(): ?int
    {
        return $this->objectId;
    }

    public function setObjectId(int $objectId): self
    {
        $this->objectId = $objectId;

        return $this;
    }

    public function getObjectObjectId(): ?int
    {
        return $this->objectObjectId;
    }

    public function setObjectObjectId(?int $objectObjectId): self
    {
        $this->objectObjectId = $objectObjectId;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(string $data): self
    {
        $this->data = $data;

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
