<?php

namespace App\Service\Maridis\Pdf\Report;

use Amenadiel\JpGraph\Graph\Graph;
use Amenadiel\JpGraph\Plot\BarPlot;
use Amenadiel\JpGraph\Plot\GroupBarPlot;
use Amenadiel\JpGraph\Plot\LinePlot;
use Amenadiel\JpGraph\Plot\PlotLine;
use Amenadiel\JpGraph\Themes\UniversalTheme;
use App\Entity\BaseEntity;
use App\Entity\UsrWeb71\ShipTable;
use App\Kohana\Arr;
use App\Kohana\Text;
use App\Service\Maridis\Pdf\Report;
use Psr\Container\ContainerInterface;

class Engine extends Report
{

    protected $objShip = null;

    // Zeile aus der Tabelle -> stdObject
    protected $objEngineParams = null;

    // Modell, dass alle nötigen Berechnungen anstellt
    /**
     * @var $objModel App\Service\Maridis\Model\Report
     */
    public $objModel = null;

    public function __construct(ContainerInterface $objContainer, ShipTable $objShip, $objEngineParams, $intCreateTs)
    {
        parent::__construct($objContainer);
        $this->objShip = $objShip;
        $this->objEngineParams = $objEngineParams;

        $this->objModel = $objContainer->get('maridis.model.report.engine');
        $this->objModel->init($objShip, $this->objEngineParams, $intCreateTs);
        // $this->objReedereiService = $objContainer->get('maridis.reederei');
        $this->objReedereiService->setShip($objShip);

        $intErrorLevel = error_reporting(0);
        require_once $objContainer->get('kernel')->getProjectDir() . '/vendor/roelfsche/jpgraph/src/config.inc.php';
        error_reporting($intErrorLevel);
    }

