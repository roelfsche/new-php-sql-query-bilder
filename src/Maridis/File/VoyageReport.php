<?php

namespace App\Maridis\File;

use App\Maridis\File;
use App\Maridis\FileInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class VoyageReport extends File implements FileInterface
{
    /**
     * DB-Connection
     *
     * @var Doctrine\DBAL\Connection;
     */
    private $objConnection;
    /**
     *
     *
     * @param ContainerInterface $objContainer
     * @param [type] $resFileHandle
     */
    public function __construct(ContainerInterface $objContainer, $resFileHandle)
    {
        parent::__construct($objContainer, $resFileHandle);

        $this->objConnection = $this->objDoctrineManagerRegistry
            ->getManager('marnoon')
            ->getConnection();
    }
    public static function getIf(ContainerInterface $objContainer, $resFileHandle): ?FileInterface
    {

        $strFilename = self::getFileNameFromResource($resFileHandle);
        $arrTmp = array();
        if (!preg_match('/VoyageReport_IMO(\d)+_\d{4}-(\d{2})-(\d{2})_(\d{1,2})_(\d{1,2})_(\d{1,2})/', $strFilename, $arrTmp)) {
            return null;
        }

        return new VoyageReport($objContainer, $resFileHandle);
    }

    public function process()
    {
        $this->NoonDataToDB($this->strAbsoluteFileName);
    }

    /**
     * alte Funktion recycled
     *
     * @param string $datei - vollst. Pfad
     * @return void
     */
    public function NoonDataToDB($datei)
    {
        // Zuordnung der Spaltennamen zu den Positionen in der Datei
        $arrValidColumns = array(
            'ID' => -1,
            'IMO' => -1,
            'Vessel' => -1,
            'EngineTyp' => -1,
            'LastEntry' => -1,
            'LastDataSend' => -1,
            'date' => -1,
            'date_ID' => -1,
            'Captain' => -1,
            'ChiefEng' => -1,
            'VoyNumber' => -1,
            'VoyFrom' => -1,
            'VoyTo' => -1,
            'Arrival' => -1,
            'Departure' => -1,
            'VoyStart' => -1,
            'VoyEnd' => -1,
            'TimeAtSea' => -1,
            'TimeAtRiver' => -1,
            'TimeAtPort' => -1,
            'NoonPos' => -1,
            'RiverMiles' => -1,
            'SeaMiles' => -1,
            'MilesCount' => -1,
            'TheoMiles' => -1,
            'MilesThroughWater' => -1,
            'SpeedThroughWater' => -1,
            'SpeedOverGround' => -1,
            'SlipThroughWater' => -1,
            'Slip' => -1,
            'CargoTotal' => -1,
            'CargoInHold' => -1,
            'CargoType' => -1,
            'DraftFore' => -1,
            'DraftAft' => -1,
            'WindForce' => -1,
            'WindDirToVessel' => -1,
            'SeaScale' => -1,
            'SeaScaleToVessel' => -1,
            'MERevCount' => -1,
            'MESpeedAvg' => -1,
            'MEPowerCount' => -1,
            'MEPowerAvg' => -1,
            'MEFuelCount' => -1,
            'MEFuelDensity' => -1,
            'MEFuelOilConsum' => -1,
            'MESFOC' => -1,
            'MECylOilInput' => -1,
            'MECylOilDensity' => -1,
            'MECylOilConsum' => -1,
            'MELubOilInput' => -1,
            'FuelPumpIndex' => -1,
            'METurboRpm' => -1,
            'Pitch' => -1,
            'AEinUse' => -1,
            'AEPower' => -1,
            'AEFuelInput' => -1,
            'AEFuelOutput' => -1,
            'AEFuelDensity' => -1,
            'AEFuelOilConsum' => -1,
            'AESFOC' => -1,
            'AELubOilInput' => -1,
            'BoilerFuelCount' => -1,
            'BoilerFuelDensity' => -1,
            'BoilerFuelConsum' => -1,
        );

        $dataArray = file($datei);
        $arrHeaderColumns = array();
        $arrValues = array();

        $tableHeader = "";
        $timeValue = null;
        $IMOValue = null;
        $timeCnt = 0;
        $IMOCnt = 0;
        $sqlString = "INSERT IGNORE INTO voyagereport( ";
        $komma = '';
        foreach ($dataArray as $line) {
            $line = trim(preg_replace('/;\s*$/', '', $line));
            if ("" === $tableHeader) {
                $i = 0;
                $tableHeader = explode(';', $line);
                foreach ($tableHeader as $intIndex => $field) {
                    // filtere jetzt die Spalten (in der Datei stehen mehr als in die DB sollen)
                    if (array_key_exists($field, $arrValidColumns)) {
                        $arrValidColumns[$field] = $intIndex;
                        $arrHeaderColumns[] = $field;
                    }
                    if ($field == "IMO") {
                        $IMOCnt = $i;
                    }

                    if ($field == "date") {
                        $timeCnt = $i;
                    }

                    $i++;
                }

                $sqlString .= implode(', ', $arrHeaderColumns) . ') VALUES ';
            } else {
                $dataSet = explode(';', $line);
                foreach ($dataSet as $intIndex => $value) {
                    // wurde der Spaltenname/-index oben gefunden?
                    if (array_search($intIndex, $arrValidColumns)) {
                        $arrValues[$intIndex] = "'" . $value . "'";

                        if ($IMOCnt == $intIndex) {
                            $IMOValue = $value;
                        }
                        if ($timeCnt == $intIndex) {
                            $tmpTime = strtotime($value);
                            if ($tmpTime > $timeValue) {
                                $timeValue = $tmpTime;
                            }
                        }
                    }
                }

                $sqlString .= $komma . '(' . implode(', ', $arrValues) . ')';
                $arrValues = array();
                $komma = ', ';
            }
        }

        try {
            $this->objConnection->prepare($sqlString)
                ->execute();
        } catch (DBALException $objDbException) {
            $this->objLogger->alert('DB-Exception: ' . $objDbException->getMessage());
            return array(false, null, null);
        }
//    $objDbInstance = Database::instance('marnoon');
        //    $objDbInstance = Database::instance();
        //    $erg = $objDbInstance->query(Database::INSERT, $sqlString);
        // $erg = mysql_query($sqlString, $_SESSION['DB_CONNECTION']['MarNoon']);

        // if (!$erg) {
        //     echo mysql_error() . "\n";
        //     return array(false, null, null);
        // } else {
        return array(true, $timeValue, $IMOValue);
        // }

    }
}
