<?php

namespace App\Service\Maridis\Model\Report;

use App\Entity\Marprime\EngineParams;
use App\Entity\UsrWeb71\ShipTable;
use App\Exception\MscException;
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

    public function __construct(ManagerRegistry $objDoctrineRegistry)
    {
        parent::__construct($objDoctrineRegistry);

        $this->objEngineParamsRepository = $objDoctrineRegistry
            ->getManager('marprime')
            ->getRepository(EngineParams::class);
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
}
