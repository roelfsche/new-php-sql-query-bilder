<?php

namespace App\Service\Maridis\Model\Report;

use App\Entity\BaseEntity;
use App\Entity\Marprime\EngineParams;
use App\Entity\Marprime\MeasurementParams;
use App\Entity\Marprime\MpdAeCurveData;
use App\Entity\Marprime\MpdMeasurementData;
use App\Entity\Marprime\Report as MarprimeReport;
use App\Entity\UsrWeb71\ShipTable;
use App\Exception\MscException;
use App\Kohana\Arr;
use App\Service\Maridis\Model\Report;
use Doctrine\Common\Persistence\ManagerRegistry;

class Engine extends Report
{

    /**
     * @var ShipTable
     */
    private $objShip = null;
    /**
     * @var stdObj
     */
    private $objEngineParams = null;

    /**
     * @var Jelly_Collection von Model_Row_Mpd_History
     */
    public $objHistoryCollection = null;

    /**
     * bei der Berechnung der Werte errechne ich auch ein paar neue, die hängen dann hier mit drinne
     * bspw. 'rel_speed'
     * @var array
     */
    public $arrCalculatedHistoryCollection = null;

    /**
     * @var int
     */
    public $intDateTs = 0;

    /**
     * enthält die Summen über
     * @var array
     */
    public $arrStatistic = null;

    /**
     * enthält die Remarks
     * Struktur:
     *
     * @var array
     */
    public $arrRemarks = null;

    protected $objEngineParamsRepository = null;
    protected $objMpdMeasurementDataRepository = null;
    protected $objMpdAeCurveDataDataRepository = null;
    protected $objMeasurementParamsRepository = null;
    protected $objReportRepository = null;

    public function __construct(ManagerRegistry $objDoctrineRegistry)
    {
        parent::__construct($objDoctrineRegistry);

        $this->objEngineParamsRepository = $objDoctrineRegistry
            ->getManager('marprime')
            ->getRepository(EngineParams::class);

        $this->objMpdMeasurementDataRepository = $objDoctrineRegistry
            ->getManager('marprime')
            ->getRepository(MpdMeasurementData::class);

        $this->objMpdAeCurveDataRepository = $objDoctrineRegistry
            ->getManager('marprime')
            ->getRepository(MpdAeCurveData::class);

        $this->objMeasurementParamsRepository = $objDoctrineRegistry
            ->getManager('marprime')
            ->getRepository(MeasurementParams::class);

        $this->objReportRepository = $objDoctrineRegistry
            ->getManager('marprime')
            ->getRepository(MarprimeReport::class);
    }

