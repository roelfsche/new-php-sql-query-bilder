<?php

// src/Command/CreateUserCommand.php
namespace App\Command;

use App\Entity\UsrWeb71\InterfaceErrorMails;
use App\Service\InterfaceAttachment;
use PhpImap\Exceptions\ConnectionException;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use SecIT\ImapBundle\Service\Imap;

class InterfaceCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'msc:interface';

    protected $objContainer = NULL;

    protected $objPropertyAccess = NULL; // für einfachen Zugriff auf Arrays

    protected $objImap = NULL;

    protected $objLogger = NULL;

    protected $objInterfaceAttachment = NULL;

    public function __construct(ContainerInterface $container, LoggerInterface $logger, InterfaceAttachment $objInterfaceAttachment)
    {
        parent::__construct();
        $this->objContainer = $container;
        $this->objLogger = $logger;

        // $this->objContainer = $this->getApplication()->getKernel()->getContainer();
        $this->objPropertyAccess = PropertyAccess::createPropertyAccessor();
        $this->objImap = $this->objContainer->get('secit.imap');
        $this->objInterfaceAttachment = $objInterfaceAttachment;
    }

    protected function configure()
    {
        // ...
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arrParameters = $this->objContainer->getParameter('interface');
        try {
            $objMailBox = $this->objImap->get('dev_connection');

            $arrMessageIds = $objMailBox->searchMailbox();
        } catch (ConnectionException $objCE) {
            $this->objLogger->error('Could not open mailbox');
            return 127;
        }

        foreach ($arrMessageIds as $intMessageId) {
            $arrMailInfos = $objMailBox->getMailsInfo([$intMessageId]);
            $objMailInfo = $arrMailInfos[0];

            $this->objLogger->info('Processing E-Mail: Subject: ' . $objMailInfo->subject);

            /**
             * @var IncomingMail
             */
            $objMail = $objMailBox->getMail($intMessageId);
            // check Grösse
            if ($objMailInfo->size > $this->objPropertyAccess->getValue($arrParameters, '[mail][max_size]')) {
                $this->objLogger->warning('E-Mail size exceeds limit; not processed...');
                $objMailBox->deleteMail($objMailInfo->message_id);
                continue;
            }

            // alle Mails von @maridis.de ignorieren
            // ausser test@maridis.de
            $strFrom = $objMailInfo->from;
            if ($this->objContainer->getParameter('kernel.environment') == 'prod') {
                $strSmallFrom = strtolower($strFrom);
                if (strpos($strSmallFrom, '@maridis.de') !== FALSE) {
                    if (strpos($strSmallFrom, 'test@maridis.de') === FALSE) {
                        $objMailBox->deleteMail($objMailInfo->message_id);
                        // Helper_Log::logHtmlSnippet("IGNORIERE EMAIL, KOMMT VON MARIDIS");
                        $this->objLogger->info("IGNORIERE EMAIL, KOMMT VON MARIDIS");
                        continue;
                    }
                }
            }

            $arrAttachments = $objMail->getAttachments();
            if (!count($arrAttachments)) {
                $this->objLogger->info('No attachments found.');
                $objMailBox->deleteMail($objMaliInfox->message_id);
                continue;
            }

            $objDoctrineManager = $this->objContainer->get('doctrine')->getManager();
            $objErrorMessageRepository = $objDoctrineManager->getRepository(InterfaceErrorMails::class);
            $objErrorMessage = $objErrorMessageRepository->findOneBy(array('messageId' => $objMailInfo->message_id));

            if ($objErrorMessage) {
                // Helper_Log::logException($objException);
                $this->objLogger->warning(strtr('E-Mail produces Exception; Message-ID = :message_id, Subject = :subject, first processed on :date; try again', [
                    ':message_id' => $objMailInfo->message_id,
                    ':subject' => $objMailInfo->subject,
                    ':date' => date('d.m.Y H:i:s', $objErrorMessage->getCreateTs())
                ]));
            } else {
                $objErrorMessage = $objErrorMessageRepository->insertMail($objMailInfo);
            }

            $this->objInterfaceAttachment->init($arrAttachments,$this->objPropertyAccess->getValue($arrParameters, '[7z][bin]') );
            $this->objInterfaceAttachment->process();
            // Attachments verarbeiten
            // $this->progressFiles($arrAttachments);


            if ($this->objContainer->getParameter('kernel.environment') == 'prod') {
                // wird sofort gelöscht
                $objMailBox->deleteMail($objMailInfo->message_id);
            }

            $objDoctrineManager->remove($objErrorMessage);
            $objDoctrineManager->flush();


            $a = 0;
        }
        // if ($this->objPropertyAccss->isReadable($arrMailboxConfig, 'port')) {
        //     $objMailBox = new Imap(Arr::get($arrMailboxConfig, 'host'), Arr::get($arrMailboxConfig, 'port'));
        // } else {
        //     $objMailBox = new Imap(Arr::get($arrMailboxConfig, 'host'));
        // }
        return 0;
    }

        //        Interface_Attachment::rmTmpDir($this->strTmpDir);
}
