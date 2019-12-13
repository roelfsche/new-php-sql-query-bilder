<?php

namespace App\Service;

use App\Exception\MscException;
use Archive7z\Archive7z;
use ErrorException;
use PhpImap\IncomingMailAttachment;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

class InterfaceAttachment
{
    /**
     * /tmp/ - Order mit trailing /
     *
     * @var null|string
     */
    public $strPath = NULL;
    /**
     * Orginal-Filename
     *
     * Wenn es sich um ein Archiv (zip/rar/7z) handelt, steht hier anfänglich der archiv-Name drin.
     * Pro Iteration des Files dann immer der akt. Filename
     *
     * @var string|null
     */
    public $strFilename = NULL;

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
    public $strImoNumber = NULL;
    /**
     * @var Model_Ship
     */
    public $objShip = NULL;
    protected $objFileHandle = NULL;

    protected $arrAttachments = [];

    protected $str7ZipBinaryPath;

    /**
     * @var LoggerInterface
     */
    protected $objLogger = NULL;

    public function __construct(LoggerInterface $objLogger)
    {
        $this->objLogger = $objLogger;
    }
    /**
     * Initialisiert den Service.
     * 
     * @param array $arrAttachments - Array mit PhpImap\IncomingMailAttachment
     * @param string $str7ZipBinaryPath - Path zum Binary      
     * @param string $strTmpDir - Path zu einem Tmp-Dir, wohin Dateien extrahiert werden können
     */
    public function init(array $arrAttachments, string $str7ZipBinaryPath, string $strTmpDir = NULL)
    {
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

    public function process()
    {
        foreach ($this->arrAttachments as $objAttachment) {
            $this->objLogger->info('Processing attachment: ' . $objAttachment->name);

            if (!preg_match('/(zip|7z|cpa|mpd|noon|mpi|pdf)/i', strtolower($objAttachment->name))) {

                $this->objLogger->info('No Maridis-File (zip|7z|cpa|mpd|noon|mpi|pdf); ignore..');
                continue;
            }
            $objAttachment->filePath = $this->strPath . $objAttachment->name;
            try {
                $objAttachment->saveToDisk();
                $this->arrFiles = array_merge($this->arrFiles + $this->_extract($objAttachment->filePath));
            } catch (ProcessFailedException $objPFE) {
                $this->objLogger->error('Error while unzipping Attachment ' . $objAttachment->name);
                $this->objLogger->error($objPFE->getMessage());
                $this->objLogger->error('No attchment processed');
            } catch (ErrorException $objE) {
                $this->objLogger->error('Error while saving Attachment ' . $objAttachment->name);
                $this->objLogger->error($objE->getMessage());
                $this->objLogger->error('No attchment processed');
                return;
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

            $strPassword = NULL;
            if (strpos(strtolower($strFilename), 'noon') !== FALSE) {
                // Marnoon
                $strPassword = 'maridis-' . $this->strImoNumber;
                // $arr7ZipParams['password'] = $strPassword;
            }

            $objArchive7z = new Archive7z($strFilename, $this->str7ZipBinaryPath);
            if ($strPassword) {
                $objArchive7z->setPassword($strPassword);
            }
            $objArchive7z->setOutputDirectory($this->strPath);
            $arrFiles = $objArchive7z->getEntries();
            $objArchive7z->extract();

            // lösche das File
            unlink($strFilename);

            // Archive7z\Entry
            foreach ($arrFiles as $intIndex => $objEntry) {
                $arrRet = array_merge($arrRet, $this->_extract($this->strPath . $objEntry->getPath()));
            }
            return $arrRet;
        } else {
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
    static function tempdir($dir = null, $prefix = 'tmp_', $mode = 0700, $maxAttempts = 1000)
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
            return FALSE;
        }

        /* Make sure characters in prefix are safe. */
        if (strpbrk($prefix, '\\/:*?"<>|') !== FALSE) {
            return FALSE;
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