    /**
     * @param Model_Row_Ship $objShip
     * @param stdObject          $objEngineParams - Zeile aus Tabelle engine_params
     * @param                $intCreateTs     - Speicher-TS in der DB; wenn leer, dann wird der aus den objEngineParams genommen
     *                                        - unix_ts; enthält typischerweise Tag 00:00:00; schaue hier dann selbst, wann die letzte Messung an dem Tag war
     * @throws Msc_Exception - wenn keine Daten in der DB vorhanden
     */
    public function init(ShipTable $objShip, $objEngineParams, $intCreateTs = null)
    {
        // $objEngineParamsRepo = $this->objDoctrineRegistry
        $this->objShip = $objShip;
        $this->objEngineParams = $objEngineParams;
        if (!$intCreateTs) {
            $this->intDateTs = strtotime($objEngineParams->date);
        } else {
            // hole den max ts dieses Tages
            $this->intDateTs = $this->objEngineParamsRepository->getLastMeasurementTs($this->objShip->getMarprimeSerialno(), $objEngineParams, $intCreateTs);
        }
        if (!$this->intDateTs) {
            throw new MscException('Keine Daten für den angegebenen Zeitraum > :date', array(
                ':date' => date('Y-m-d H:i:s', $intCreateTs),
            ));
        }

        $this->arrStatistic = array(
            'total' => array(
                'indicated_power' => 0,
                'effective_power' => 0,
                'generator_power' => 0,
            ),
            'avg' => array(
                'speed' => 0,
                'rel_speed' => 0,
                'p_comp' => 0,
                'p_max' => 0,
                'angle_pmax' => 0,
                'pcomp_rel_pscav' => 0,
                'pmax-pcomp' => 0,
                'mip' => 0,
                'ind_power' => 0,
                'eff_power' => 0,
                'gen_power' => 0,
                'load' => 0,
                'leakage' => 0,
            ),
            'min' => array(
                'speed' => PHP_INT_MAX,
                'rel_speed' => PHP_INT_MAX,
                'p_comp' => PHP_INT_MAX,
                'p_max' => PHP_INT_MAX,
                'angle_pmax' => PHP_INT_MAX,
                'pcomp_rel_pscav' => PHP_INT_MAX,
                'pmax-pcomp' => PHP_INT_MAX,
                'mip' => PHP_INT_MAX,
                'ind_power' => PHP_INT_MAX,
                'eff_power' => PHP_INT_MAX,
                'gen_power' => PHP_INT_MAX,
                'load' => PHP_INT_MAX,
                'leakage' => PHP_INT_MAX,
            ),
            'max' => array(
                'speed' => 0,
                'rel_speed' => 0,
                'p_comp' => 0,
                'p_max' => 0,
                'angle_pmax' => 0,
                'pcomp_rel_pscav' => 0,
                'pmax-pcomp' => 0,
                'mip' => 0,
                'ind_power' => 0,
                'eff_power' => 0,
                'gen_power' => 0,
                'load' => 0,
                'leakage' => 0,
            ),
            'diff' => array(
                'speed' => 0,
                'rel_speed' => 0,
                'p_comp' => 0,
                'p_max' => 0,
                'angle_pmax' => 0,
                'pcomp_rel_pscav' => 0,
                'pmax-pcomp' => 0,
                'mip' => 0,
                'ind_power' => 0,
                'eff_power' => 0,
                'gen_power' => 0,
                'load' => 0,
                'leakage' => 0,
            ),
            'dev' => array(
                'speed' => 0,
                'rel_speed' => 0,
                'p_comp' => 0,
                'p_max' => 0,
                'angle_pmax' => 0,
                'pcomp_rel_pscav' => 0,
                'pmax-pcomp' => 0,
                'mip' => 0,
                'ind_power' => 0,
                'eff_power' => 0,
                'gen_power' => 0,
                'load' => 0,
                'leakage' => 0,
            ),
        );
    }

