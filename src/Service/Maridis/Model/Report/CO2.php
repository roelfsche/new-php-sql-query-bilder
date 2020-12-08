<?php

namespace App\Service\Maridis\Model\Report;

use App\Entity\Marnoon\Voyagereport;
use App\Entity\UsrWeb71\ShipTable;
use App\Kohana\Arr;
use App\Service\Maridis\Model\Report;
use Doctrine\Common\Persistence\ManagerRegistry;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class CO2 extends Report
{
    /**
     * @var
     */
    public $objShip = null;

    /**
     * @var int
     */
    public $intFromTs = 0;

    /**
     * @var int
     */
    public $intToTs = 0;

    /**
     * enthält die Summen über einzelne Felder
     *
     * @var array
     */
    public $arrSum = null;

    /**
     * @var App\Repository\Marnoon\VoyagereportsRepository
     */
    public $objVoyageReportsRepository = null;

    public function __construct(ContainerInterface $objContainer, ManagerRegistry $objDoctrineRegistry, LoggerInterface $objLogger)
    {
        parent::__construct($objContainer, $objDoctrineRegistry, $objLogger);

        /** @var $this->objVoyageReportsRepository App\Repository\Marnoon\VoyagereportsRepository */
        $this->objVoyageReportsRepository = $objDoctrineRegistry
            ->getManager('marnoon')
            ->getRepository(Voyagereport::class);
    }

    /**
     * Daten-initialisierung dieses Services
     *
     * @param Model_Row_Ship $objShip
     * @param int            $intFromTs - Unix-TS: inkl. Intervalluntergrenze
     * @param int            $intToTs   - unix-ts: inkl. Intervallobergrenze
     */
    public function init(ShipTable $objShip, $intFromTs, $intToTs)
    {
        $this->objShip = $objShip;
        $this->intToTs = $intToTs;
        $this->intFromTs = $intFromTs;

        $this->objVoyageReportsRepository->init($objShip, $intFromTs, $intToTs);
        $this->arrSum = array('me' => array(), 'ae' => array(), 'boiler' => array());
    }

    /**
     * Diese Methode holt die DB-Relationen und berechnet gleich die notwendigen Summen/Daten.
     *
     * Sie liefert die Relationen zurück.
     *
     *
     */
    public function calculateData()
    {
        $arrRows = $this->objVoyageReportsRepository->retrieveAllForShip();
        $arrVoyages = array();

        if (count($arrRows)) {
            $arrVoyages = $this->compileVoyage($arrRows);
        }

        return $arrVoyages;
    }

    /**
     * da eine Reise von Hafen 1 zu Hafen 2 über mehrere Einträge geht, "gruppiere" ich sie hier zu einer.
     *
     * @param Voyagereport[] $arrVoyagereports
     * @return array
     */
    public function compileVoyage($arrVoyagereports, $boolTotal = false)
    {
        $arrRet = array();
        /** @var App\Entity\Marnoon\Voyagereport $objActRow */
        $objActRow = new Voyagereport();

        /** @var App\Entity\Marnoon\Voyagereport $objVoyagereport  */
        foreach ($arrVoyagereports as $objVoyagereport)
        {
            // initial-Behandlung
            if (!$objActRow->getId())
            {
                $objActRow = $objVoyagereport;
                /*
                 * Struktur:
                 * co2 => array(
                 *   'me' => array(
                 *     '3.1111114' => 322222
                 *     '3.00001' => 2323
                 *     ....
                 *   ),
                 *   'ae' => array(...)
                 */
                $objActRow->co2 = array('me' => array(), 'ae' => array(), 'boiler' => array());
                // merke mir die CO2-Daten
                $arrCo2 = $objActRow->co2;
                $this->addCo2Data($arrCo2, $objVoyagereport, 'me');
                $this->addCo2Data($arrCo2, $objVoyagereport, 'ae');
                $this->addCo2Data($arrCo2, $objVoyagereport, 'boiler');
                $objActRow->co2 = $arrCo2;
                // setze die Ende-Uhrzeit
            
                $objActRow->setVoyend($objVoyagereport->getVoyend());
                $objActRow->end_date = $objVoyagereport->getDate();
            }

            // wenn neue Reise...
            if ($objActRow->getVoyfrom() != $objVoyagereport->getVoyfrom() || $objActRow->getVoyto() != $objVoyagereport->getVoyto())
            {
                // .. dann Durchschnitte berechnden
                if ($objActRow->getId())
                {
                    // merken
                    $arrRet[] = $objActRow;
                }

                $objActRow = $objVoyagereport;
                $objActRow->co2 = array('me' => array(), 'ae' => array(), 'boiler' => array());
                // merke mir die CO2-Daten
                $arrCo2 = $objActRow->co2;
                $this->addCo2Data($arrCo2, $objVoyagereport, 'me');
                $this->addCo2Data($arrCo2, $objVoyagereport, 'ae');
                $this->addCo2Data($arrCo2, $objVoyagereport, 'boiler');
                $objActRow->co2 = $arrCo2;
                // setze die Ende-Uhrzeit
                $objActRow->setVoyend($objVoyagereport->getVoyend());
                $objActRow->end_date = $objVoyagereport->getDate();
            }
            else
            {
                // .. sonst Werte aufaddieren
                if ($objActRow->getId() != $objVoyagereport->getId())
                {
                    $objActRow->addTimeatsea($this->sanitizeValue($objVoyagereport->getTimeatsea(), 'time_at_sea'));
                    $objActRow->addTimeatriver($this->sanitizeValue($objVoyagereport->getTimeatriver(), 'time_at_river'));
                    $objActRow->addTimeatport($this->sanitizeValue($objVoyagereport->getTimeatport(), 'time_at_port'));
                    // Achtung: Muss ers overallSeamiles adden, dann seamiles und rivermeiles
                    // Hintergrund: siehe AddOverallSeamiles: added, wenn 0 die beiden und sind ja dann schon gesetzt... / verfälscht...
                    $objActRow->AddOverallSeaMiles($objVoyagereport->getOverallSeaMiles());//overall_sea_miles;
                    $objActRow->addSeamiles($this->sanitizeValue($objVoyagereport->getSeamiles(), 'miles_at_sea'));
                    $objActRow->addRivermiles($this->sanitizeValue($objVoyagereport->getRivermiles(), 'miles_at_river'));
                    $objActRow->addOverallFuelOilConsumption($objVoyagereport->getOverallFuelOilConsumption()); 
                    $objActRow->setCargototal(max($this->sanitizeValue($objActRow->getCargototal(), 'cargo_total'), $this->sanitizeValue($objVoyagereport->getCargototal(), 'cargo_total')));
                    // setze die Ende-Uhrzeit
                    $objActRow->setVoyend($objVoyagereport->getVoyend());
                    $objActRow->end_date = $objVoyagereport->getDate();
                    // merke mir die CO2-Daten
                    $arrCo2 = $objActRow->get('co2');
                    $this->addCo2Data($arrCo2, $objVoyagereport, 'me');
                    $this->addCo2Data($arrCo2, $objVoyagereport, 'ae');
                    $this->addCo2Data($arrCo2, $objVoyagereport, 'boiler');
                    $objActRow->co2 = $arrCo2;
                }
            }
        }

        // merken
        $arrRet[] = $objActRow;
        return $arrRet;
    }
        /**
     * füge hier dem internen array den entspr. Wert zu
     *
     * @param array                 $arrCo2         - das int. Array
     * @param Model_Row_Voyage_Data $objRow
     * @param string                $strMachineType - 'me', 'ae', 'boiler'
     */
    private function addCo2Data(&$arrCo2, $objRow, $strMachineType)
    {
        switch ($strMachineType)
        {
            case 'me':
                $floatFactor = $objRow->matchType($objRow->getMefueltype());
                $floatConsumption = $this->sanitizeValue($objRow->getMefueloilconsum(), 'me_fuel_oil_consumption');// / 1000; // t
                break;
            case 'ae':
                $floatFactor = $objRow->matchType($objRow->getAefueltype());
                $floatConsumption = $this->sanitizeValue($objRow->getAefueloilconsum(), 'ae_fuel_oil_consumption');// / 1000;
                break;
            case 'boiler':
                $floatFactor = $objRow->matchType($objRow->getBoilerfueltype());
                $floatConsumption = $this->sanitizeValue($objRow->getBoilerfuelconsum(), 'boiler_fuel_oil_consumption');// / 1000;
                break;
            default:
                return;
        }

        if (!$floatConsumption)
        {
            return;
        }
        $floatConsumption /= 1000; // kg --> t

        if ($floatFactor)
        {
            if (!Arr::path($arrCo2, $strMachineType . '+' . $floatFactor, NULL, '+'))
            {
                Arr::set_path($arrCo2, $strMachineType . '+' . $floatFactor, $floatConsumption, '+');
            }
            else
            {
                $arrCo2[$strMachineType][strval($floatFactor)] += $floatConsumption;
            }

            // nun die gesamtsumme
            if (!Arr::path($this->arrSum, $strMachineType . '+' . $floatFactor, NULL, '+'))
            {
                Arr::set_path($this->arrSum, $strMachineType . '+' . $floatFactor, $floatConsumption, '+');
            }
            else
            {
                $this->arrSum[$strMachineType][strval($floatFactor)] += $floatConsumption;
            }
        }
        return;
    }

    /**
     * Erstellt eine Summe, indem es jedes key/value - Pair miteinander multipliziert und dann alle aufaddiert
     * @param $arrArr
     * @return float
     */
    public function arraySum($arrArr)
    {
        $floatSum = 0;
        foreach ($arrArr as $floatIndex => $floatValue)
        {
            $floatSum += $floatIndex * $floatValue;
        }
        return $floatSum;
    }
}
