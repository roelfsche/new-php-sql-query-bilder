<?php

namespace App\Service\Maridis\Pdf\Report;

use App\Entity\Marprime\EngineParams;
use App\Entity\UsrWeb71\ShipTable;
use Psr\Container\ContainerInterface;
use App\Service\Maridis\Pdf\Report;

class Engine extends Report
{

    protected $objShip = null;

    // Zeile aus der Tabelle -> stdObject
    protected $objEngineParams = null;

    // Modell, dass alle nötigen Berechnungen anstellt
    protected $objModel = null;

    public function __construct(ContainerInterface $objContainer, ShipTable $objShip, $objEngineParams, $intCreateTs)
    {
        parent::__construct($objContainer);
        $this->objShip = $objShip;
        $this->objEngineParams = $objEngineParams;

        $this->objModel = $objContainer->get('maridis.model.report.engine');
        $this->objModel->init($objShip, $this->objEngineParams, $intCreateTs);
    }

        /**
     * Diese Methode berechnet die Daten und druckt die Tabellen
     *
     * @return bool - wenn FALSE, dann keine Daten
     */
    public function create()
    {
        $boolDiag = FALSE; // Rückgabewert der Diagrammzeichnungsmethode
        $this->objModel->calculateData();

        $this->objModel->createRemarks();
        $objHistoryCollection = $this->objModel->objHistoryCollection;  //calculateCompressionData();
        $objLeakageResult = $this->objModel->calculateLeakageData();
        $this->AddPage();
        $this->statisticOverview();
        $this->AddPage();
        $this->remarks();
        $this->AddPage();


        //zeichne min-/max-Linien ein
        // Pressure / Max Pressrue
        $arrHorizontalLines = array();
        $floatAvgPressure = Model_Mpd_Measurement_Data::getAveragePressure($this->objShip->marprime_serial_number, $this->objModel->intDateTs);
        $floatMaxPressure = Model_Mpd_Measurement_Data::getAverageMaxPressure($this->objShip->marprime_serial_number, $this->objModel->intDateTs);
        $floatLimitPressure = Model_Engine_Params::getPressureLimit($this->objShip->marprime_serial_number, $this->objModel->intDateTs);
        if ($floatLimitPressure !== FALSE)
        {
            $objPlotlineMax = new JpGraph\PlotLine(HORIZONTAL, ($floatAvgPressure + $floatLimitPressure) * 10, Kohana::$config->load('report.engine.color.red'));
            $objPlotlineMin = new JpGraph\PlotLine(HORIZONTAL, ($floatAvgPressure - $floatLimitPressure) * 10, Kohana::$config->load('report.engine.color.red'));
            $arrHorizontalLines[] = $objPlotlineMax;
            $arrHorizontalLines[] = $objPlotlineMin;
//            $graph->Add($objPlotlineMax);
//            $graph->Add($objPlotlineMin);

            $objPlotlineMax = new JpGraph\PlotLine(HORIZONTAL, ($floatMaxPressure + $floatLimitPressure) * 10, Kohana::$config->load('report.engine.color.dark_red'));
            $objPlotlineMin = new JpGraph\PlotLine(HORIZONTAL, ($floatMaxPressure - $floatLimitPressure) * 10, Kohana::$config->load('report.engine.color.dark_red'));
            $arrHorizontalLines[] = $objPlotlineMax;
            $arrHorizontalLines[] = $objPlotlineMin;
//            $graph->Add($objPlotlineMax);
//            $graph->Add($objPlotlineMin);
//            $graph->legend->Add('comp. press. interval', Kohana::$config->load('report.engine.color.red'));
//            $graph->legend->Add('max comp. press. interval', Kohana::$config->load('report.engine.color.dark_red'));
        }


        $boolDiag = $this->drawDiagram('compression pressure / max. cylinder pressure', 'Cylinder', 'pressure [bar]', array_values($objHistoryCollection->as_array('measurement_num', 'measurement_num')), array(
            array_map('floatval', array_values($objHistoryCollection->as_array('measurement_num', 'comp_pressure'))),
            array_map('floatval', array_values($objHistoryCollection->as_array('measurement_num', 'max_pressure'))),
        ), FALSE, array(), array(), array(
            'compression pressure',
            'max. cylinder pressure',
        ), function ($graph)
        {
            // Setze die Legende
            $graph->legend->SetColumns(1);
            $graph->legend->SetPos(.01, .3, 'right', 'top');
            $graph->SetMargin(50, 200, 10, 10);

            // setze y-min/-max manuell, damit die Zahlen auf den Bars nicht abgeschnitten werden, weil sie über das Diagramm hinaus ragen
            $arrPlots = $graph->plots;
            $arrMinMax = $graph->GetPlotsYMinMax($arrPlots);
            $intMax = (int)$arrMinMax[1] + 10;
            $graph->yscale->SetAutoMax($intMax);


            // Legende für die Horizontalen
            // müsste eigentlich checken, ob die Horizontalen da sind...
            $graph->legend->Add('comp. pressure limit', Kohana::$config->load('report.engine.color.red'));
            $graph->legend->Add('max. pressure limit', Kohana::$config->load('report.engine.color.dark_red'));

            return $graph;
        }, $arrHorizontalLines);



        if ($boolDiag)
        {
            $this->addY(68);
        }
        $boolDiag |= $this->drawDiagram('Angle max pressure', 'Cylinder', 'angle max pressure [°CA]', array_values($objHistoryCollection->as_array('measurement_num', 'measurement_num')), array(array_map('floatval', array_values($objHistoryCollection->as_array('measurement_num', 'apmax')))));
        if ($boolDiag)
        {
            $this->AddPage();
        }


        $boolDiag = $this->drawDiagram('Indicated Power', 'Cylinder', 'indicated power [kW]', array_values($objHistoryCollection->as_array('measurement_num', 'measurement_num')), array(array_map('floatval', array_values($objHistoryCollection->as_array('measurement_num', 'ind_power')))));
        if ($boolDiag)
        {
            $this->addY(68);
        }


        $objLoadBalanceResult = $this->objModel->calculateLoadBalanceData();
        $arrValues = array_map('floatval', array_values($objLoadBalanceResult->as_array('measurement_num', 'load_balance')));
        $boolDiag |= $this->drawDiagram('Load balance', 'Cylinder', 'load balance [%]', array_values($objLoadBalanceResult->as_array('measurement_num', 'measurement_num')), array($arrValues), TRUE, array(), array(), array(), function ($graph)
        {
            // setze min/max der y-Achse, so dass die Grenze (5) immer zu sehen ist
            $arrPlots = $graph->plots;
            $arrMinMax = $graph->GetPlotsYMinMax($arrPlots);
            $intMax = max(5, max(abs((int)$arrMinMax[0]), (int)$arrMinMax[1])) + 1;
            $graph->yscale->SetAutoMax($intMax);
            $graph->yscale->SetAutoMin(-1 * $intMax);
            $objPlotlineMax = new JpGraph\PlotLine(HORIZONTAL, 5, Kohana::$config->load('report.engine.color.red'));
            $objPlotlineMin = new JpGraph\PlotLine(HORIZONTAL, -5, Kohana::$config->load('report.engine.color.red'));
            $objPlotlineZero = new JpGraph\PlotLine(HORIZONTAL, 0, '#333333');
            $graph->Add($objPlotlineMax);
            $graph->Add($objPlotlineMin);
            $graph->Add($objPlotlineZero);
            return $graph;
        });
        if ($boolDiag)
        {
            $this->AddPage();
        }


        if ($this->drawPressureCurveDiagram())
        {
            $this->AddPage();
        }

        $this->drawLeakageDiagram($objLeakageResult);

        return TRUE;
    }
}