    /**
     * Diese Methode berechnet die notwendigen Daten für die Tabelle 1 und 2
     *
     * Hab nachträglich leakage eingefügt, da das aus Model_Row_Data heraus berechnet werden kann.
     * (brauche ich für Dashboard --> pressure-Widget Pressure.php)
     *
     */
    public function calculateData()
    {
        # statistic overview
        $this->arrCalculatedHistoryCollection = array();

        $this->arrHistory = $this->objMpdMeasurementDataRepository->getBySerialNumberAndDate($this->objShip->getMarprimeSerialno(), date(BaseEntity::strDateFormat, $this->intDateTs), $this->objEngineParams->strokes);

        $intCount = count($this->arrHistory);
        $floatLoadBalanceAvg = $this->objMpdMeasurementDataRepository->retrieveLoadAvg();
        if ($intCount) {
            // %Speed: 100% wird durch engineParams->speed definiert
            $floatFactor = 100 / (float) $this->objEngineParams->speed;
            // Berechne total/min/max
            foreach ($this->arrHistory as $objMpdHistory) {
//                $floatMechEfficiency
                # Werte berechnen, die nicht in der DB stehen
                // %Speed
                $objMpdHistory->set('rel_speed', $floatFactor * $objMpdHistory->revolution);
                // effective Power
                //                $objMpdHistory->set('effective_power', $objMpdHistory->ind_power * $floatMechEfficiency);
                // generator power
                //                $objMpdHistory->set('generator_power', $objMpdHistory->get('effective_power') * $floatCos94);
                // Load
                //                $objMpdHistory->set('load', $objMpdHistory->get('effective_power') * 100 / $this->objEngineParams->power);
                //                $objResult = Db::select(array(Db::expr('(ind_power - ' . $floatAvgPower . ') / ' . $floatAvgPower . ' * 100'), 'load_balance'), 'measurement_num')->from('mpd_measurement_data')
                // siehe email hans 30.09.
                //                $objMpdHistory->set('load', ($objMpdHistory->ind_power - $floatLoadBalanceAvg) / $floatLoadBalanceAvg * 100);
                // siehe email von Hans vom 13.02.18
                $objMpdHistory->set('load', 100 * $objMpdHistory->ind_power / ((float) $this->objEngineParams->power / $this->objEngineParams->cyl_count));

                # total
                $this->arrStatistic['total']['indicated_power'] += $objMpdHistory->ind_power;

                # avg
                foreach (array(
                    'revolution' => 'speed',
                    'rel_speed' => 'rel_speed',
                    'comp_pressure' => 'p_comp',
                    'max_pressure' => 'p_max',
                    'apmax' => 'angle_pmax',
                    'pcomp_rel_pscav' => 'pcomp_rel_pscav',
                    'mean_ind_pressure' => 'mip',
                    'ind_power' => 'ind_power',
                    'load' => 'load',
                    'pmax_pcomp' => 'pmax-pcomp',
                    'leakage' => 'leakage',
                ) as $strDbKey => $strArrKey) {
                    $this->arrStatistic['avg'][$strArrKey] += (float) $objMpdHistory->get($strDbKey);
                }
                // min/max
                foreach (array(
                    'revolution' => 'speed',
                    'rel_speed' => 'rel_speed',
                    'comp_pressure' => 'p_comp',
                    'max_pressure' => 'p_max',
                    'apmax' => 'angle_pmax',
                    'pcomp_rel_pscav' => 'pcomp_rel_pscav',
                    'mean_ind_pressure' => 'mip',
                    'ind_power' => 'ind_power',
//                             'effective_power' => 'eff_power',
                    //                             'generator_power' => 'gen_power',
                    'load' => 'load',
                    'pmax_pcomp' => 'pmax-pcomp',
                    'leakage' => 'leakage',
                ) as $strDbKey => $strArrKey) {
                    $this->arrStatistic['min'][$strArrKey] = min((float) $objMpdHistory->get($strDbKey), $this->arrStatistic['min'][$strArrKey]);
                    $this->arrStatistic['max'][$strArrKey] = max((float) $objMpdHistory->get($strDbKey), $this->arrStatistic['max'][$strArrKey]);
                }

                // merke mir die Zeile mit den zusätzlich errechneten Daten
                $this->arrCalculatedHistoryCollection[] = $objMpdHistory;
            }

            // avg: / anz
            foreach ($this->arrStatistic['avg'] as $strKey => $floatValue) {
                $this->arrStatistic['avg'][$strKey] /= $intCount;
            }
            //Difference und Deviation:Diff * 100 / Avg (proz. Abweichung)
            foreach ($this->arrStatistic['max'] as $strKey => $floatMaxValue) {
                if ($strKey == 'angle_pmax') {
                    continue;
                }
                // Difference
                $this->arrStatistic['diff'][$strKey] = $floatMaxValue - $this->arrStatistic['min'][$strKey];
                // Deviation
                if ($this->arrStatistic['avg'][$strKey]) {
                    $this->arrStatistic['dev'][$strKey] = $this->arrStatistic['diff'][$strKey] * 100 / $this->arrStatistic['avg'][$strKey];
                } else {
                    // kriege bei Mathilde Oldendorf (264) für 2015-04-16 div / zero exception
                    $this->arrStatistic['dev'][$strKey] = 0;
                }
            }
        }

        $this->arrStatistic = $this->resetInfValues($this->arrStatistic);
    }

