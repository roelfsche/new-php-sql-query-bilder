<?php

namespace App\Maridis\File;

use Archive7z\Archive7z;
use App\Entity\UsrWeb71\ShipTable as UsrWeb71ShipTable;
use App\Exception\MscException;
use App\Maridis\File;
use App\Maridis\FileInterface;
use App\Service\Maridis\AesCipher;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Mpi extends File implements FileInterface
{

    private $strDecryptedDeflatedFileName = '';

    public static function getIf(ContainerInterface $objContainer, $resFileHandle): ?FileInterface 
    {
        $strFileName = self::getFileNameFromResource($resFileHandle);
        $arrTmp = [];
        if (!preg_match('/\.mpi$/', $strFileName, $arrTmp)) {
            return NULL;
        }

        return new Mpi($objContainer, $resFileHandle);
    }

    public function __construct($objContainer, $objFileHandle) {

        parent::__construct($objContainer, $objFileHandle);

        // Entschlüsseln
        $arrStat = fstat($objFileHandle);
        $intFileSize = $arrStat['size'];

        $arrParameters = $this->objContainer->getParameter('msc_interface');
        $this->strUploadPath = $this->objPropertAccess->getValue($arrParameters, '[reports][upload_path]');

        $objAesCipher = new AesCipher($this->objPropertAccess->getValue($arrParameters, '[mpi][init_vector]'));//Kohana::$config->load('interface.mpi.init_vector'));
        // wirft Exception im Fehlerfall
        $strZipped = $objAesCipher->decrypt($this->objPropertAccess->getValue($arrParameters, '[mpi][secret_key]'), fread($objFileHandle, $intFileSize));
        $resZippedTmpFile = tmpfile();
        if (!fwrite($resZippedTmpFile, $strZipped))
        {
            throw new MscException('Could not write decrypted content to tmp file.');
        }

        // Entpacken --> datei heißt dann 'content'
        $strCompleteZippedFileName = stream_get_meta_data($resZippedTmpFile)['uri'];
        $strTmpPath = sys_get_temp_dir();

        // $objZipExtractor = new SevenZipArchive($strCompleteZippedFileName, array('binary' => '7za'));
        // $objZipExtractor->extractTo($strTmpPath, array('content'));//, $filename);


        $objArchive7z = new Archive7z($strCompleteZippedFileName, $this->objPropertAccess->getValue($arrParameters, '[7z][bin]'));//$this->str7ZipBinaryPath);
        $objArchive7z->setOutputDirectory($strTmpPath);//$this->strPath);
        // $arrFiles = $objArchive7z->getEntries(); // [0]['path'] = 'content'
        $objArchive7z->extract();




        unlink($strCompleteZippedFileName);

        $this->strDecryptedDeflatedFileName = $strTmpPath . '/content';


    }

    public function process()
    {
        $objSimpleXml = simplexml_load_file($this->strDecryptedDeflatedFileName);//, 'de.maridis.mpa.io.MPIFile');
        $arrNodes = $objSimpleXml->xpath('//marprimeSerial');
        if (!$arrNodes || count($arrNodes) != 1)
        {
            throw new MscException('marprime-Serial-number not found in MPI-File.');
        }

        $strMarprimeSerialNumber = $arrNodes[0]->__toString();
        $objShipRepository = $this->objDoctrineDefaultManager->getRepository(UsrWeb71ShipTable::class);
        $this->objShip = $objShipRepository->findByMarprimeSerialNo($strMarprimeSerialNumber);
     }
}
