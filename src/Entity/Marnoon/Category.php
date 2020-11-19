<?php

namespace App\Entity\Marnoon;

use Doctrine\ORM\Mapping as ORM;

/**
 * Category
 *
 * @ORM\Table(name="category")
 * @ORM\Entity
 */
class Category
{
    /**
     * @var int
     *
     * @ORM\Column(name="CategoryId", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $categoryid;

    /**
     * @var string
     *
     * @ORM\Column(name="CategoryName", type="string", length=20, nullable=false)
     */
    private $categoryname;

    /**
     * @var bool
     *
     * @ORM\Column(name="SortOrder", type="boolean", nullable=false)
     */
    private $sortorder;

    public function getCategoryid(): ?int
    {
        return $this->categoryid;
    }

    public function getCategoryname(): ?string
    {
        return $this->categoryname;
    }

    public function setCategoryname(string $categoryname): self
    {
        $this->categoryname = $categoryname;

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
