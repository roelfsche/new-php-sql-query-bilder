<?php

namespace App\Service\Maridis;

use PhpImap\Mailbox;

/**
 * will von aussen den Pfad setzen, in dem die Attachments gespeichert werden
 */
class Imap extends \SecIT\ImapBundle\Service\Imap
{
    // ... mit trailing /
    private $strAttachmentPath = null;

    /**
     * Setter für Tmp-Path zum Abspeichern der Attachments
     */
    public function setAttachmentPath(string $strAttachmentPath)
    {
        $this->strAttachmentPath = rtrim($strAttachmentPath, '/') . '/';
    }

    public function getAttachmentPath(): string
    {
        return $this->strAttachmentPath;
    }

    /**
     * Get new mailbox instance.
     *
     * @param string $name
     *
     * @return Mailbox
     *
     * @throws \Exception
     */
    protected function getMailbox($name)
    {
        if (!isset($this->connections[$name])) {
            throw new \Exception(sprintf('Imap connection %s is not configured.', $name));
        }

        $config = $this->connections[$name];

        if (!isset($config['attachments_dir']) && $this->strAttachmentPath) {
            $config['attachments_dir'] = $this->strAttachmentPath;
        }

        if (isset($config['attachments_dir'])) {
            $this->checkAttachmentsDir($config['attachments_dir']);
        }

        return new Mailbox(
            $config['mailbox'],
            $config['username'],
            $config['password'],
            isset($config['attachments_dir']) ? $config['attachments_dir'] : null,
            isset($config['server_encoding']) ? $config['server_encoding'] : 'UTF-8'
        );
    }
}
