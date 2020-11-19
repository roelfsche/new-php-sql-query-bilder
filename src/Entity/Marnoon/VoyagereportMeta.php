<?php

namespace App\Entity\Marnoon;

use Doctrine\ORM\Mapping as ORM;

/**
 * VoyagereportMeta
 *
 * @ORM\Table(name="voyagereport_meta")
 * @ORM\Entity
 */
class VoyagereportMeta
{
    /**
     * @var string
     *
     * @ORM\Column(name="ColumnName", type="string", length=20, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $columnname;

    /**
     * @var int|null
     *
     * @ORM\Column(name="CategoryId", type="integer", nullable=true)
     */
    private $categoryid;

    /**
     * @var int
     *
     * @ORM\Column(name="Importance", type="integer", nullable=false)
     */
    private $importance;

    /**
     * @var string
     *
     * @ORM\Column(name="DisplayName", type="string", length=40, nullable=false)
     */
    private $displayname;

    /**
     * @var bool
     *
     * @ORM\Column(name="SortOrder", type="boolean", nullable=false)
     */
    private $sortorder;

    public function getColumnname(): ?string
    {
        return $this->columnname;
    }

    public function getCategoryid(): ?int
    {
        return $this->categoryid;
    }

    public function setCategoryid(?int $categoryid): self
    {
        $this->categoryid = $categoryid;

        return $this;
    }

    public function getImportance(): ?int
    {
        return $this->importance;
    }

    public function setImportance(int $importance): self
    {
        $this->importance = $importance;

        return $this;
    }

    public function getDisplayname(): ?string
    {
        return $this->displayname;
    }

    public function setDisplayname(string $displayname): self
    {
        $this->displayname = $displayname;

        return $this;
    }

    public function getSortorder(): ?bool
    {
        return $this->sortorder;
    }

    public function setSortorder(bool $sortorder): self
    {
        $this->sortorder = $sortorder;

        return $this;
    }


}
