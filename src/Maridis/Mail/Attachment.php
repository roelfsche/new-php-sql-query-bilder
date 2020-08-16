<?php

namespace App\Maridis\Mail;

use App\Entity\UsrWeb71\ShipTable;
use App\Exception\MscException;
use App\Maridis\File as MaridisFile;
use App\Maridis\FileInterface;
use App\Service\Maridis\SevenZipArchive;
// use App\Service\Maridis\File;
use App\Maridis\File\Pdf;
use Archive7z\Archive7z;
use ErrorException;
use PhpImap\IncomingMailAttachment;
use Psr\Log\LoggerInterface;
use SplFileInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Diese Klasse verarbeitet ein Attachment einer Email.
 *
 * Sie entpackt bei Bedarf alle Files und prozessiert sie dann
 */
class Attachment
{
    /**
     * /tmp/ - Order mit trailing /
     *
     * @var null|string
     */
    public $strPath = null;
    /**
     * Orginal-Filename
     *
     * Wenn es sich um ein Archiv (zip/rar/7z) handelt, steht hier anfänglich der archiv-Name drin.
     * Pro Iteration des Files dann immer der akt. Filename
     *
     * @var string|null
     */
    public $strFilename = null;

    /**
     * Anhang kann zip-Archiv sein, daher umgestellt auf mehrere
     * @var array
     */
    public $arrFiles = array();

    /**
     * gesetzt, wenn entpackt werden muss. Dann wird sie aus dem Dateinamen extrahiert
     *
     * @var string|null
     */
    public $strImoNumber = null;
    /**
     * @var Model_Ship
     */
    public $objShip = null;
    protected $objFileHandle = null;

    protected $arrAttachments = [];

    protected $str7ZipBinaryPath;

    /**
     * @var ContainerInterface
     */
    protected $objContainer;
    /**
     * @var LoggerInterface
     */
    protected $objLogger = null;

    /**
     * Konstruktor
     *
     * @param ContainerInterface $objContainer
     * @param array $arrAttachments - Array mit PhpImap\IncomingMailAttachment
     * @param string $str7ZipBinaryPath - Path zum Binary
     * @param string $strTmpDir - Path zu einem Tmp-Dir, wohin Dateien extrahiert werden können
     */
    public function __construct(ContainerInterface $objContainer, array $arrAttachments, string $str7ZipBinaryPath, string $strTmpDir = null)
    {
        $this->objContainer = $objContainer;
        $this->objLogger = $objContainer->get('logger'); //$objLogger;

        if ($strTmpDir) {
            $this->strPath = $strTmpDir;
        } else {
            // kreiere ein tmp-Dir
            $this->strPath = $this->tempdir(null, 'interface_attachement') . '/';
        }
        $this->str7ZipBinaryPath = $str7ZipBinaryPath;
        $this->arrAttachments = $arrAttachments;
        $this->arrFiles = [];
    }

    /**
     * extrahiert und verarbeitet alle Attachment-Files
     *
     * die IMAP-Library benennt die Attachments um, indem sie die Mail_Id und die Attachment_Id vor den Filenamen präfixt.
     * kriege den aber aus den Attachment-Daten wieder raus.
     *
     */
    public function process()
    {
        $objShip = null;
        $arrPdfFiles = [];

        // extrahiere alle Files aus allen Archiven
        foreach ($this->arrAttachments as $objAttachment) {
            $this->objLogger->debug('Saving / unzipping attachment: ' . $objAttachment->name);

            if (!preg_match('/(zip|7z|cpa|mpd|noon|mpi|pdf)/i', strtolower($objAttachment->name))) {

                $this->objLogger->debug('No Maridis-File (zip|7z|cpa|mpd|noon|mpi|pdf); ignore..');
                continue;
            }
            // $objAttachment->filePath = $this->strPath . $objAttachment->name;
            try {
                // $objAttachment->saveToDisk();
                // benenne das Attachment um, da das API die Message- und Attachment-Id vor den Filenamen schreibt
                $objSplFileInfo = new SplFileInfo($objAttachment->filePath);
                $strNewFilename = $objSplFileInfo->getPath() . '/' . $objAttachment->name;
                rename($objAttachment->filePath, $strNewFilename);

                $this->arrFiles = array_merge($this->arrFiles, $this->_extract($strNewFilename)); //objAttachment->filePath));
            } catch (ProcessFailedException $objPFE) {
                $this->objLogger->error('Error while unzipping Attachment ' . $objAttachment->name);
                $this->objLogger->error($objPFE->getMessage());
                $this->objLogger->error('attachment will not be processed');
            } catch (ErrorException $objE) {
                $this->objLogger->error('Error while saving Attachment ' . $objAttachment->name);
                $this->objLogger->error($objE->getMessage());
                $this->objLogger->error('No attachment processed');
                return;
            }
        }

        // Starte die Verarbeitung
        $e = error_reporting(E_PARSE | E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR);
        foreach ($this->arrFiles as $strFilename) {
            $objFile = MaridisFile::getMaridisFile($this->objContainer, $strFilename);
            if ($objFile instanceof FileInterface) {
                if ($objFile instanceof Pdf) {
                    $arrPdfFiles[] = $objFile;
                    continue;
                }
                $objFile->process();

                if (!$objShip && $objFile->getShip() instanceof ShipTable) {
                    $objShip = $objFile->getShip();
                }
            }
        }
        error_reporting($e);

        if (!$objShip) {
            return;
        }
        // nun alle PDF-Files
        foreach ($arrPdfFiles as $objPdfFile) {
            try
            {

                $objPdfFile->setShip($objShip);
                $objPdfFile->process();

            } catch (MscException $objMscExcpetion) {
                    $this->objLogger->error($objMscExcpetion->getMessage());
                // Helper_Log::logException($objMscExcpetion);
            }
        }

    }