    /**
     * Diese Methode berechnet die Daten und druckt die Tabellen
     *
     * @return bool - wenn FALSE, dann keine Daten
     */
    public function create()
    {
        $boolDiag = false; // Rückgabewert der Diagrammzeichnungsmethode
        $this->objModel->calculateData();

        $this->objModel->createRemarks();
        $arrHistory = $this->objModel->arrCalculatedHistory; //calculateCompressionData();
        $objLeakageStatement = $this->objModel->calculateLeakageData();
        $this->AddPage();
        $this->statisticOverview();
        $this->AddPage();
        $this->remarks();
        $this->AddPage();
        //zeichne min-/max-Linien ein
        // Pressure / Max Pressrue
        $arrHorizontalLines = array();

        $floatAvgPressure = $this->objModel->objMpdMeasurementDataRepository->retrievePressureAvg();
        $floatMaxPressure = $this->objModel->objMpdMeasurementDataRepository->retrieveMaxPressureAvg();
        $floatLimitPressure = $this->objModel->objMeasurementParamsRepository->retrievePressureLimit();
        if ($floatLimitPressure !== false) {
            $objPlotlineMax = new PlotLine(HORIZONTAL, ($floatAvgPressure + $floatLimitPressure) * 10, Arr::path($this->arrConfig, 'color.red'));
            $objPlotlineMin = new PlotLine(HORIZONTAL, ($floatAvgPressure - $floatLimitPressure) * 10, Arr::path($this->arrConfig, 'color.red'));
            $arrHorizontalLines[] = $objPlotlineMax;
            $arrHorizontalLines[] = $objPlotlineMin;

            $objPlotlineMax = new PlotLine(HORIZONTAL, ($floatMaxPressure + $floatLimitPressure) * 10, Arr::path($this->arrConfig, 'color.dark_red'));
            $objPlotlineMin = new PlotLine(HORIZONTAL, ($floatMaxPressure - $floatLimitPressure) * 10, Arr::path($this->arrConfig, 'color.dark_red'));
            $arrHorizontalLines[] = $objPlotlineMax;
            $arrHorizontalLines[] = $objPlotlineMin;
        }

        $boolDiag = $this->drawDiagram('compression pressure / max. cylinder pressure', 'Cylinder', 'pressure [bar]', array_values($this->objModel->getEntityValues($arrHistory, ['measurement_num', 'measurement_num'])), array(
            // extra-Mapping per array_values, da ich in der Version der Lib nun mit 0 anfangen muss
            // measurement_num's starten immer mit 1; war bisher dann auch der Index; array_values() nummeriert nochmal neu von 0 durch...
            array_values(array_map('floatval', $this->objModel->getEntityValues($arrHistory, ['measurement_num', 'comp_pressure']))),
            array_values(array_map('floatval', $this->objModel->getEntityValues($arrHistory, ['measurement_num', 'max_pressure']))),
        ), false, array(), array(), array(
            'compression pressure',
            'max. cylinder pressure',
        ), function ($graph) {
            // Setze die Legende
            $graph->legend->SetColumns(1);
            $graph->legend->SetPos(.01, .3, 'right', 'top');
            $graph->SetMargin(50, 200, 10, 10);

            // setze y-min/-max manuell, damit die Zahlen auf den Bars nicht abgeschnitten werden, weil sie über das Diagramm hinaus ragen
            $arrPlots = $graph->plots;

            $arrMinMax = $graph->GetPlotsYMinMax($arrPlots);
            $intMax = (int) $arrMinMax[1] + 10;
            $graph->yscale->SetAutoMax($intMax);

            // Legende für die Horizontalen
            // müsste eigentlich checken, ob die Horizontalen da sind...
            $graph->legend->Add('comp. pressure limit', Arr::path($this->arrConfig, 'color.red'));
            $graph->legend->Add('max. pressure limit', Arr::path($this->arrConfig, 'color.dark_red'));

            return $graph;
        }, $arrHorizontalLines);

        if ($boolDiag) {
            $this->addY(68);
        }
        $boolDiag |= $this->drawDiagram('Angle max pressure', 'Cylinder', 'angle max pressure [°CA]', array_values($this->objModel->getEntityValues($arrHistory, ['measurement_num', 'measurement_num'])), array(array_values(array_map('floatval', $this->objModel->getEntityValues($arrHistory, ['measurement_num', 'apmax'])))));
        // $boolDiag |= $this->drawDiagram('Angle max pressure', 'Cylinder', 'angle max pressure [°CA]', array_values($arrHistory->as_array('measurement_num', 'measurement_num')), array(array_map('floatval', array_values($arrHistory->as_array('measurement_num', 'apmax')))));
        if ($boolDiag) {
            $this->AddPage();
        }
        $boolDiag = $this->drawDiagram('Indicated Power', 'Cylinder', 'indicated power [kW]', array_values($this->objModel->getEntityValues($arrHistory, ['measurement_num', 'measurement_num'])), array(array_values(array_map('floatval', $this->objModel->getEntityValues($arrHistory, ['measurement_num', 'ind_power'])))));
        if ($boolDiag) {
            $this->addY(68);
        }
        $arrLoadBalance = $this->objModel->calculateBalanceData()->fetchAll();
        $arrData = [];
        $arrData1 = [];
        foreach ($arrLoadBalance as $arrRow) {
            $arrData[] = (float) $arrRow['load_balance'];
            $arrData1[] = $arrRow['measurement_num'];
        }

        $boolDiag |= $this->drawDiagram('Load balance', 'Cylinder', 'load balance [%]', $arrData1, array($arrData), true, array(), array(), array(), function ($graph) {
            // setze min/max der y-Achse, so dass die Grenze (5) immer zu sehen ist
            $arrPlots = $graph->plots;
            $arrMinMax = $graph->GetPlotsYMinMax($arrPlots);
            $intMax = max(5, max(abs((int) $arrMinMax[0]), (int) $arrMinMax[1])) + 1;
            $graph->yscale->SetAutoMax($intMax);
            $graph->yscale->SetAutoMin(-1 * $intMax);
            $objPlotlineMax = new PlotLine(HORIZONTAL, 5, Arr::path($this->arrConfig, 'color.red'));
            $objPlotlineMin = new PlotLine(HORIZONTAL, -5, Arr::path($this->arrConfig, 'color.red'));
            $objPlotlineZero = new PlotLine(HORIZONTAL, 0, '#333333');
            $graph->Add($objPlotlineMax);
            $graph->Add($objPlotlineMin);
            $graph->Add($objPlotlineZero);
            return $graph;
        });
        if ($boolDiag) {
            $this->AddPage();
        }

        if ($this->drawPressureCurveDiagram()) {
            $this->AddPage();
        }
        $this->drawLeakageDiagram($objLeakageStatement->fetchAll());

        return true;
    }

