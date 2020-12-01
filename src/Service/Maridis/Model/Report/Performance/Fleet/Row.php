<?php

namespace App\Service\Maridis\Model\Report\Performance\Fleet;

use App\Entity\Marnoon\Voyagereport;
use App\Entity\UsrWeb71\ShipTable;
use App\Kohana\Arr;
use App\Service\Maridis\Model\Report;
use NilPortugues\Sql\QueryBuilder\Builder\GenericBuilder;
use Psr\Container\ContainerInterface;

class Row extends Report
{

    /**
     * Container
     *
     * @var Psr\Container\ContainerInterface
     */
    // p $objContainer;

    /**
     * @var App\Repository\Marnoon\VoyagereportsRepository
     */
    private $objVoyageReportsRepository = null;

    /**
     * @var App\Entity\UsrWeb71\ShipTable
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
     * hier werden die errechneten Werte drin gespeichert.
     *
     * @var array
     */
    public $arrValues = null;

    /**
     * Constructor
     *
     * @param App\Entity\UsrWeb71\ShipTable $objShip
     * @param int            $intFromTs - Unix-TS: inkl. Intervalluntergrenze
     * @param int            $intToTs   - unix-ts: inkl. Intervallobergrenze
     */
    public function __construct(ContainerInterface $objContainer, ShipTable $objShip, $intFromTs, $intToTs)
    {
        parent::__construct($objContainer, $objContainer->get('doctrine'));

        // $this->objContainer = $objContainer;
        $this->objShip = $objShip;
        $this->intFromTs = $intFromTs;
        $this->intToTs = $intToTs;

        /** @var $this->objVoyageReportsRepository App\Repository\Marnoon\VoyagereportsRepository */
        $this->objVoyageReportsRepository = $objContainer->get('doctrine')
            ->getManager('marnoon')
            ->getRepository(Voyagereport::class);

        $this->objVoyageReportsRepository->init($objShip, $intFromTs, $intToTs);

        $this->arrValues = array(
            'objShip' => $this->objShip,
            'last_date' => null,
            'foc_per_mile' => PHP_INT_MAX,
            'foc_per_mile_ranking' => 0,
            'eeoi' => PHP_INT_MAX,
            'eeoi_ranking' => 0,
            'sfoc' => PHP_INT_MAX,
            'sfoc_ranking' => 0,
            'foc_me' => 0,
            'foc_aux' => 0,
            'cyl_oil' => 0,
            'power' => 0,
            'cyl_oil_avg' => 0,
            'speed' => 0,
            'sea_miles' => 0,
            'sum_time_at_sea' => 0,
            'time_at_port' => 0,
        );
    }