    public function createRemarks()
    {
        $this->createRemarksNumberOfCylinders($this->arrRemarks);
        $this->createRemarksSpeedFluctuation($this->arrRemarks);
        $this->createRemarksOtCorrection($this->arrRemarks);
        $this->createRemarksUltraSonicSignal($this->arrRemarks);
        $this->createRemarksLoadTuning($this->arrRemarks);
        $this->createRemarksCompressionPressure($this->arrRemarks);
        $this->createRemarksMaxPressure($this->arrRemarks);
        $this->createRemarksMeanIndicatedPressure($this->arrRemarks);
        $this->createRemarksLeakage($this->arrRemarks);
        $this->createRemarksReportCreated($this->arrRemarks);
        return $this->arrRemarks;
    }

    /**
     * Diese Methode errechnet die Daten für die Remarks.
     */
    public function createRemarksNumberOfCylinders(&$arrRemarks)
    {
        // Measurement
        // Anzahl der gemessenen Zylinder
        $intRefCountCylinder = $this->objEngineParams->cyl_count;
        $intCountCylinder = $this->objMpdMeasurementDataRepository->retrieveMeasurementCount();

        if ($intCountCylinder + 1 == $intRefCountCylinder) {
            $arrRemarks[] = array(
                'priority' => 'green',
                'concern' => 'Measurement',
                'event' => 'Cylinder measured',
                'cylinder' => 'All',
                'remark' => '---',
            );
        } elseif ($intCountCylinder > $intRefCountCylinder) {
            $arrRemarks[] = array(
                'priority' => 'red',
                'concern' => 'Measurement',
                'event' => 'Cylinders measured',
                'cylinder' => 'too many',
                'remark' => 'Too many cylinders were measured. Make sure that you measured only one engine at a
time and each cylinder only once. Please flash your handheld device manually and measure again.',
            );
        } else {
            // zu wenige, rauskriegen, welche
            $arrRef = range(0, $intRefCountCylinder);

            $objResult = $this->objMpdMeasurementDataRepository->retrieveDistinctMeasurementNums();

            foreach ($objResult as $arrMpdData) {
                if (isset($arrRef[$arrMpdData['cyl_no']])) {
                    unset($arrRef[$arrMpdData['cyl_no']]);
                }
            }
            if (isset($arrRef[0])) {
                unset($arrRef[0]);
            }

            $arrRemarks[] = array(
                'priority' => 'yellow',
                'concern' => 'Measurement',
                'event' => 'Cylinders not measured',
                'cylinder' => implode(', ', $arrRef),
                'remark' => 'Please be sure that you measured the correct number of cylinders.',
            );
        }

        return $this->arrRemarks;
    }

    /**
     * Diese Methode errechnet die Remarks für die Drehzahlschwankung.
     *
     * Mittelwert = AVG(revolution) where cyl_count = 0 (gibt für jeden Zylinder 1 Eintrag = Mittelwert für den Zylinder)
     * Dann min/max für jede cyl_no
     * Wenn die < avg-3 oder > avg+3, dann Abweichung zu gross
     *
     * Anmerkung von René: Bei dieser Messung SOLL gegen jeden einzelnen Messwert gecheckt werden.
     * D.h. Selektion where cyl_no > 0
     * Bei allen anderen Remarks wird immer gegen den Zylinderdurchschnitt gecheckt (where cyl_no = 0)
     *
     * @param $arrRemarkds
     *
     */
    public function createRemarksSpeedFluctuation(&$arrRemarks)
    {
        $intMax = 3;
        $floatAvgRevolution = $this->objMpdMeasurementDataRepository->retrieveRevolutionAvg();

        $objStatement = $this->objMpdMeasurementDataRepository->retrieveMinMaxRevolution(null, null, 3, $floatAvgRevolution);

        if (!$objStatement->rowCount()) {
            $arrRemarks[] = array(
                'priority' => 'green',
                'concern' => 'Measurement',
                'event' => 'Speed variation',
                'cylinder' => 'All',
                'remark' => 'Speed variation for measurement was within range',
            );
        } else {
            $arrCylinders = array();

            $arrCylinders = Arr::pluck($objStatement->fetchAll(), 'cyl_no');
            $arrRemarks[] = array(
                'priority' => 'yellow',
                'concern' => 'Measurement',
                'event' => 'Speed variation',
                'cylinder' => implode(', ', $arrCylinders),
                'remark' => 'Speed variation is too high. Please measure again at a constant engine load point. Therefore
please avoid heavy sea and rudder movement when measuring.',
            );
        }

    }