    /**
     * Diese Methode druckt die Tabelle des Statistik-Overviews auf die Seite
     */
    public function statisticOverview()
    {
        $this->addHeadline('Measurements', 'h2');

        $this->addY(Arr::path($this->arrConfig, 'table.margin_top', 0));
        // $this->addY(Arr::get(Kohana::$config->load('report.pdf.default.table'), 'margin_top', 0));
        $this->setTableHead(array(
            'Value',
            'Speed [rpm]',
            'Speed [%]',
            'p comp [bar]',
            'p max [bar]',
            'Angle p max [°Ca]',
            'p comp/p scav [-]',
            'MIP [bar]',
            'Indicated Power [kW]',
            'Load [%]',
            'probable Leakage [%]',
        ), array(11, 6, 8, 8, 10, 10, 8, 10, 10, 9, 10));

        $intFlag = 0;

        foreach ($this->objModel->arrCalculatedHistory as $objRow) {
            $intFlag ^= 1; // xor 1
            if (!$this->addTableRow(array(
                'Cylinder ' . $objRow->measurement_num, //cyl_no,
                number_format($objRow->get('revolution'), 1, '.', ','),
                number_format($objRow->get('rel_speed'), 1),
                number_format($objRow->get('comp_pressure'), 1),
                number_format($objRow->get('max_pressure'), 1),
                number_format($objRow->get('apmax'), 1),
                number_format($objRow->get('pcomp_rel_pscav'), 2),
                number_format($objRow->get('mean_ind_pressure'), 1),
                number_format($objRow->get('ind_power'), 1, '.', ','),
                number_format($objRow->get('load'), 1, '.', ','),
                ($objRow->leakage < 5) ? '<5' : number_format($objRow->leakage, 2),
            ), $this->compileConfigs(array(
                // 'report.engine.table.cell',
                'table.cell',
            )), true, true)
            ) {
                if (!$intFlag) {
                    $intFlag = 1; // wieder auf "weissen Hintergrund" stellen
                }
            }
        }

        $arrAggregates = $this->objModel->arrStatistic;
        # Total/Average/Max/Min
        $this->addTableRow(array(
            'Total Value',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            array(
                'value' => number_format($arrAggregates['total']['indicated_power'], 1, '.', ','),
                'config' => $this->compileConfigs(array(
                    'table.cell',
                    'table.dark_gray_cell',
                )),
            ),
            '',
            '',
        ), $this->compileConfigs(array(
            'table.cell',
        )), true, true);
        // Average
        $this->addTableRow(array(
            'Average Value',
            number_format($arrAggregates['avg']['speed'], 1, '.', ','),
            number_format($arrAggregates['avg']['rel_speed'], 1, '.', ','),
            number_format($arrAggregates['avg']['p_comp'], 1, '.', ','),
            number_format($arrAggregates['avg']['p_max'], 1, '.', ','),
            '',
            number_format($arrAggregates['avg']['pcomp_rel_pscav'], 1, '.', ','),
            number_format($arrAggregates['avg']['mip'], 1, '.', ','),
            number_format($arrAggregates['avg']['ind_power'], 1, '.', ','),
            number_format($arrAggregates['avg']['load'], 1, '.', ','),
            '',
        ), $this->compileConfigs(array(
            'table.cell',
        )), true, true);
        // Max
        $this->addTableRow(array(
            array(
                'value' => 'Maximum',
                'config' => $this->compileConfigs(array(
                    'table.cell',
                )),
            ),
            number_format($arrAggregates['max']['speed'], 1, '.', ','),
            number_format($arrAggregates['max']['rel_speed'], 1, '.', ','),
            number_format($arrAggregates['max']['p_comp'], 1, '.', ','),
            number_format($arrAggregates['max']['p_max'], 1, '.', ','),
            number_format($arrAggregates['max']['angle_pmax'], 1, '.', ','),
            number_format($arrAggregates['max']['pcomp_rel_pscav'], 1, '.', ','),
            number_format($arrAggregates['max']['mip'], 1, '.', ','),
            number_format($arrAggregates['max']['ind_power'], 1, '.', ','),
            number_format($arrAggregates['max']['load'], 1, '.', ','),
            '',
        ), $this->compileConfigs(array(
            'table.cell',
            'table.dark_gray_cell',
        )), true, true);
        // Min
        $this->addTableRow(array(
            array(
                'value' => 'Minimum',
                'config' => $this->compileConfigs(array(
                    'table.cell',
                )),
            ),
            number_format($arrAggregates['min']['speed'], 1, '.', ','),
            number_format($arrAggregates['min']['rel_speed'], 1, '.', ','),
            number_format($arrAggregates['min']['p_comp'], 1, '.', ','),
            number_format($arrAggregates['min']['p_max'], 1, '.', ','),
            number_format($arrAggregates['min']['angle_pmax'], 1, '.', ','),
            number_format($arrAggregates['min']['pcomp_rel_pscav'], 1, '.', ','),
            number_format($arrAggregates['min']['mip'], 1, '.', ','),
            number_format($arrAggregates['min']['ind_power'], 1, '.', ','),
            number_format($arrAggregates['min']['load'], 1, '.', ','),
            '',
        ), $this->compileConfigs(array(
            'table.cell',
            'table.dark_gray_cell',
        )), true, true);
        // Difference
        $this->addTableRow(array(
            array(
                'value' => 'Difference',
                'config' => $this->compileConfigs(array(
                    'table.cell',
                )),
            ),
            number_format($arrAggregates['diff']['speed'], 1, '.', ','),
            number_format($arrAggregates['diff']['rel_speed'], 1, '.', ','),
            number_format($arrAggregates['diff']['p_comp'], 1, '.', ','),
            number_format($arrAggregates['diff']['p_max'], 1, '.', ','),
            '',
            number_format($arrAggregates['diff']['pcomp_rel_pscav'], 1, '.', ','),
            number_format($arrAggregates['diff']['mip'], 1, '.', ','),
            number_format($arrAggregates['diff']['ind_power'], 1, '.', ','),
            number_format($arrAggregates['diff']['load'], 1, '.', ','),
            '',
        ), $this->compileConfigs(array(
            'table.cell',
            'table.dark_gray_cell',
        )), true, true);
        // Deviation
        $this->addTableRow(array(
            array(
                'value' => 'Deviation [%]',
                'config' => $this->compileConfigs(array(
                    'table.cell',
                )),
            ),
            number_format($arrAggregates['dev']['speed'], 1, '.', ','),
            number_format($arrAggregates['dev']['rel_speed'], 1, '.', ','),
            number_format($arrAggregates['dev']['p_comp'], 1, '.', ','),
            number_format($arrAggregates['dev']['p_max'], 1, '.', ','),
            '',
            number_format($arrAggregates['dev']['pcomp_rel_pscav'], 1, '.', ','),
            number_format($arrAggregates['dev']['mip'], 1, '.', ','),
            number_format($arrAggregates['dev']['ind_power'], 1, '.', ','),
            number_format($arrAggregates['dev']['load'], 1, '.', ','),
            '',
        ), $this->compileConfigs(array(
            'table.cell',
            'table.dark_gray_cell',
        )), true, true);
    }