    /**
     * Diese Methode berrechnet alle notwendigen Werte
     *
     * @param int $intCacheTime - Cachezeit in Sekunden; wenn 0, dann kein Caching
     *
     * @return array|NULL
     */
    public function calculateData($intCacheTime = 0)
    {
        // $strCacheKey = 'performance-row-' . $this->objShip->id . '-' . $this->intFromTs . '-' . $this->intToTs;
        // if ($intCacheTime) {
        //     $arrData = Cache::instance()->get($strCacheKey);
        //     if ($arrData) {
        //         return $arrData;
        //     }
        // } else {
        //     Cache::instance()->delete($strCacheKey);
        // }

        // EEOI - Berechnung; siehe https://redmine.lumturo.net/issues/263
        $arrVoyageReports = $this->objVoyageReportsRepository->retrieveAllForShip();

        if (count($arrVoyageReports)) {
            // if ($arrVoyageReports->count()) {

            $floatSumCounter = 0.0; // Zähler
            $floatSumDenominator = 0.0; // Nenner
            foreach ($arrVoyageReports as $objRow) {
                $floatMeFCo2 = (float) $objRow->matchType($objRow->getMefueltype());
                $floatMeFuelOilConsumption = $this->sanitizeValue($objRow->getMefueloilconsum(), 'me_fuel_oil_consumption');
                $floatAeFCo2 = (float) $objRow->matchType($objRow->getAefueltype());
                $floatAeFuelOilConsumption = $this->sanitizeValue($objRow->getAefueloilconsum(), 'ae_fuel_oil_consumption');
                $floatBoilerFCo2 = (float) $objRow->matchType($objRow->getBoilerfueltype());
                $floatBoilerOilConsumption = $this->sanitizeValue($objRow->getBoilerfuelconsum(), 'boiler_fuel_oil_consumption');

                $floatSumCounter += $floatMeFCo2 * $floatMeFuelOilConsumption * 1000 + $floatAeFCo2 * $floatAeFuelOilConsumption * 1000 + $floatBoilerFCo2 * $floatBoilerOilConsumption * 1000;
                $floatSumDenominator += $this->sanitizeValue($objRow->getCargototal(), 'cargo_total') * $this->sanitizeValue($objRow->getSeamiles(), 'miles_at_sea');
            }

            $floatSumDenominator = max($floatSumDenominator, 1);
            // durch sanitizing kann sein, dass 0
            if ($floatSumDenominator) {
                $this->arrValues['eeoi'] = $floatSumCounter / $floatSumDenominator;
            } else {
                // invalid..
                $this->arrValues['eeoi'] = -1; // wird dann gleich noch angepasst
            }

            if ($this->arrValues['eeoi'] < 1 || $this->arrValues['eeoi'] > 999) {
                $this->arrValues['eeoi'] = PHP_INT_MAX; // wegen sortierung: bei Ausgabe dann n.a.
            }

            foreach ([
                [
                    'field_expression' => [
                        ['SUM(a.mefueloilconsum)', 'foc_me'],
                    ],
                    'constraints' => [
                        'a.mefueloilconsum' => 'me_max_fuel_oil_consumption',
                    ],
                ],
                [
                    'field_expression' => [
                        ['MAX(a.date)', 'last_date'],
                    ],
                    'constraints' => [],
                ],
                [
                    'field_expression' => [
                        ['SUM(a.timeatsea)', 'sum_time_at_sea'],
                    ],
                    'constraints' => [
                        'a.timeatsea' => 'time_at_sea',
                    ],
                ],
                [
                    'field_expression' => [
                        ['SUM(a.seamiles)', 'sea_miles'],
                    ],
                    'constraints' => [
                        'a.seamiles' => 'miles_at_sea',
                    ],
                ],
                // [
                //     'field_expression' => [
                //         ['SUM(a.aefueloilconsum + a.boilerfuelconsum)', 'foc_aux'],
                //     ],
                //     'constraints' => [
                //         'a.timeatsea' => 'miles_sea',
                //     ],
                // ],
            ] as $arrQueryConfig) {
                $arrData = $this->objVoyageReportsRepository->retrieveVoyageValues($arrQueryConfig);
                if (!$arrData) {
                    continue;
                }

                foreach ($arrData as $strIndex => $mixedValue) {
                    // if (strpos($strIndex, '_')) {
                    $this->arrValues[$strIndex] = $mixedValue;
                    // Arr::set_path($this->arrValues, $strIndex, $mixedValue, '__');
                    // }
                }
            }

            foreach ([
                'speed' => [
                    'columns' => [
                        'inner' => [
                            ['func' => 'SUM', 'arguments' => ['TimeAtSea'], 'alias' => 'tas'],
                            ['func' => 'SUM', 'arguments' => ['TimeAtSea*SpeedThroughWater'], 'alias' => 'speed'],
                        ],
                        'outer' => [
                            ['func' => 'speed / tas', 'arguments' => [], 'alias' => 'speed'],
                        ],
                    ],
                    'constraints' => [
                        'TimeAtSea' => 'time_at_sea',
                    ],
                ],
                // doppelt; siehe unten
                'power' => [
                    'columns' => [
                        'inner' => [
                            ['func' => 'SUM', 'arguments' => ['TimeAtSea*MEPowerAvg'], 'alias' => 'mepa'],
                            ['func' => 'SUM', 'arguments' => ['TimeAtSea'], 'alias' => 'tas'],
                        ],
                        'outer' => [
                            ['func' => 'mepa / tas', 'arguments' => [], 'alias' => 'power'],
                        ],
                    ],
                    'constraints' => [
                        'TimeAtSea' => 'time_at_sea',
                        'MEPowerAvg' => 'me_power_avg',
                    ],
                ],
                // doppelt; siehe unten
                'sfoc' => [
                    'columns' => [
                        'inner' => [
                            ['func' => 'SUM', 'arguments' => ['TimeAtSea*MESFOC'], 'alias' => 'sfoc'],
                            ['func' => 'SUM', 'arguments' => ['TimeAtSea'], 'alias' => 'tas'],
                        ],
                        'outer' => [
                            ['func' => 'sfoc / tas', 'arguments' => [], 'alias' => 'sfoc'],
                        ],
                    ],
                    'constraints' => [
                        'TimeAtSea' => 'time_at_sea',
                        'MESFOC' => 'me_sfoc',
                    ],
                ],
                'cyl_oil_avg' => [
                    'columns' => [
                        'inner' => [
                            ['func' => 'SUM', 'arguments' => ['TimeAtSea*MECylOilConsum'], 'alias' => 'coa'],
                            ['func' => 'SUM', 'arguments' => ['TimeAtSea'], 'alias' => 'tas'],
                        ],
                        'outer' => [
                            ['func' => 'coa / tas', 'arguments' => [], 'alias' => 'cyl_oil_avg'],
                        ],
                    ],
                    'constraints' => [
                        'TimeAtSea' => 'time_at_sea',
                        'MECylOilConsum' => 'me_specific_cylinder_oil_consumption',
                    ],
                ],
            ] as $strKey => $arrQueryConfig) {
                $this->arrValues[$strKey] = (float) $this->retrieveVoyageValueFromCascadeSql($objRow, $arrQueryConfig);
            }

            // $this->arrValues['foc_aux'] = (float) $this->getDbAggregateWithinInterval(DB::expr('AEFuelOilConsum + BoilerFuelConsum'), Db::expr('SUM(foc_aux)'), 'foc_aux', 1000, 15000);
            // Time at Port
            // $this->arrValues['time_at_port'] = $this->getDbValueWithinInterval(DB::expr('SUM(TimeAtPort)'), 'TimeAtPort', 0, 24);
            foreach ([
                [
                    //TimeAtPort 
                    'field_expression' => [
                        ['func' => 'SUM', 'arguments' => ['TimeAtPort'], 'time_at_port'],
                    ],
                    'constraints' => [
                        'TimeAtPort' => 'time_at_port',
                    ],
                ],
                // [
                //     'field_expression' => [
                //         ['func' => 'SUM', 'arguments' => ['AEFuelOilConsum + BoilerFuelConsum'], 'alias' => 'foc_aux'],
                //     ],
                //     'having' => [
                //         'foc_aux' => 'foc_aux',
                //     ],
                // ],
                [
                    // FOC ME per Mile
                    'field_expression' => [
                        ['func' => 'ROUND', 'arguments' => ['SUM(MEFuelOilConsum) / SUM(TimeAtSea)', 1], 'foc_per_mile'],
                    ],
                    'constraints' => [
                        'MEFuelOilConsum' => 'me_fuel_oil_consumption',
                        'TimeAtSea' => 'time_at_sea',
                    ],
                    'defaults' => [
                        'foc_per_mile' => PHP_INT_MAX,
                    ],
                ],
                // Speed
                [
                    'field_expression' => [
                        // func ändert nix, so kann ich das statemetn aber fehlerfrei bauen..
                        ['func' => 'ABS', 'arguments' => ['SUM(TimeAtSea * SpeedThroughWater) / SUM(TimeAtSea)'], 'speed'],
                    ],
                    'constraints' => [
                        'SpeedThroughWater' => 'speed_through_water',
                        'TimeAtSea' => 'time_at_sea',
                    ],
                ],
                // Cyl Oil
                [
                    'field_expression' => [
                        ['func' => 'SUM', 'arguments' => ['MECylOilInput'], 'cyl_oil'],
                    ],
                    'constraints' => ['MECylOilInput' => 'me_cyl_oil_input'],
                    'defaults' => [
                        'cyl_oil' => PHP_INT_MAX,
                    ],
                ],
                // Power
                array(
                    'field_expression' => array(
                        array('func' => 'ABS', 'args' => ['SUM(TimeAtSea * MEPowerAvg) / SUM(TimeAtSea)'], 'power'),
                    ),
                    'constraints' => array(
                        'MEPowerAvg' => 'me_power_avg',
                        'TimeAtSea' => 'time_at_sea',
                    ),
                ),
                // waren doch doppelt?
                // checken, ob Ergebnis gleich
                //SFOC
                array(
                    'field_expression' => array(
                        array('func' => 'ABS', 'args' => ['SUM(TimeAtSea * MESFOC) / SUM(TimeAtSea)'], 'sfoc'),
                    ),
                    'constraints' => array(
                        'MESFOC' => 'me_specific_fuel_oil_consumption', // geändert: oben waren die Grenzen  170/280; nehme jetzt die aus der config (150/350)
                        'TimeAtSea' => 'time_at_sea',
                    ),
                    'defaults' => array('sfoc' => PHP_INT_MAX),
                ),
            ] as $arrQueryConfig) {
                $arrRow = $this->objVoyageReportsRepository->retrieveVoyageValuesBySql($arrQueryConfig);
                // $arrRow = $this->doVoyageReportQuery($arrQueryConfig);
                if ($arrRow) {
                    // ist zwar immer nur ein Feld, mache trotzdem Schleife (war vorher auch so ;-); komme sonst nicht so leicht an den Feld-Alias)
                    foreach ($arrRow as $strIndex => $mixedValue) {
                        Arr::set_path($this->arrValues, $strIndex, $mixedValue);
                    }
                }
            }

        }

        // if ($intCacheTime) {
        //     Cache::instance()->set($strCacheKey, $this->arrValues, $intCacheTime);
        // }
        return $this->arrValues;
    }

