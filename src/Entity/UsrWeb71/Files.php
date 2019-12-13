<?php

namespace App\Entity\UsrWeb71;

use Doctrine\ORM\Mapping as ORM;

/**
 * Files
 *
 * @ORM\Table(name="files", uniqueConstraints={@ORM\UniqueConstraint(name="path", columns={"path"})})
 * @ORM\Entity
 */
class Files
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
     * @ORM\Column(name="ship_id", type="integer", nullable=false)
     */
    private $shipId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=254, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="ext", type="string", length=8, nullable=false)
     */
    private $ext;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=254, nullable=false)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="mime_type", type="string", length=64, nullable=false)
     */
    private $mimeType;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=254, nullable=false)
     */
    private $path;

    /**
     * @var int
     *
     * @ORM\Column(name="size", type="integer", nullable=false)
     */
    private $size;

    /**
     * @var int
     *
     * @ORM\Column(name="sort", type="integer", nullable=false)
     */
    private $sort;

    /**
     * @var int|null
     *
     * @ORM\Column(name="delete_user_id", type="integer", nullable=true)
     */
    private $deleteUserId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="delete_ts", type="integer", nullable=true)
     */
    private $deleteTs;

    /**
     * @var int|null
     *
     * @ORM\Column(name="create_user_id", type="integer", nullable=true)
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

    public function getShipId(): ?int
    {
        return $this->shipId;
    }

    public function setShipId(int $shipId): self
    {
        $this->shipId = $shipId;

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

    public function getExt(): ?string
    {
        return $this->ext;
    }

    public function setExt(string $ext): self
    {
        $this->ext = $ext;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;

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

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getSort(): ?int
    {
        return $this->sort;
    }

    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    public function getDeleteUserId(): ?int
    {
        return $this->deleteUserId;
    }

    public function setDeleteUserId(?int $deleteUserId): self
    {
        $this->deleteUserId = $deleteUserId;

        return $this;
    }

    public function getDeleteTs(): ?int
    {
        return $this->deleteTs;
    }

    public function setDeleteTs(?int $deleteTs): self
    {
        $this->deleteTs = $deleteTs;

        return $this;
    }

    public function getCreateUserId(): ?int
    {
        return $this->createUserId;
    }

    public function setCreateUserId(?int $createUserId): self
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