    /**
     * Diese Methode druckt die Remarks.
     *
     * @throws Kohana_Exception
     */
    public function remarks()
    {
        $this->addHeadline('Remarks', 'h2');

        $this->addY(Arr::path($this->arrConfig, 'table.margin_top', 0));
        // $this->addY(Arr::get(Kohana::$config->load('report.pdf.default.table'), 'margin_top', 0));
        $this->setTableHead(array(
            '#',
            'Prio',
            'Concern',
            'Event',
            'Cylinder',
            'Remark',
        ), array(5, 4, 10, 15, 10, 56));
        foreach ($this->objModel->arrRemarks as $intIndex => $arrRemark) {

            $this->addTableRow(array(
                $intIndex + 1,
                array(
                    'value' => '', //$arrRemark['priority'],
                    // 'config' => array_merge(Arr::merge(Kohana::$config->load('report.pdf.monthly_performance.table.cell'),
                    //     $this->decodeHexColor(Kohana::$config->load('color.' . $arrRemark['priority']), array('fill_color_red', 'fill_color_green', 'fill_color_blue'))
                    // ),
                    'config' => array_merge(Arr::merge(Arr::path($this->arrReportConfig, 'pdf.monthly_performance.table.cell'),
                        $this->decodeHexColor(Arr::path($this->arrConfig, 'color.' . $arrRemark['priority']), array('fill_color_red', 'fill_color_green', 'fill_color_blue'))
                    ),
                        array('border' => true)),
                ),
                $arrRemark['concern'],
                $arrRemark['event'],
                $arrRemark['cylinder'],
                $arrRemark['remark'],
            ), $this->compileConfigs(array(
                'table.cell',
            )), true, true);
        }
        $this->Ln();
        $this->legend(array(
            array(
                'color' => Arr::path($this->arrConfig, 'color.green'), //(Kohana::$config->load('color.green'),
                'x' => 15,
                'label' => 'Everything is in order',
            ),
            array(
                'color' => Arr::path($this->arrConfig, 'color.yellow'), //(Kohana::$config->load('color.green'),
                // 'color' => Kohana::$config->load('color.yellow'),
                'x' => 65,
                'label' => 'Warning - action is not necessary',
            ),
            array(
                'color' => Arr::path($this->arrConfig, 'color.red'), //(Kohana::$config->load('color.green'),
                // 'color' => Kohana::$config->load('color.red'),
                'x' => 135,
                'label' => 'Warning - action required',
            ),
        ));

    }

    /**
     * Diese Methode zeichnet eine Legende in derart: Farbkästchen Label {Farbkästchen Label} usw
     *
     * @param $arrConfig
     *         array(
     *         array(
     *         'x' => integer
     *         'y' => integer
     *         'label' => string
     *         'color' => string #00ff00
     *         ),
     *         ...)
     */
    public function legend($arrConfig)
    {
        // $arrFont = Kohana::$config->load('report.engine.legend');
        $arrFont = Arr::get($this->arrConfig, 'legend'); //Kohana::$config->load('report.engine.legend');
        $this->SetFont($arrFont['font_family'], $arrFont['font_style'], $arrFont['font_size']);
        foreach ($arrConfig as $arrEntry) {
            $intX = Arr::get($arrEntry, 'x', $this->getX());
            $intY = Arr::get($arrEntry, 'y', $this->getY());
            $strLabel = Arr::get($arrEntry, 'label', 'label missing!!!');
            $this->SetX($intX);
            $this->SetY($intY, false);
            $arrColor = $this->decodeHexColor(Arr::get($arrEntry, 'color'));
            $this->Rect($this->GetX(), $this->GetY(), 10, 5, 'F', 0, $arrColor); //$arrStyleGreen, )
            $this->SetX($this->GetX() + 15);
            $this->Write(0, $strLabel);
        }
    }

    public function decodeHexColor($strColorCode, $arrIndizes = array('R', 'G', 'B'))
    {
        $arrRet = sscanf($strColorCode, "#%02x%02x%02x");
        return array_combine($arrIndizes, $arrRet);
    }

