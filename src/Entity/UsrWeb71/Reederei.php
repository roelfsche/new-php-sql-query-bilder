<?php

namespace App\Entity\UsrWeb71;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Reederei
 *
 * @ORM\Table(name="reederei", uniqueConstraints={@ORM\UniqueConstraint(name="id", columns={"id"})}, indexes={@ORM\Index(name="flagge", columns={"flagge"})})
 * @ORM\Entity
 */
class Reederei
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
     * @ORM\Column(name="name", type="string", length=25, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="flagge", type="text", length=65535, nullable=false)
     */
    private $flagge;

    /**
     * @var string
     *
     * @ORM\Column(name="company", type="text", length=65535, nullable=false)
     */
    private $company;

    /**
     * @var string
     *
     * @ORM\Column(name="company_name", type="string", length=254, nullable=false)
     */
    private $companyName;

    /**
     * @var string
     *
     * @ORM\Column(name="company_street", type="string", length=254, nullable=false)
     */
    private $companyStreet;

    /**
     * @var string
     *
     * @ORM\Column(name="company_zip", type="string", length=32, nullable=false)
     */
    private $companyZip;

    /**
     * @var string
     *
     * @ORM\Column(name="company_city", type="string", length=254, nullable=false)
     */
    private $companyCity;

    /**
     * @OneToMany(targetEntity="ShipTable", mappedBy="reederei")
     */
    private $ships;
    /**
     * @var string
     *
     * @ORM\Column(name="logo_unified", type="string", length=254, nullable=false)
     */
    private $logoUnified;

    public function __construct()
    {
        $this->ships = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getFlagge(): ?string
    {
        return $this->flagge;
    }

    public function setFlagge(string $flagge): self
    {
        $this->flagge = $flagge;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(string $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): self
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function getCompanyStreet(): ?string
    {
        return $this->companyStreet;
    }

    public function setCompanyStreet(string $companyStreet): self
    {
        $this->companyStreet = $companyStreet;

        return $this;
    }

    public function getCompanyZip(): ?string
    {
        return $this->companyZip;
    }

    public function setCompanyZip(string $companyZip): self
    {
        $this->companyZip = $companyZip;

        return $this;
    }

    public function getCompanyCity(): ?string
    {
        return $this->companyCity;
    }

    public function setCompanyCity(string $companyCity): self
    {
        $this->companyCity = $companyCity;

        return $this;
    }

    public function getLogoUnified(): ?string
    {
        return $this->logoUnified;
    }

    public function setLogoUnified(string $logoUnified): self
    {
        $this->logoUnified = $logoUnified;

        return $this;
    }

}
