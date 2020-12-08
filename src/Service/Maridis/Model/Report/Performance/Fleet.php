<?php
namespace App\Service\Maridis\Model\Report\Performance;

use App\Entity\Marnoon\Voyagereport;
use App\Kohana\Arr;
use App\Service\Maridis\Model\Report;
use App\Service\Maridis\Model\Report\Performance\Fleet\Row;
use Doctrine\Common\Persistence\ManagerRegistry;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Generiert die Daten für das Performance-Ranking
 *
 * (c)  rolf.staege@lumturo.net
 *
 * @copyright    Copyright (c) 2015 rolf.staege@lumturo.net
 */
class Fleet extends Report
{
    // speicher für usort-Funktion
    public static $strSortKey;
    public static $intSortDir;
    /**
     * @var array
     */
    public $arrShips;

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

    public function __construct(ContainerInterface $objContainer, ManagerRegistry $objDoctrineRegistry, LoggerInterface $objLogger)
    {
        parent::__construct($objContainer, $objDoctrineRegistry, $objLogger);
        
        /** @var $this->objVoyageReportsRepository App\Repository\Marnoon\VoyagereportsRepository */
        $this->objVoyageReportsRepository = $objDoctrineRegistry
            ->getManager('marnoon')
            ->getRepository(Voyagereport::class);
    }
    /**
     * @param array $arrShips - von Model_Row_Ship
     * @param int              $intFromTs         - Unix-TS: inkl. Intervalluntergrenze
     * @param int              $intToTs           - unix-ts: inkl. Intervallobergrenze
     */
    public function init($arrShips, $intFromTs, $intToTs)
    {
        $this->arrShips = $arrShips;
        $this->intFromTs = $intFromTs;
        $this->intToTs = $intToTs;
    }

    /**
     * Diese Methode holt für jedes Schiff die Werte als Array und gibt sie in einem Array dann sortiert zurück.
     *
     * @param string  $strSortKey   - der Array-Index, nach dem sortiert wird: 'eeoi', 'sfoc', 'foc_me', 'foc_aux', 'power', 'speed', 'sea_miles'
     * @param string  $strSortDir   - 'asc'|'desc'
     * @param integer $intCacheTime - Cachezeit für die Zeilen des Arrays in sek; wenn 0, dann kein Caching
     *
     * @return array
     */
    public function calculateData($strSortKey = 'eeoi', $strSortDir = 'asc', $intCacheTime = 0)
    {
        // if ($intCacheTime) {
        //     $strCacheKey = implode('-', array(Model_Ship::generateHash($this->arrShips), $this->intFromTs, $this->intToTs, $strSortKey, $strSortDir));
        //     $arrCached = Cache::instance()->get($strCacheKey);
        //     if ($arrCached) {
        //         return $arrCached;
        //     }
        // }

        $arrData = array();
        foreach ($this->arrShips as $objShip) {
            $objDataRow = new Row($this->objContainer, $objShip, $this->intFromTs, $this->intToTs, $this->objLogger);
            $arrRow = $objDataRow->calculateData($intCacheTime);
            if ($arrRow) {
                $arrData[] = $arrRow;
            }
        }

        // errechne nun die Rankings für den PDF-Report
        $arrData = $this->calculateRanking($arrData, 'foc_per_mile', 'foc_per_mile_ranking');
        $arrData = $this->calculateRanking($arrData, 'eeoi', 'eeoi_ranking');
        $arrData = $this->calculateRanking($arrData, 'sfoc', 'sfoc_ranking');

        $arrData = $this->sort($arrData, $strSortKey, $strSortDir);

        // if ($intCacheTime) {
        //     Cache::instance()->set($strCacheKey, $arrData);
        // }
        return $arrData;
    }

    /**
     * Diese Methode sortiert das Array nach einem Key und übertragt den Index (= das Ranking) danach in das entsprechende Feld innerhalb der Array-Zeilen
     *
     * @param array  $arrData
     * @param string $strKey          - key, nach dem sortiert wird
     * @param        $strTargetColumn - key, in dessen Feld dann das Ranking geschrieben wird
     * @return array
     */
    private function calculateRanking($arrData, $strKey, $strTargetColumn)
    {

        self::$intSortDir = -1; // asc
        self::$strSortKey = $strKey;

        usort($arrData, function ($arrA, $arrB) {
            if ($arrA[self::$strSortKey] < $arrB[self::$strSortKey]) {
                return self::$intSortDir;
            } elseif ($arrA[self::$strSortKey] > $arrB[self::$strSortKey]) {
                return self::$intSortDir * -1;
            } else {
                return 0;
            }
        });

        foreach ($arrData as $intIndex => $arrRow) {
            $arrData[$intIndex][$strTargetColumn] = $intIndex + 1; // Index beginnt bei 1
        }

        return $arrData;
    }

    /**
     * Sortiert das Array
     *
     * @param        $arrData
     * @param string $strSortKey - 'eeoi', 'sfoc', 'foc_me', 'foc_aux', 'power', 'speed', 'sea_miles'
     * @param string $strSortDir - 'asc', 'desc'
     *
     * @return array
     */
    public function sort($arrData, $strSortKey = 'eeoi', $strSortDir = 'asc')
    {
        self::$intSortDir = (strtolower($strSortDir) == 'asc') ? -1 : 1;
        self::$strSortKey = $strSortKey;

        usort($arrData, function ($arrA, $arrB) {
            switch (self::$strSortKey) {
                case 'actual_name':
                    $mixedValA = $arrA['objShip']->getAktName();
                    $mixedValB = $arrB['objShip']->getAktName();
                    break;
                case 'imo_number':
                    $mixedValA = $arrA['objShip']->getImoNo();
                    $mixedValB = $arrB['objShip']->getImoNo();
                    break;
                case 'last_date':
                    $mixedValA = strtotime($arrA['last_date']);
                    $mixedValB = strtotime($arrB['last_date']);
                    break;
                case 'sfoc':
                case 'foc_me':
                case 'foc_aux':
                case 'power':
                case 'speed':
                case 'sea_miles':
                    $mixedValA = $arrA[self::$strSortKey];
                    $mixedValB = $arrB[self::$strSortKey];
                    break;
                default:
                    $mixedValA = $arrA['eeoi'];
                    $mixedValB = $arrB['eeoi'];
            }

            if ($mixedValA < $mixedValB) {
                return self::$intSortDir;
            } elseif ($mixedValA > $mixedValB) {
                return self::$intSortDir * -1;
            } else {
                return 0;
            }
        });

        return $arrData;
    }
}