    public function Header()
    {
        $this->addY(3);
        $strTmpLogoFilename = $this->objReedereiService->getLogoFilePath(null, 144);
        if ($strTmpLogoFilename) {
            $this->Image($strTmpLogoFilename, 0, 5, 0, 20, '', '', '', false, 300, 'R');
        }
        $this->setCellPaddings(0, 1, 0, 0);
        $this->SetFont('freesans', '', 12, '', true);
        $this->Write(0, 'Cylinder Indication Report "' . $this->objShip->getAktName() . '"', '', 0, 'L', true); //, 0, '', 0, FALSE, 'M', 'M');
        $this->SetFont('freesans', '', 10, '', true);
        $this->Write(0, 'IMO-No.: ' . $this->objShip->getImoNo(), '', 0, 'L');
        $this->SetX(90);
//        $this->Write(0, 'IMO-No.: ' . $this->objShip->imo_number, '', 0, 'L', TRUE);
        $this->Write(0, 'Report Date: ' . date('d/m/Y'), '', 0, 'L', false);
        $this->SetX(160);
//        $this->Write(0, 'Measurement Date: ' . date('d/m/Y', strtotime($this->objEngineParams->date)), '', 0, 'L', TRUE);
        $this->Write(0, 'Measurement Date: ' . date('d/m/Y', $this->objModel->intDateTs), '', 0, 'L', true);

        $this->Write(0, 'Engine: ' . $this->objEngineParams->engine_name, '', 0, 'L');
        $this->SetX(90);
        $this->Write(0, 'Engine-Type: ' . $this->objEngineParams->engine_type, '', 0, 'L', false);
        $this->SetX(160);
        // $this->Write(0, 'Shipping Company: ' . Text::limit_words($this->cleanContent($objShippingCompany->company_name), 2, ''), '', 0, 'L', TRUE);
        $this->Write(0, 'Shipping Company: ' . Text::limit_words($this->cleanContent($this->objReedereiService->getReederei()->getCompanyName()), 2, ''), '', 0, 'L', true);
        $this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array('R' => 192, 'G' => 192, 'B' => 192)));
        $this->Cell(0, 0, '', 'B', 1, 'C', 0, '', 0, false, 'M', 'M');
    }

    public function Footer()
    {
        // Position at 25 mm from bottom
        $this->SetY(-18);
        // Set font
        $this->SetFont('freesans', '', 9);
        $this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array('R' => 0, 'G' => 0, 'B' => 0)));
        // Page number
        $this->Cell(0, 8, 'page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 'T', 1, 'C', 0, '', 0, false, 'T', 'M');
        $this->SetFont('freesans', '', 6);
        $this->SetTextColor(0, 0, 0);
//        $this->addY(1);
        //        $this->Write(0, 'Owner of the listed data is the shipping company. The shipping company is responsible for the content. The report service is powered by MARIDIS GmbH.', '', FALSE, 'L', FALSE);
        $this->Write(0, 'Owner of the listed data is the company owning the engine. The company is responsible for the content. The report service is powered by MARIDIS GmbH.', '', false, 'C', true);
        $this->SetTextColor(0, 0, 255);
        $this->SetX(120);
        $this->Write(0, 'www.maridis.de', 'https://www.maridis.de', false, 'L', false);
        $this->SetTextColor(0, 0, 0);
        $this->Write(0, ' email ', '', false, 'L', false);
        $this->SetTextColor(0, 0, 255);
        $this->Write(0, 'maridis@maridis.de', 'maridis@maridis.de', false, 'L', false);
    }

    /**
     * Diese Methode zeichnet ein Balken-Diagramm in das PDF
     * @param            $strHeadline        - Überschrift
     * @param            $strXTitle          - title für X-Achse
     * @param            $strYTitle          - title für Y-Achse
     * @param            $arrTicks           - ind. Array mit X-Achsenbezeichnungen
     * @param            $arrGraphs          - ind. Array mit Datenreihen:
     *                                       - array(
     *                                       0 => array(intwert1, intwert2, intwert3), // für erste Reihe
     *                                       1 => array(intwert1, intwert2, intwert3), // für zweite Reihe
     *                                       )
     * @param bool|FALSE $boolSetXPosToMin   - wenn TRUE, wird die X-Achse ganz nach unten verschoben (wichtig, wenn neg. Werte
     * @param array      $arrSizes           - array mit Grössen-Angaben für Diagramm + Bild im PDF:
     *                                       - array(
     *                                       'diagram => array('width' => int, 'height' => int), // pixel
     *                                       'image => array('width' => int, 'height' => int) // mm
     *                                       )
     * @param array      $arrColor           - ind. Array mit Farbdefinitionen für die Bars
     *                                       - array(
     *                                       array(        // für 1. Datenreihe
     *                                       'color' => 'white',
     *                                       'fill_color' => '#0080ff'
     *                                       ),
     *                                       .... // weitere Datenreihen
     *                                       )
     * @param function   $funcPostProcession - wenn übergeben, wird diese Funktion nach allem ausgeführt (vor der Image-Kreierung). Als Parameter wird das graph-Objekt übergeben, welches auch zurück geliefert werden muss.
     *                                       Hintergrund ist, dass ich bei load balance noch eine obere und eine untere grenze (5%, -5%) einzeichnen muss.
     * @return boolean FALSE, wenn keine Daten gefunden, dann wurde auch kein Diagramm gedruckt
     */
    protected function drawDiagram($strHeadline, $strXTitle, $strYTitle, $arrTicks, $arrGraphs, $boolSetXPosToMin = false, $arrSizes = array(), $arrColor = array(), $arrLegendLabels = array(), $funcPostProcession = null, $arrHorizontalLines = array())
    {
        $boolFoundData = false; // einige haben keine DAten
        // defaults
        $arrSizes = Arr::merge(array(
            'diagram' => array(
                'width' => 1022,
                'height' => 400,
            ),
            'image' => array(
                'width' => 210,
                'height' => 70,
            ),
        ), $arrSizes);
        $arrColor = Arr::merge(array(
            array(
                'color' => 'white',
//                'fill_color' => '#006400'
                'fill_color' => '#7a818d',
            ),
            array(
                'color' => 'white',
//                'fill_color' => '#228622'
                'fill_color' => '#b3b7be',
            ),
        ), $arrColor);

        // Create a graph instance
        $graph = new Graph($arrSizes['diagram']['width'], $arrSizes['diagram']['height']);
//        $graph->SetFrame(FALSE);

        // Specify what scale we want to use,
        // text = txt scale for the X-axis
        // int = integer scale for the Y-axis
        $graph->SetScale('textint');

        $theme_class = new UniversalTheme();
        $graph->SetTheme($theme_class);

        // Setup a title for the graph
        //        $graph->title->Set('Sunspot example');

        // Setup titles and X-axis labels
        $graph->xaxis->title->Set($strXTitle);
        // nochmal array_values(), damit ich beim index bei 0 anfange
        $graph->xaxis->SetTickLabels($arrTicks);

        // bei Bedarf die X-Achse ganz nach unten schieben (bspw. wenn pos + neg Y-Werte); sonst stehen die Label mitten in der vertik. Mitte
        if ($boolSetXPosToMin) {
            $graph->xaxis->SetPos('min');
        }

        // Setup Y-axis title
        $graph->yaxis->title->Set($strYTitle);
        // damit 'effective power' nicht zu dicht an den Zahlen steht
        $graph->yaxis->SetTitleMargin(35);
        $graph->SetMargin(50, 50, 50, 50);

        if (count($arrHorizontalLines)) {
            foreach ($arrHorizontalLines as $objLine) {
                $graph->Add($objLine);
            }
        }

        // Create the bar plot
        if (count($arrGraphs) > 1) {

            $arrBarPlots = array();
            foreach ($arrGraphs as $intIndex => $arrGraph) {
                if (count($arrGraph)) {
                    $boolFoundData = true;
                    $objBarPlot = new BarPlot($arrGraph);
                    $strLegendLabel = Arr::get($arrLegendLabels, $intIndex);
                    if ($strLegendLabel) {
                        $objBarPlot->SetLegend($strLegendLabel);
                    }
                    $arrBarPlots[] = $objBarPlot;
                }
            }

            // erstelle eine Gruppe zum gruppieren mehrerer Reihen
            $objGroupBarPlot = new GroupBarPlot($arrBarPlots);

            // Add the plot to the graph
            $graph->Add($objGroupBarPlot);

            // muss die Farbe leider nach dem Add() setzen
            foreach ($arrBarPlots as $intIndex => $objBarPlot) {
                $objBarPlot->SetColor($arrColor[$intIndex]['color']);
                $objBarPlot->SetFillColor($arrColor[$intIndex]['fill_color']);
                $objBarPlot->value->Show();
            }
        } else {
            // hole mir cyl_no => angle_pmax, indiziere das array dann von 0 an neu und mache alle werte zu float-Werten
            if (count($arrGraphs[0])) {
                $objBarplot = new BarPlot($arrGraphs[0]);
                $boolFoundData = true;
                // Add the plot to the graph
                $graph->Add($objBarplot);

                $objBarplot->SetColor($arrColor[0]['color']);
                $objBarplot->SetFillColor($arrColor[0]['fill_color']);
                $objBarplot->value->Show();
            }
        }

        // keine Daten gefunden?
        if (!$boolFoundData) {
            return false;
        }

        if ($funcPostProcession) {
            $graph = $funcPostProcession($graph);
        }

        // jetzt erst die Überschrift, weil evtl. oben schon Rücksprung, weil keine Daten gefunden
        $this->addHeadline($strHeadline, 'h2');

        // speichere das Diagramm
        $strFilename = tempnam(sys_get_temp_dir(), 'msc_diagram_');
        $graph->Stroke($strFilename);
        // zeichne das Diagramm ins PDF
        $this->Image($strFilename, '', '', $arrSizes['image']['width'], $arrSizes['image']['height']);
        return true;
    }

    /**
     * Diese Methode zeichnet das Pressure-Linien-Diagramm
     *
     * @param array $arrSizes
     */
    protected function drawPressureCurveDiagram($arrSizes = array())
    {
        // defaults
        $arrSizes = Arr::merge(array(
            'diagram' => array(
                'width' => 1.25 * 940, //1400,
                'height' => 1.25 * 600, //1000
            ),
            'image' => array(
                'width' => 1.4 * 140,
                'height' => 1.4 * 100,
            ),
        ), $arrSizes);

        $arrColor = array(
            '#000000', '#000080', '#0000FF', '#008000', '#FF5900', '#0080FF', '#800000', '#800080',
            '#8000FF', '#808000', '#FF1493', '#8080FF', '#80FF00', '#80FF80', '#80FFFF', '#FF0080',
        );

        $objGraph = new Graph($arrSizes['diagram']['width'], $arrSizes['diagram']['height']);
        $objGraph->SetScale('linlin');
        // Beschriftung auch oben und rechts
        //        $objGraph->SetAxisStyle(AXSTYLE_BOXOUT);

        $theme_class = new UniversalTheme();
        $objGraph->SetTheme($theme_class);
        $objGraph->SetMargin(50, 50, 20, 0); // damit rechts die 16 nicht so abgeschnitten wird

        $objGraph->img->SetAntiAliasing(true); // für den Graphen
        $objGraph->SetBox(false); // kein Rahmen
        $objGraph->SetFrame(false); // kein Rahmen

        // setze selbst die Ticks auf der Y-Achse
        // anhand derer orientiert sich das Grid
        $intMaxY = (int) $this->objModel->objMpdPressureCurveDataRepository->retrieveMaxPressureValues($this->objShip->getMarprimeSerialno(), date(BaseEntity::strDateFormat, $this->objModel->intDateTs));
        if ($intMaxY) {
            // hmm... bei einem 4-Takter (Elsa Oldendorff main Engine) franst das Diagramm nach oben hin aus, wenn nicht min. 140
            $intMaxY = max(140, $intMaxY);
            $arrYMajorTicks = range(0, $intMaxY + 10, 10);
            $arrYMinorTicks = range(5, $intMaxY - 5, 5);
            $objGraph->yaxis->setTickPositions($arrYMajorTicks, $arrYMinorTicks);
        }

        $objGraph->yaxis->HideZeroLabel();
        $objGraph->yaxis->HideLine(false); // Y-Achse nicht verstecken
        $objGraph->yaxis->HideTicks(false, false);
        $objGraph->yaxis->title->Set('pressure [bar]');
        $objGraph->yaxis->title->SetMargin(10);

        $intMinX = 0;
        $intMaxX = 0;
        $boolFoundData = false; // kommt vor bei E.R. Copenhagen 29.01.2015

        for ($intCylNo = 1; $intCylNo <= $this->objEngineParams->cyl_count; $intCylNo++) {
            $objStatement = $this->objModel->objMpdPressureCurveDataRepository->retrievePressureCurveData(null, null, $intCylNo);
            // $objResult = $this->objModel->calculatePressureCurveData($intCylNo);
            if (!$objStatement->RowCount()) {
                // if (!$objResult->count()) {
                continue; // kommt vor bei E.R. Copenhagen 29.01.2015
            }
            $arrResult = $objStatement->fetchAll();
            $boolFoundData = true;
            $arrX = $this->objModel->as_array($arrResult, 'x_val', 'x_val'); //$objResult->as_array('x_val', 'x_val');
            $arrY = $this->objModel->as_array($arrResult, 'x_val', 'y_val'); //$objResult->as_array('x_val', 'y_val');

            if (!$intMinX) {
                $intMinX = min($arrX);
                $intMaxX = max($arrX);
            }

            $sp1 = new LinePlot(array_values($arrY), array_values($arrX));
            $sp1->SetFastStroke();
            $sp1->SetLegend('Cylinder ' . $intCylNo);

            $objGraph->Add($sp1);
            $sp1->SetColor($arrColor[$intCylNo - 1]);
        }

        if (!$boolFoundData) {
            return false;
        }

        // setze die x-Ticks
        if ($this->objEngineParams->strokes == 4) {
            $objGraph->xaxis->setTickPositions(range($intMinX, $intMaxX, 20), range($intMinX - 5, $intMaxX - 5, 5));
        } else {
            $objGraph->xaxis->setTickPositions(range($intMinX, $intMaxX, 10), range($intMinX - 2, $intMaxX - 2, 2));
        }
        $objGraph->xaxis->HideTicks(false, false);
        $objGraph->xgrid->Show();
        $objGraph->xgrid->SetLineStyle("solid");
        $objGraph->legend->SetColumns(8); // Legende soll 8Zyl. nebeneinander packen

        $objGraph->xaxis->title->Set('crank angle [deg]');

        $test = new PlotLine(VERTICAL, 0);
        $test->SetLegend('TDC');
        $objGraph->Add($test);
        // speichere das Diagramm
        $strFilename = tempnam(sys_get_temp_dir(), 'msc_pressure_diagram_');
        $objGraph->Stroke($strFilename);
        // jetzt erst die Überschrift, weil evtl. oben schon Rücksprung, weil keine Daten gefunden
        $this->addHeadline('Pressure curve', 'h2');

        $this->Image($strFilename, '', '', $arrSizes['image']['width'], $arrSizes['image']['height']);
        return true;
    }

    /**
     * Diese Methode zeichnet das Leakage-Diagramm in das PDF
     * @param Database_Result $objResult     -Datenreihe
     * @param array           $arrSizes      - array mit Grössen-Angaben für Diagramm + Bild im PDF:
     *                                       - array(
     *                                       'diagram => array('width' => int, 'height' => int), // pixel
     *                                       'image => array('width' => int, 'height' => int) // mm
     *                                       )
     * @param array           $arrColor      - ind. Array mit Farbdefinitionen für die Bars
     *                                       - array(
     *                                       array(        // für 1. Datenreihe
     *                                       'color' => 'white',
     *                                       'fill_color' => '#0080ff'
     *                                       ),
     *                                       .... // weitere Datenreihen
     *                                       )
     */
    protected function drawLeakageDiagram($objStatement, $arrSizes = array(), $arrColor = array())
    {
        // defaults
        $arrSizes = Arr::merge(array(
            'diagram' => array(
                'width' => 1024,
                'height' => 400,
            ),
            'image' => array(
                'width' => 210,
                'height' => 70,
            ),
        ), $arrSizes);

        $arrColor = Arr::merge(array(
            array(
                'color' => 'white',
                'fill_color' => '#006400',
            ),
            array(
                'color' => 'white',
                'fill_color' => '#1111cc',
            ),
        ), $arrColor);

        $this->addHeadline('Probability of valve leakage', 'h2');

        // Create a graph instance
        $graph = new Graph($arrSizes['diagram']['width'], $arrSizes['diagram']['height']);
//        $graph->SetFrame(FALSE);

        // Specify what scale we want to use,
        // text = txt scale for the X-axis
        // int = integer scale for the Y-axis
        // 0, 100 bedeutet, Y-Achsen-Bereich
        // 0, 0 -> auto detect
        $graph->SetScale('textint', 0, 100, 0, 0);

        $theme_class = new UniversalTheme();
        $graph->SetTheme($theme_class);
        $graph->SetMargin(50, 0, 50, 50);

        // Setup a title for the graph
        //        $graph->title->Set('Sunspot example');

        // Setup titles and X-axis labels
        $graph->xaxis->title->Set('Cylinder');
        // nochmal array_values(), damit ich beim index bei 0 anfange
        $graph->xaxis->SetTickLabels(array_values($this->objModel->as_array($objStatement, 'cyl_no', 'cyl_no')));
        // $graph->xaxis->SetTickLabels(array_values($objResult->as_array('cyl_no', 'cyl_no')));

        // Setup Y-axis title
        $graph->yaxis->title->Set('probability of leakage [%]');
        $graph->yaxis->HideTicks(false, false);
        $graph->yaxis->setTickPositions(range(0, 100, 10), range(2, 98, 2));
        $graph->yaxis->title->SetMargin(10);
        $graph->yaxis->HideZeroLabel();

        $arrGraph = array_map('intval', array_values($this->objModel->as_array($objStatement, 'cyl_no', 'value')));
        // $arrGraph = array_map('intval', array_values($objResult->as_array('cyl_no', 'value')));
        $objBarplot = new BarPlot($arrGraph);
//        $objBarplot->value->Show();
        // Add the plot to the graph
        $graph->Add($objBarplot);

        $objBarplot->SetColor($arrColor[0]['color']);
        /* Farben errechne ich anhand der Werte:
        0 <= x < 40% --> grün
        40% <= x < 80% --> gelb
        sonst rot
         */
        $arrColors = array();
        foreach ($arrGraph as $intValue) {
            if ($intValue < 40) {
                $arrColors[] = Arr::path($this->arrConfig, 'color.green');
                // $arrColors[] = Kohana::$config->load('report.engine.color.green');
            } elseif ($intValue >= 80) {
                $arrColors[] = Arr::path($this->arrConfig, 'color.red');
            } else {
                $arrColors[] = Arr::path($this->arrConfig, 'color.yellow');
            }
        }

        $objBarplot->SetFillColor($arrColors);
//        $objBarplot->SetValuePos('max');
        $objBarplot->value->Show();

        // speichere das Diagramm
        $strFilename = tempnam(sys_get_temp_dir(), 'msc_diagram_');
        $graph->Stroke($strFilename);
        // zeichne das Diagramm ins PDF
        $this->Image($strFilename, '', '', $arrSizes['image']['width'], $arrSizes['image']['height']);
        $this->addY(70);
        $this->legend(array(
            array(
                'color' => Arr::path($this->arrConfig, 'color.green'),
                'x' => 15,
                'label' => 'normal/noncritical',
            ),
            array(
                'color' => Arr::path($this->arrConfig, 'color.yellow'),
                'x' => 65,
                'label' => 'steady observation recommended',
            ),
            array(
                'color' => Arr::path($this->arrConfig, 'color.red'),
                'x' => 135,
                'label' => 'check your valves fore leakage!',
            ),
        ));
    }

}