    /**
     * Diese Methode errechnet die Remarks für die OT-Korrektur.
     *
     * Wenn Fehler, dann Bit4 in calc_fail gesetzt.
     *
     * @param $arrRemarkds
     *
     */
    public function createRemarksOtCorrection(&$arrRemarks)
    {
        $objStatement = $this->objMpdMeasurementDataRepository->retrieveOtCorrection();

        if (!$objStatement->rowCount()) {
            $arrRemarks[] = array(
                'priority' => 'green',
                'concern' => 'Measurement',
                'event' => 'TDC correction',
                'cylinder' => 'All',
                'remark' => 'Dynamic TDC-correction was done without errors.',
            );
        } else {
            $arrCylinders = Arr::pluck($objStatement->fetchAll(), 'cyl_no');
            // foreach ($objResult->fetchAll() as $arrRow) {
            //     $arrCylinders[$arrRow->cyl_no] = $objRow->cyl_no;
            // }
            $arrRemarks[] = array(
                'priority' => 'red',
                'concern' => 'Measurement',
                'event' => 'TDC correction',
                'cylinder' => implode(', ', $arrCylinders),
                'remark' => 'TDC correction failed. Please check your engine configuration, the scavenge air pressure
value you entered and measure again.',
            );
        }

    }

    /**
     * Diese Methode errechnet die Remarks für den Ultraschall.
     *
     *
     * @param $arrRemarkds
     *
     */
    public function createRemarksUltraSonicSignal(&$arrRemarks)
    {
        $intRefCountCylinder = $this->objEngineParams->cyl_count;
        $objStatement = $this->objMpdAeCurveDataRepository->retrieveRevolutionAvg($this->objShip->getMarprimeSerialno(), date(BaseEntity::strDateFormat, $this->intDateTs));

        $intCountCylinder = $objStatement->rowCount();

        if ($intCountCylinder == $intRefCountCylinder) {
            $arrRemarks[] = array(
                'priority' => 'green',
                'concern' => 'Measurement',
                'event' => 'Ultrasonic sound signal',
                'cylinder' => 'All',
                'remark' => '---',
            );
        } elseif ($intCountCylinder == 0) {
            $arrRemarks[] = array(
                'priority' => 'yellow',
                'concern' => 'Measurement',
                'event' => 'Ultrasonic sound signal',
                'cylinder' => 'All',
                'remark' => 'This is irrelevant if there is no sensor available or you didn\'t use it.',
            );
        } else {
            // !=, eigentlich sollte ich nur auf kleiner checken, so erwischt es auch die,d ie mehr haben (weiss nicht, ob das je geht)
            $arrRef = range(0, $intRefCountCylinder);
            unset($arrRef[0]);

            $arrResult = Arr::pluck($objStatement->fetchAll(), 'cyl_no');
            foreach ($arrResult as $intCylNo) {
                if (isset($arrRef[$intCylNo])) {
                    unset($arrRef[$intCylNo]);
                }
            }

            $arrRemarks[] = array(
                'priority' => 'yellow',
                'concern' => 'Measurement',
                'event' => 'Ultrasonic sound signal',
                'cylinder' => implode(', ', $arrRef),
                'remark' => 'At future measurements please make sure, that you have better coupling with your ultrasonic sound sensor. E.g. clean the surface and use coupling agent.',
            );
        }
    }

