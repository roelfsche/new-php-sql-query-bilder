<?php

namespace App\Maridis;

use App\Entity\UsrWeb71\ShipTable;
use App\Maridis\File\DailyMail;
use App\Maridis\File\Mpd;
use App\Maridis\File\Mpi;
use App\Maridis\File\Pdf;
use App\Maridis\File\VoyageReport;
// use App\Entity\UsrWeb71\ShipTable as ShipTable;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\Exception\SyntaxErrorException;
use ErrorException;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Basisklassen aller .mpi, .noon, .pdf, .mpd usw. Files
 */
abstract class File
{
    protected $objContainer = null;
    /**
     * @var Doctrine\Common\Persistence\ManagerRegistry
     */
    protected $objDoctrineManagerRegistry = null;
    /**
     * @var Doctrine\Commom\Persistance\ObjectManager
     */
    protected $objDoctrineDefaultManager = null;
    /**
     * absoluter Pfad + Filename
     * @var string
     */
    protected $strAbsoluteFileName = null;
    protected $resFileHandle = null;
    protected $arrHeader = [];
    protected $objLogger = null;
    /**
     * @var App\Entity\UsrWeb71\ShipTable
     */
    protected $objShip = null;

    /**
     * @var Symfony\Component\PropertyAccess\PropertyAccess
     */
    protected $objPropertAccess = null;
    /**
     * @var Doctrine\DBAL\Connection
     */
    protected $objDbConnection = null;

    public function __construct(ContainerInterface $objContainer, $resFileHandle)
    {
        $this->objContainer = $objContainer;
        $this->resFileHandle = $resFileHandle;
        $this->strAbsoluteFileName = self::getFileNameFromResource($resFileHandle);

        $this->objLogger = $objContainer->get('logger');
        $this->objDoctrineManagerRegistry = $objContainer->get('doctrine');
        $this->objDoctrineDefaultManager = $this->objDoctrineManagerRegistry->getManager();
        $this->objDbConnection = $objContainer->get('doctrine.dbal.default_connection');
        $this->objPropertAccess = PropertyAccess::createPropertyAccessor(); //$objContainer->get('property_accessor');
        // exit();
    }

    /**
     * checkt, ob das File eines von Maridis-Files ist.
     *
     * Wenn ja, wird ein entsprechendes Interface zurück gegeben.
     *
     * @return FileInterface
     */
    public static function getMaridisFile(ContainerInterface $objContainer, $strFileName)
    {
        if (!$resFileHandle = fopen($strFileName, "rb")) {
            throw new FileNotFoundException("Could not open file: ", $strFileName);
        }

        $objFileInterface = null;
        if ($objFileInterface = Mpi::getIf($objContainer, $resFileHandle)) {
            return $objFileInterface;
        }
        if ($objFileInterface = VoyageReport::getIf($objContainer, $resFileHandle)) {
            return $objFileInterface;
        }
        if ($objFileInterface = Mpd::getIf($objContainer, $resFileHandle)) {
            return $objFileInterface;
        }
        if ($objFileInterface = DailyMail::getIf($objContainer, $resFileHandle)) {
            return $objFileInterface;
        }
        if ($objFileInterface = Pdf::getIf($objContainer, $resFileHandle)) {
            return $objFileInterface;
        }
    }

    public function getShip() //: ShipTable

    {
        return $this->objShip;
    }
    public function setShip(ShipTable $objShip)
    {
        $this->objShip = $objShip;
    }

    /**
     * checkt, ob dieses File vom Typ ist
     */
    abstract public static function getIf(ContainerInterface $objContainer, $resFileHandle): ?FileInterface;

    /**
     * liefert den Filenamen zur Ressource
     * @throws ErrorException
     */
    protected static function getFileNameFromResource($resFileHandle/* = NULL*/)
    {
        // if (!$resFileHandle) {
        //     $resFileHandle = $this->resFileHandle;
        // }
        if (!is_resource($resFileHandle)) {
            throw new ErrorException(('Parameter is not a resource ' . $resFileHandle));
        }
        $arrMetaData = stream_get_meta_data($resFileHandle);
        return $arrMetaData['uri'];
    }

    // protected function getFilenameFromResource($resFileHandle = NULL) {

    // }

    protected function ArrGet($arrArr, $strPath, $mixedDefault = "''")
    {

    }

    protected function executeQuery($strQuery, $strConnection = 'default')
    { //ObjectManager $objManager = NULL) {
        $objConnection = $this->objDoctrineManagerRegistry->getConnection($strConnection); //$objManager->getConnection();
        // echo $strQuery . "\n\n\n";
        $objStmt = $objConnection->prepare($strQuery);
        try {
            $objStmt->execute();
            return $objStmt;
        } catch (SyntaxErrorException $objSEE) {
            $this->objLogger->warning("Error in SQL-Statement " . $objSEE->getMessage());
        }
        return null;
    }

    /**
     *
     * @return int neuer Prim-Key
     */
    protected function executeInsert($strQuery, $strConnection = 'default')
    { //ObjectManager $objManager = NULL) {
        $objConnection = $this->objDoctrineManagerRegistry->getConnection($strConnection); //$objManager->getConnection();
        $objStmt = $objConnection->prepare($strQuery);
        $objStmt->execute();
        return $objConnection->lastInsertId();
    }

    /**
     * Übernahme aus alter Schnittstelle
     */
    public function ShiftToPosition($intCount)
    {
        $values = unpack("a" . $intCount . "Identifier/v1Version/V1Datasize", fread($this->resFileHandle, $intCount + 6));
        return $values['Datasize'];
    }

    /**
     *
     */
    public function setLastMeasurementData($intLatestTs, $strImoNumber = null, $strCdsSerialNumber = null, $strMarprimeSerialNumber = null)
    {
        $strCriteria = false; // enthält das Where-Kriterium
        foreach (array('IMO_No' => $strImoNumber, 'CDS_SerialNo' => $strCdsSerialNumber, 'MarPrime_SerialNo' => $strMarprimeSerialNumber) as $strFieldName => $strValue) {
            if ($strValue) {
                $strCriteria = $strFieldName . " = '$strValue'";
                break;
            }
        }
        if (!$strCriteria) {
            echo "Fehler: keine eindeutige Id zum Holen des ship_data-Datensatzes erhalten\n";
            return false;
        }

        $strSql = 'SELECT UNIX_TIMESTAMP(last_measurement) AS latest_ts FROM ship_table WHERE ' . $strCriteria;

        $objResult = $this->executeQuery($strSql);
        // $objResult = mysql_query($strSql, $_SESSION['DB_CONNECTION']['usr_web7_1']);
        if (!$objResult->rowCount()) {
            return false;
        }

        $arrRow = $objResult->fetch();
        // $arrRow = mysql_fetch_assoc($objResult);
        if (!$arrRow) {
            echo "Fehler beim Holen des last-measurement-Timestamps: " . $strSql . "\n";
            return false;
        }

        $intDbLatestTs = (int) $arrRow['latest_ts'];
        if ($intLatestTs > $intDbLatestTs) {
            $strSql = 'UPDATE ship_table SET last_measurement = \'' . date('Y-m-d', $intLatestTs) . '\' WHERE ' . $strCriteria;
            $this->executeQuery($strSql);
            // mysql_query($strSql, $_SESSION['DB_CONNECTION']['usr_web7_1']);
        }

        return true;
    }
}
