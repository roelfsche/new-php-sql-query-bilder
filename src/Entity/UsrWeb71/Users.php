<?php

namespace App\Entity\UsrWeb71;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Users
 *
 * @ORM\Table(name="users", indexes={@ORM\Index(name="username", columns={"username"}), @ORM\Index(name="email", columns={"email"})})
 * @ORM\Entity(repositoryClass="App\Repository\UsrWeb71\UserRepository")
 */
class Users
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false, options={"comment"="notwendig, wenn historienfähig"})
     */
    
    private $userId;
    /**
     * @ORM\OneToMany(targetEntity="ShipFavorites", mappedBy="users")
     */
    private $ship_favorites;
    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=32, nullable=false)
     */
    private $username = '';

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=64, nullable=false)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=254, nullable=false)
     */
    private $email;

    /**
     * @var bool
     *
     * @ORM\Column(name="salutation", type="boolean", nullable=false)
     */
    private $salutation;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=32, nullable=false)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=100, nullable=false)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=100, nullable=false)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="data", type="text", length=65535, nullable=false)
     */
    private $data;

    /**
     * @var string
     *
     * @ORM\Column(name="timezone", type="string", length=50, nullable=false)
     */
    private $timezone;

    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=5, nullable=false)
     */
    private $locale;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_system_user", type="boolean", nullable=false, options={"comment"="wird nur bei system-usern im backend angezeigt"})
     */
    private $isSystemUser;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_active", type="boolean", nullable=false)
     */
    private $isActive;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_latest", type="boolean", nullable=false, options={"comment"="notwendig, wenn historienfähig"})
     */
    private $isLatest;

    /**
     * @var int|null
     *
     * @ORM\Column(name="last_login", type="integer", nullable=true)
     */
    private $lastLogin;

    /**
     * @var int
     *
     * @ORM\Column(name="logins", type="integer", nullable=false, options={"comment"="Anzahl Logins"})
     */
    private $logins = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="create_ts", type="integer", nullable=false)
     */
    private $createTs = '0';

    public function __construct()
    {
        $this->ship_favorites = new ArrayCollection();
    }

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

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getSalutation(): ?bool
    {
        return $this->salutation;
    }

    public function setSalutation(bool $salutation): self
    {
        $this->salutation = $salutation;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

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

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getIsSystemUser(): ?bool
    {
        return $this->isSystemUser;
    }

    public function setIsSystemUser(bool $isSystemUser): self
    {
        $this->isSystemUser = $isSystemUser;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getIsLatest(): ?bool
    {
        return $this->isLatest;
    }

    public function setIsLatest(bool $isLatest): self
    {
        $this->isLatest = $isLatest;

        return $this;
    }

    public function getLastLogin(): ?int
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?int $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getLogins(): ?int
    {
        return $this->logins;
    }

    public function setLogins(int $logins): self
    {
        $this->logins = $logins;

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

    /**
     * @return Collection|ShipFavorites[]
     */
    public function getShipFavorites(): Collection
    {
        return $this->ship_favorites;
    }

    public function addShipFavorite(ShipFavorites $shipFavorite): self
    {
        if (!$this->ship_favorites->contains($shipFavorite)) {
            $this->ship_favorites[] = $shipFavorite;
            $shipFavorite->setUserId($this);
        }

        return $this;
    }

    public function removeShipFavorite(ShipFavorites $shipFavorite): self
    {
        if ($this->ship_favorites->contains($shipFavorite)) {
            $this->ship_favorites->removeElement($shipFavorite);
            // set the owning side to null (unless already changed)
            if ($shipFavorite->getUserId() === $this) {
                $shipFavorite->setUserId(null);
            }
        }

        return $this;
    }

}