    /**
     * Diese Methode errechnet die Remarks für den Lastabgleich
     * @param $arrRemarks
     */
    public function createRemarksLoadTuning(&$arrRemarks)
    {
        // limit der Abweichung (0,05 -> 5%)
        $objStatement = $this->objMpdMeasurementDataRepository->retrieveLimitPower();

        // if ($objResult->count() != 1) {
        if ($objStatement->RowCount() != 1) {
            $arrRemarks[] = array(
                'priority' => 'red',
                'concern' => 'Engine',
                'event' => 'Load balance',
                'cylinder' => 'All',
                'remark' => 'no reference value found.',
            );
            return;
        }

        $floatLimitPower = (float) $objStatement->fetchColumn(0);
        $floatAvgLoad = $this->objMpdMeasurementDataRepository->retrieveLoadAvg();

        $objStatement = $this->objMpdMeasurementDataRepository->retrieveMeasurementNumFromAvgLoad(null, null, $floatAvgLoad * (1 + $floatLimitPower), $floatAvgLoad * (1 - $floatLimitPower));

        // wenn keiner --> alles ok
        // if (!$objResult->count()) {
        if (!$objStatement->RowCount()) {
            $arrRemarks[] = array(
                'priority' => 'green',
                'concern' => 'Engine',
                'event' => 'Load balance',
                'cylinder' => 'All',
                'remark' => 'Engine load balance is within range.',
            );
            return;
        }

        $arrCylinders = Arr::pluck($objStatement->fetchAll(), 'measurement_num');

        $arrRemarks[] = array(
            'priority' => 'red',
            'concern' => 'Engine',
            'event' => 'Load balance',
            'cylinder' => implode(', ', $arrCylinders),
            'remark' => 'Load balance exceeded limit.',
        );
    }

    /**
     * Diese Methode errechnet die Remarks für den Lastabgleich
     * @param $arrRemarks
     */
    public function createRemarksCompressionPressure(&$arrRemarks)
    {
        // limit der Abweichung (0.3 => 3bar)
        // $floatLimitPressure = Model_Engine_Params::getPressureLimit($this->objShip->marprime_serial_number, $this->intDateTs);
        $floatLimitPressure = $this->objMeasurementParamsRepository->retrievePressureLimit($this->objShip->getMarprimeSerialno(), date(BaseEntity::strDateFormat, $this->intDateTs));
        if ($floatLimitPressure === false) {
            $arrRemarks[] = array(
                'priority' => 'red',
                'concern' => 'Engine',
                'event' => 'Compression pressure deviation',
                'cylinder' => 'All',
                'remark' => 'no reference value found.',
            );
            return;
        }

        // Durchschnitts-Lastwert der Zylinder
        $floatAvgLoad = $this->objMpdMeasurementDataRepository->retrievePressureAvg();
        // $floatAvgLoad = Model_Mpd_Measurement_Data::getAveragePressure($this->objShip->marprime_serial_number, $this->intDateTs);

        // hole alle Zylinderdurchschnitte, die eine Messung ausserhalb des Ranges haben
        $objStatement = $this->objMpdMeasurementDataRepository->retrieveP0Deviation(null, null, $floatAvgLoad - $floatLimitPressure, $floatAvgLoad + $floatLimitPressure);
        // $objResult = Db::select(array(
        //     'measurement_num',
        //     'cyl_no',
        // ))->from('mpd_measurement_data')
        //     ->where('cyl_no', '=', 0)
        //     ->where('MarPrime_SerialNo', '=', $this->objShip->marprime_serial_number)
        //     ->where('date', '=', date(Model_Mpd_History::strDateFormat, $this->intDateTs))
        //     ->where_open()
        // # Anpassung siehe https://redmine.lumturo.net/issues/393
        //     ->where('p0', '<', $floatAvgLoad - $floatLimitPressure) //* (1 - $floatLimitPressure))
        //     ->or_where('p0', '>', $floatAvgLoad + $floatLimitPressure) //* (1 + $floatLimitPressure))
        //     ->where_close()
        //     ->execute('marprime', true);

        // wenn keiner --> alles ok
        // if (!$objResult->count()) {
        if (!$objStatement->RowCount()) {
            $arrRemarks[] = array(
                'priority' => 'green',
                'concern' => 'Engine',
                'event' => 'Compression pressure deviation',
                'cylinder' => 'All',
                'remark' => 'Compression pressure deviation is within range.',
            );
            return;
        }

        $arrCylinders = Arr::pluck($objStatement->fetchAll(), 'cyl_no');
        // $arrCylinders = array();
        // foreach ($objResult as $objRow) {
        //     $arrCylinders[$objRow->cyl_no] = $objRow->cyl_no;
        // }

        $arrRemarks[] = array(
            'priority' => 'red',
            'concern' => 'Engine',
            'event' => 'Compression pressure deviation',
            'cylinder' => implode(', ', $arrCylinders),
            'remark' => 'Compression pressure deviation exceeded limit.',
        );

    }