    /**
     * Extrahiere - wenn zip/7z/noon-Archiv alle Dateien.
     *
     * Schaue rekursiv weiter, ob sich weitere zip-Archive drin befinden und entpacke die dann auch.
     *
     * @param string $strFilename
     * @return array - Struktur array(..., array('Name' => Filename), ... )
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     */
    private function _extract(string $strFilename)
    {
        // $strFilename = $objAttachment->filePath;
        $arrRet = array();
        if (preg_match('/\.(zip|7z|noon)/i', $strFilename)) {
            // IMO-Nummer
            $arrFilenameParts = explode('_', $strFilename);
            foreach ($arrFilenameParts as $strFilenamePart) {
                if (preg_match('/IMO[0-9]+$/', $strFilenamePart)) {
                    $this->strImoNumber = str_replace('IMO', '', $strFilenamePart);
                    break;
                }
            }

            $arr7ZipParams = array('binary' => $this->str7ZipBinaryPath);
            $strPassword = null;
            if (strpos(strtolower($strFilename), 'noon') !== false) {
                // Marnoon
                $strPassword = 'maridis-' . $this->strImoNumber;
                $arr7ZipParams['password'] = $strPassword;
            }

            $objZipExtractor = new SevenZipArchive($strFilename, $arr7ZipParams);
            $objZipExtractor->extractTo($this->strPath);//, $this->strFilename);
            $arrFiles = $objZipExtractor->entries();

            // $objArchive7z = new Archive7z($strFilename, $this->str7ZipBinaryPath);
            // if ($strPassword) {
            //     $objArchive7z->setPassword($strPassword);
            // }
            // $objArchive7z->setOutputDirectory($this->strPath);
            // $arrFiles = $objArchive7z->getEntries();
            // $objArchive7z->extract();

            // lösche das File
            unlink($strFilename);

            // Archive7z\Entry
            foreach ($arrFiles as $intIndex => $arrFile) {
            // foreach ($arrFiles as $intIndex => $objEntry) {
                $arrRet = array_merge($arrRet, $this->_extract($this->strPath . $arrFile['Name']));
                // $arrRet = array_merge($arrRet, $this->_extract($this->strPath . $objEntry->getPath()));
            }
            return $arrRet;
        } else {
            // benenne das File um --> Orginalname
            // $objSplFileInfo = new SplFileInfo($strFilename);
            // $strNewFilename = $objSplFileInfo->getPath() . '/' . $objAttachment->name;
            // rename($strFilename, $strNewFilename);
            // return array($strNewFilename);
            return array($strFilename);
            // bilde Struktur von SevenZipArchive-Lib nach
            // return array(array('Name' => $strFilename));
        }
    }

    /**
     * Erstellt ein tmp_dir.
     *
     *
     * von: https://stackoverflow.com/a/30010928/2933053
     *
     * @param null   $dir
     * @param string $prefix
     * @param int    $mode
     * @param int    $maxAttempts
     * @return bool|string
     */
    public static function tempdir($dir = null, $prefix = 'tmp_', $mode = 0700, $maxAttempts = 1000)
    {
        /* Use the system temp dir by default. */
        if (is_null($dir)) {
            $dir = sys_get_temp_dir();
        }

        /* Trim trailing slashes from $dir. */
        $dir = rtrim($dir, '/');

        /* If we don't have permission to create a directory, fail, otherwise we will
         * be stuck in an endless loop.
         */
        if (!is_dir($dir) || !is_writable($dir)) {
            return false;
        }

        /* Make sure characters in prefix are safe. */
        if (strpbrk($prefix, '\\/:*?"<>|') !== false) {
            return false;
        }

        /* Attempt to create a random directory until it works. Abort if we reach
         * $maxAttempts. Something screwy could be happening with the filesystem
         * and our loop could otherwise become endless.
         */
        $attempts = 0;
        do {
            $path = sprintf('%s/%s%s', $dir, $prefix, mt_rand(100000, mt_getrandmax()));
        } while (
            !mkdir($path, $mode) &&
            $attempts++ < $maxAttempts
        );

        return $path;
    }
}