    public function retrieveVoyageValueFromCascadeSql($objRow, $arrQueryConfig)
    {
        $objBuilder = new GenericBuilder();
        $objInnerQuery = $objBuilder->select('voyagereport')
            ->where()
            ->equals('IMO', $objRow->getImo())
        // ->greaterThan('TimeAtSea', 0)
        // ->lessThan('TimeAtSea', 25)
            ->greaterThanOrEqual('date', date('Y-m-d', $this->intFromTs))
            ->lessThanOrEqual('date', date('Y-m-d', $this->intToTs))
            ->end();
        $objInnerQuery->setColumns([]);
        foreach ($arrQueryConfig['columns']['inner'] as $arrColumnConfig) {
            $objInnerQuery->setFunctionAsColumn($arrColumnConfig['func'], $arrColumnConfig['arguments'], $arrColumnConfig['alias']);
        }

        $objInnerQuery = $this->objVoyageReportsRepository->addConstraintsToSqlQuery($objInnerQuery, $arrQueryConfig['constraints']);
        $strInnerQuery = $objBuilder->write($objInnerQuery);
        $arrParams = $objBuilder->getValues();

        $objOuterQuery = $objBuilder->select('innerTable');
        $objOuterQuery->setColumns([]);

        foreach ($arrQueryConfig['columns']['outer'] as $arrColumnConfig) {
            $objOuterQuery->setFunctionAsColumn($arrColumnConfig['func'], $arrColumnConfig['arguments'], $arrColumnConfig['alias']);
        }

        $strOuterQuery = $objBuilder->write($objOuterQuery);
        $strSql = str_replace('innerTable', '(' . $strInnerQuery . ') as innerTable', $strOuterQuery);
        $arrRows = $this->objVoyageReportsRepository->findByNativeSql($strSql, $arrParams, false, '');
        return array_shift($arrRows[0]); // liefert 1. Wert d. 1.Row
        // return;
    }
}
