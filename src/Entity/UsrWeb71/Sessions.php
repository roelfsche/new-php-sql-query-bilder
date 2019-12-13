<?php

namespace App\Entity\UsrWeb71;

use Doctrine\ORM\Mapping as ORM;

/**
 * Sessions
 *
 * @ORM\Table(name="sessions", indexes={@ORM\Index(name="last_active", columns={"last_active"})})
 * @ORM\Entity
 */
class Sessions
{
    /**
     * @var string
     *
     * @ORM\Column(name="session_id", type="string", length=24, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $sessionId;

    /**
     * @var int
     *
     * @ORM\Column(name="last_active", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $lastActive;

    /**
     * @var string
     *
     * @ORM\Column(name="contents", type="text", length=65535, nullable=false)
     */
    private $contents;

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function getLastActive(): ?int
    {
        return $this->lastActive;
    }

    public function setLastActive(int $lastActive): self
    {
        $this->lastActive = $lastActive;

        return $this;
    }

    public function getContents(): ?string
    {
        return $this->contents;
    }

    public function setContents(string $contents): self
    {
        $this->contents = $contents;

        return $this;
    }


}
