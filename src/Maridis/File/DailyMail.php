<?php

namespace App\Maridis\File;

use App\Entity\UsrWeb71\ShipTable as UsrWeb71ShipTable;
use App\Maridis\File;
use App\Maridis\FileInterface;
use App\Service\Maridis\DailyMail\Version1001;
use App\Service\Maridis\DailyMail\Version1101;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DailyMail extends File implements FileInterface
{
    private $strImoNumber = null;

    protected $intMaxMeasTs = 0;

    // protected $arrSession = NULL;

    public static function getIf(ContainerInterface $objContainer, $resFileHandle): ?FileInterface
    {

        if (!is_resource($resFileHandle)) {
            throw new Exception('No Resource!');
        }

        fseek($resFileHandle, 0);
        $strContent = fread($resFileHandle, 50);
        if (strpos($strContent, "IDENT") != false) {
            $objFile = new DailyMail($objContainer, $resFileHandle);
            return $objFile;
        }

        return null;
    }

    public function process()
    {
        fseek($this->resFileHandle, 9, SEEK_SET);
        $text = fread($this->resFileHandle, 1024);
        $version = unpack("v1ddc", $text);

        // require_once 'ddc_version_1001.php';
        // require_once 'ddc_version_1101.php';
        $count = 45;
        // unset($_SESSION['CDS_Serial']);
        // $values = identification_data($text);
        $values = Version1001::identification_data($text);
        $intMaxMeasTs = 0; //Timestamp des akt. Datensatzes
        // echo "Messdatei erstellt: " . date("Y-m-d  H:i:s", $values['daily_file_date']) . "\nSchiffsname: " . $values['ship_name'] . "\n";
        $this->strImoNumber = $values['imo_nr'];
        /**
         * @var App\Repository\UsrWeb71\ShipTableRepository
         */
        $objShipRepository = $this->objDoctrineDefaultManager->getRepository(UsrWeb71ShipTable::class);
        $this->objShip = $objShipRepository->findByImoNumber($this->strImoNumber);
        // $this->objShip = Model_Ship::byImo($this->strImoNumber);
        $count += $values['engine_name_count'] + $values['hull_no_count'] + $values['ship_name_count'] + $values['serial_count'];
        fseek($this->resFileHandle, $count, SEEK_SET); //Dateizeiger hinter ##_END_IDENT setzen
        // echo $_SESSION['error_code'] . "\n";
        if ($version['ddc'] == '1001') {
            $objDmVersion = new Version1001($this->objContainer, $this->resFileHandle, $values);
        } else {
            $objDmVersion = new Version1101($this->objContainer, $this->resFileHandle, $values);
        }
        $intMaxMeasTs = $objDmVersion->process();

        // include 'send_alarmlist.php';
        $arrSession = $objDmVersion->getSession();
        if (isset($arrSession['ship_data'])) {
            $this->setLastMeasurementData($intMaxMeasTs, null, $arrSession['ship_data']->CDS_SerialNo);
        }

    }
}
