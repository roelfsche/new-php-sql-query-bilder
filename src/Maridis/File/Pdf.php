<?php

namespace App\Maridis\File;

use App\Entity\UsrWeb71\GeneratedReports;
use App\Exception\MscException;
use App\Maridis\File;
use App\Maridis\FileInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Pdf extends File implements FileInterface
{

    private $strUploadPath = null;

    /**
     * filename ohne Pfad
     */
    private $strFileName = null;

    public function __construct(ContainerInterface $objContainer, $resFileHandle)
    {
        parent::__construct($objContainer, $resFileHandle);

        $arrParameters = $this->objContainer->getParameter('msc_interface');
        $this->strUploadPath = rtrim($this->objPropertAccess->getValue($arrParameters, '[reports][upload_path]'), '/') . '/';
        $this->strFileName = basename($this->strAbsoluteFileName);
    }

    /**
     * checkt, ob es sich um eine Datei diesen Typs handelt
     * @return Pdf / null
     */
    public static function getIf(ContainerInterface $objContainer, $resFileHandle): ?FileInterface
    {
        $strFileName = self::getFileNameFromResource($resFileHandle);
        $arrTmp = [];
        if (!preg_match('/\.pdf$/', $strFileName, $arrTmp)) {
            return null;
        }

        return new Pdf($objContainer, $resFileHandle);
    }

    /**
     * verschiebt das File ins Zielverz. und schreibt den Eintrag in die DB
     */
    public function process()
    {
        if (!$this->objShip) {
            throw new MscException('Kann PDF-Datei nicht verarbeiten, da keine Ship-Id gesetzt');
        }

        $objDbReport = new GeneratedReports();
        $objDbReport->setShipId($this->objShip->getId())
            ->setType('interface-upload')
            ->setPeriod('') // möchte Hans so (Comment übernommen aus alter Schnittstelle)
            ->setFromTs(0)
            ->setToTs(0)
            ->setCreateTs(time())
            ->setModifyTs(time())
            ->setData([
                'original_filename' => $this->strFileName,
            ]);
        // $objDbReport = Jelly::factory('Row_Generated_Report')->set(array(
        //     'ship' => $this->objShip->id,
        //     'type' => 'interface-upload',
        //     'period' => '', // möchte Hans so...date('Y-m-d'),
        //     'from_ts' => 0,
        //     'to_ts' => 0,
        //     'data' => array(
        //         'original_filename' => $this->strFilename,
        //     ),
        // ));

//        $this->closeFile(); // File-Handle zu
        // File verschieben
        // $objDbReport->saveUploadedFile($this->strPath . $this->strFilename);
        $objDbReport->moveUploadedFile($this->strAbsoluteFileName, $this->strUploadPath);

        $objDoctrineManager = $this->objContainer->get('doctrine')->getManager();
        // $objGeneratedReportRepository = $objDoctrineManager->getRepository(GeneratedReports::class);
        $objDoctrineManager->persist($objDbReport);
        $objDoctrineManager->flush();

        // $objDbReport->save();
        $this->objLogger->debug("PDF-Datei " . $this->strFileName . " in der DB gespeichert.");
        // Helper_Log::logHtmlSnippet('PDF :pdf erfolgreich verarbeitet.', array(
        //     ':pdf' => $this->strFilename,
        // ));
    }
}
