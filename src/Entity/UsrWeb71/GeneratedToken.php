<?php

namespace App\Entity\UsrWeb71;

use Doctrine\ORM\Mapping as ORM;

/**
 * GeneratedToken
 *
 * @ORM\Table(name="generated_token")
 * @ORM\Entity
 */
class GeneratedToken
{
    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=100, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $token;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="generated_time", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $generatedTime = 'CURRENT_TIMESTAMP';

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=20, nullable=false)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="CDS_SerialNo", type="string", length=20, nullable=false)
     */
    private $cdsSerialno;

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getGeneratedTime(): ?\DateTimeInterface
    {
        return $this->generatedTime;
    }

    public function setGeneratedTime(\DateTimeInterface $generatedTime): self
    {
        $this->generatedTime = $generatedTime;

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

    public function getCdsSerialno(): ?string
    {
        return $this->cdsSerialno;
    }

    public function setCdsSerialno(string $cdsSerialno): self
    {
        $this->cdsSerialno = $cdsSerialno;

        return $this;
    }


}