    /**
     * Diese Methode errechnet die Remarks für den Maximaldruck
     * @param $arrRemarks
     */
    public function createRemarksMaxPressure(&$arrRemarks)
    {
        // limit der Abweichung (0.3 => 3bar)
        $floatLimitPressure = $this->objMeasurementParamsRepository->retrievePressureLimit($this->objShip->getMarprimeSerialno(), date(BaseEntity::strDateFormat, $this->intDateTs));
        $arrRemark = array(
            'concern' => 'Engine',
            'event' => 'Maximum pressure deviation',
            'cylinder' => 'All',
        );

        if ($floatLimitPressure === false) {
            $arrRemarks[] = Arr::merge($arrRemark, array(
                'priority' => 'red',
                'remark' => 'no reference value found.',
            ));
            return;
        }

        // hole alle Zylinderdurchschnitte (cyl_no = 0), die eine Messung ausserhalb des Ranges haben
        $objStatement = $this->objMpdMeasurementDataRepository->retrieveErrorMaxPressureAvg(null, null, $floatLimitPressure);

        // wenn keiner --> alles ok
        if (!$objStatement->RowCount()) {
            $arrRemarks[] = Arr::merge($arrRemark, array(
                'priority' => 'green',
                'remark' => 'Maximum pressure deviation is within range.',
            ));
            return;
        }

        $arrCylinders = Arr::pluck($objStatement, 'measurement_num');

        $arrRemarks[] = Arr::merge($arrRemark, array(
            'priority' => 'red',
            'cylinder' => implode(', ', $arrCylinders),
            'remark' => 'Maximum pressure deviation exceeded limit.',
        ));
    }

    /**
     * Diese Methode errechnet die Remarks für den induzierten Druck
     * @param $arrRemarks
     */
    public function createRemarksMeanIndicatedPressure(&$arrRemarks)
    {
        $objStatement = $this->objMeasurementParamsRepository->retrieveLimitPressure(null, null, $this->objEngineParams->strokes);

        $arrRemark = array(
            'concern' => 'Engine',
            'event' => 'MIP deviation',
            'cylinder' => 'All',
        );

        if ($objStatement->RowCount() != 1) {
            // if ($objResult->count() != 1) {
            $arrRemarks[] = Arr::merge($arrRemark, array(
                'priority' => 'red',
                'remark' => 'no reference value found.',
            ));
            return;
        }

        $floatInductedPressure = (float) $objStatement->fetchColumn(0);
        // hole alle zylinderdurchschnitte, die eine Messung ausserhalb des Ranges haben
        $objStatement = $this->objMpdMeasurementDataRepository->retrieveMeanIndicatedPressureValues(null, null, $floatInductedPressure);

        // wenn keiner --> alles ok
        if (!$objStatement->RowCount()) {
            // if (!$objResult->count()) {
            $arrRemarks[] = Arr::merge($arrRemark, array(
                'priority' => 'green',
                'remark' => 'Mean indicated pressure deviation is within range.',
            ));
            return;
        }

        $arrCylinders = Arr::pluck($objStatement->fetchAll(), 'measurement_num');
        $arrRemarks[] = Arr::merge($arrRemark, array(
            'priority' => 'red',
            'cylinder' => implode(', ', $arrCylinders),
            'remark' => 'Mean indicated pressure deviation exceeded limit.',
        ));
    }

