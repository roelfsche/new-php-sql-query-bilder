<?php

namespace App\Entity\UsrWeb71;

use Doctrine\ORM\Mapping as ORM;

/**
 * IpLogger
 *
 * @ORM\Table(name="ip_logger")
 * @ORM\Entity
 */
class IpLogger
{
    /**
     * @var string
     *
     * @ORM\Column(name="HTTP_USER_AGENT", type="text", length=65535, nullable=false)
     */
    private $httpUserAgent;

    /**
     * @var string
     *
     * @ORM\Column(name="HTTP_COOKIE", type="string", length=50, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $httpCookie;

    /**
     * @var string
     *
     * @ORM\Column(name="REMOTE_ADDR", type="string", length=15, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $remoteAddr;

    /**
     * @var string
     *
     * @ORM\Column(name="SCRIPT_FILENAME", type="string", length=50, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $scriptFilename;

    /**
     * @var string
     *
     * @ORM\Column(name="REQUEST_TIME", type="string", length=20, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $requestTime;

    /**
     * @var string
     *
     * @ORM\Column(name="CDS_SerialNo", type="string", length=11, nullable=false)
     */
    private $cdsSerialno;

    /**
     * @var string
     *
     * @ORM\Column(name="reederei", type="string", length=50, nullable=false)
     */
    private $reederei;

    public function getHttpUserAgent(): ?string
    {
        return $this->httpUserAgent;
    }

    public function setHttpUserAgent(string $httpUserAgent): self
    {
        $this->httpUserAgent = $httpUserAgent;

        return $this;
    }

    public function getHttpCookie(): ?string
    {
        return $this->httpCookie;
    }

    public function getRemoteAddr(): ?string
    {
        return $this->remoteAddr;
    }

    public function getScriptFilename(): ?string
    {
        return $this->scriptFilename;
    }

    public function getRequestTime(): ?string
    {
        return $this->requestTime;
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

    public function getReederei(): ?string
    {
        return $this->reederei;
    }

    public function setReederei(string $reederei): self
    {
        $this->reederei = $reederei;

        return $this;
    }


}
