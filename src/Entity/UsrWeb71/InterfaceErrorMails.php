<?php

namespace App\Entity\UsrWeb71;

use Doctrine\ORM\Mapping as ORM;

/**
 * InterfaceErrorMails
 *
 * @ORM\Table(name="interface_error_mails", uniqueConstraints={@ORM\UniqueConstraint(name="message_id", columns={"message_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\UsrWeb71\InterfaceErrorMailsRepository")
 */
class InterfaceErrorMails
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
     * @ORM\Column(name="message_id", type="string", length=255, nullable=false)
     */
    private $messageId;

    /**
     * @var string
     *
     * @ORM\Column(name="header", type="text", length=65535, nullable=false)
     */
    private $header;

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

    public function getMessageId(): ?string
    {
        return $this->messageId;
    }

    public function setMessageId(string $messageId): self
    {
        $this->messageId = $messageId;

        return $this;
    }

    public function getHeader(): ?string
    {
        return $this->header;
    }

    public function setHeader(string $header): self
    {
        $this->header = $header;

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