    /**
     * Diese Methode errechnet die Remarks für Leakage
     * @param $arrRemarks
     */
    public function createRemarksLeakage(&$arrRemarks)
    {
        $arrRemark = array(
            'concern' => 'Engine',
            'event' => 'Leakage',
            'cylinder' => 'All',
        );

        // hole alle Zylinderdurchschnitte, die eine Messung ausserhalb des Ranges haben
        $objStatement = $this->objMpdMeasurementDataRepository->retrieveLeakageValues();
//         $objResult = Db::select('measurement_num', 'aev')->from('mpd_measurement_data')
        // //            ->where('cyl_no', '!=', 0)
        //             ->where('cyl_no', '=', 0)
        //             ->where('MarPrime_SerialNo', '=', $this->objShip->marprime_serial_number)
        //             ->where('date', '=', date(Model_Mpd_History::strDateFormat, $this->intDateTs))
        //             ->where('aev', '>', 0.4)
        //             ->execute('marprime', TRUE);

        // wenn keiner --> alles ok
        if (!$objStatement->RowCount())
        // if (!$objResult->count())
        {
            $arrRemarks[] = Arr::merge($arrRemark, array(
                'priority' => 'green',
                'remark' => 'Normal/noncritical leakage below 40% detected.',
            ));
            return;
        }

        // checke, ob welche > 0.4 und > 0.8
        $arr04Cylinders = array();
        $arr08Cylinders = array();

        // foreach ($objResult as $objRow)
        foreach ($objStatement->fetchAll() as $arrRow) {
            if ($arrRow['aev'] > 0.8) {
                $arr08Cylinders[$arrRow['measurement_num']] = $arrRow['measurement_num'];
            } else {
                $arr04Cylinders[$arrRow['measurement_num']] = $arrRow['measurement_num'];
            }
        }

        if (count($arr04Cylinders)) {
            $arrRemarks[] = Arr::merge($arrRemark, array(
                'priority' => 'yellow',
                'cylinder' => implode(', ', $arr04Cylinders),
                'remark' => 'Valve leakage above 40% detected. A steady observation is recommended.',
            ));
        }
        if (count($arr08Cylinders)) {
            $arrRemarks[] = Arr::merge($arrRemark, array(
                'priority' => 'red',
                'cylinder' => implode(', ', $arr08Cylinders),
                'remark' => 'Valve leakage above 80% detected. Checking your valves for leakage is recommended!',
            ));
        }
    }

    /*
     * Diese Methode errechnet die Remarks für Reporterstellung
     * @param $arrRemarks
     */
    public function createRemarksReportCreated(&$arrRemarks)
    {
        $arrRemark = array(
            'concern' => 'Measurement',
            'event' => 'Engine Report',
            'cylinder' => 'All',
        );

        $intCount = $this->objReportRepository->retrieveReportCount($this->objShip->getMarprimeSerialno(), date(BaseEntity::strDateFormat));
        // $objResult = Db::select(array(Db::expr('COUNT(*)'), 'count'))->from('report')
        //     ->where('MarPrimeSerial', '=', $this->objShip->marprime_serial_number)
        //     ->where('date', '=', date(Model_Mpd_History::strDateFormat, $this->intDateTs))
        //     ->execute('marprime', true);

        // wenn einer --> alles ok
        if ($intCount) {
            // if ($objResult->count()) {
            $arrRemarks[] = Arr::merge($arrRemark, array(
                'priority' => 'green',
                'remark' => 'Report was created and successfully saved.',
            ));
            return;
        }

        // keinen gefunden
        $arrRemarks[] = Arr::merge($arrRemark, array(
            'priority' => 'yellow',
            'remark' => 'Report hasn\'t been created yet.',
        ));
    }

}
