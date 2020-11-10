<?php

namespace App\Service\Maridis\Pdf\Report;

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
        $boolDiag = false; // Rückgabewert der Diagrammzeichnungsmethode
        $this->objModel->calculateData();

        $this->objModel->createRemarks();
        // $objHistoryCollection = $this->objModel->objHistoryCollection;  //calculateCompressionData();
        $arrHistory = $this->objModel->arrHistory;
        $objLeakageResult = $this->objModel->calculateLeakageData();
        $this->AddPage();
        $this->statisticOverview();
        $this->AddPage();
        $this->remarks();
        $this->AddPage();
return true;
        //zeichne min-/max-Linien ein
        // Pressure / Max Pressrue
        $arrHorizontalLines = array();
        $floatAvgPressure = Model_Mpd_Measurement_Data::getAveragePressure($this->objShip->marprime_serial_number, $this->objModel->intDateTs);
        $floatMaxPressure = Model_Mpd_Measurement_Data::getAverageMaxPressure($this->objShip->marprime_serial_number, $this->objModel->intDateTs);
        $floatLimitPressure = Model_Engine_Params::getPressureLimit($this->objShip->marprime_serial_number, $this->objModel->intDateTs);
        if ($floatLimitPressure !== false) {
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
            $graph->legend->Add('comp. pressure limit', Kohana::$config->load('report.engine.color.red'));
            $graph->legend->Add('max. pressure limit', Kohana::$config->load('report.engine.color.dark_red'));

            return $graph;
        }, $arrHorizontalLines);

        if ($boolDiag) {
            $this->addY(68);
        }
        $boolDiag |= $this->drawDiagram('Angle max pressure', 'Cylinder', 'angle max pressure [°CA]', array_values($objHistoryCollection->as_array('measurement_num', 'measurement_num')), array(array_map('floatval', array_values($objHistoryCollection->as_array('measurement_num', 'apmax')))));
        if ($boolDiag) {
            $this->AddPage();
        }

        $boolDiag = $this->drawDiagram('Indicated Power', 'Cylinder', 'indicated power [kW]', array_values($objHistoryCollection->as_array('measurement_num', 'measurement_num')), array(array_map('floatval', array_values($objHistoryCollection->as_array('measurement_num', 'ind_power')))));
        if ($boolDiag) {
            $this->addY(68);
        }

        $objLoadBalanceResult = $this->objModel->calculateLoadBalanceData();
        $arrValues = array_map('floatval', array_values($objLoadBalanceResult->as_array('measurement_num', 'load_balance')));
        $boolDiag |= $this->drawDiagram('Load balance', 'Cylinder', 'load balance [%]', array_values($objLoadBalanceResult->as_array('measurement_num', 'measurement_num')), array($arrValues), true, array(), array(), array(), function ($graph) {
            // setze min/max der y-Achse, so dass die Grenze (5) immer zu sehen ist
            $arrPlots = $graph->plots;
            $arrMinMax = $graph->GetPlotsYMinMax($arrPlots);
            $intMax = max(5, max(abs((int) $arrMinMax[0]), (int) $arrMinMax[1])) + 1;
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
        if ($boolDiag) {
            $this->AddPage();
        }

        if ($this->drawPressureCurveDiagram()) {
            $this->AddPage();
        }

        $this->drawLeakageDiagram($objLeakageResult);

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

        foreach ($this->objModel->arrCalculatedHistoryCollection as $objRow) {
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
            'Number',
            'Priority',
            'Concern',
            'Event',
            'Cylinder',
            'Remark',
        ), array(5, 5, 10, 15, 10, 55));
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
                'color' => Arr::path($this->arrConfig, 'color.green'),//(Kohana::$config->load('color.green'),
                'x' => 15,
                'label' => 'Everything is in order',
            ),
            array(
                'color' => Arr::path($this->arrConfig, 'color.yellow'),//(Kohana::$config->load('color.green'),
                // 'color' => Kohana::$config->load('color.yellow'),
                'x' => 65,
                'label' => 'Warning - action is not necessary',
            ),
            array(
                'color' => Arr::path($this->arrConfig, 'color.red'),//(Kohana::$config->load('color.green'),
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
        $arrFont = Arr::get($this->arrConfig, 'legend');//Kohana::$config->load('report.engine.legend');
        $this->SetFont($arrFont['font_family'], $arrFont['font_style'], $arrFont['font_size']);
        foreach ($arrConfig as $arrEntry)
        {
            $intX = Arr::get($arrEntry, 'x', $this->getX());
            $intY = Arr::get($arrEntry, 'y', $this->getY());
            $strLabel = Arr::get($arrEntry, 'label', 'label missing!!!');
            $this->SetX($intX);
            $this->SetY($intY, FALSE);
            $arrColor = $this->decodeHexColor(Arr::get($arrEntry, 'color'));
            $this->Rect($this->GetX(), $this->GetY(), 10, 5, 'F', 0, $arrColor);//$arrStyleGreen, )
            $this->SetX($this->GetX() + 15);
            $this->Write(0, $strLabel);
        }
    }

    public function decodeHexColor($strColorCode, $arrIndizes = array('R', 'G', 'B'))
    {
        $arrRet = sscanf($strColorCode, "\#%02x%02x%02x");
        return array_combine($arrIndizes, $arrRet);
    }

    public function Header()
    {
        $this->addY(3);
        $objShippingCompany = $this->objShip->getReederei();
        if ($objShippingCompany/*->loaded()*/)
        {
            $strTmpLogoFilename = $objShippingCompany->getLogoFilePath(NULL, 144);
            if ($strTmpLogoFilename)
            {
                $this->Image($strTmpLogoFilename, 0, 5, 0, 20, '', '', '', FALSE, 300, 'R');
            }
        }
        $this->setCellPaddings(0, 1, 0, 0);
        $this->SetFont('freesans', '', 12, '', TRUE);
        $this->Write(0, 'Cylinder Indication Report "' . $this->objShip->getAktName() . '"', '', 0, 'L', TRUE);//, 0, '', 0, FALSE, 'M', 'M');
        $this->SetFont('freesans', '', 10, '', TRUE);
        $this->Write(0, 'IMO-No.: ' . $this->objShip->getImoNo(), '', 0, 'L');
        $this->SetX(90);
//        $this->Write(0, 'IMO-No.: ' . $this->objShip->imo_number, '', 0, 'L', TRUE);
        $this->Write(0, 'Report Date: ' . date('d/m/Y'), '', 0, 'L', FALSE);
        $this->SetX(160);
//        $this->Write(0, 'Measurement Date: ' . date('d/m/Y', strtotime($this->objEngineParams->date)), '', 0, 'L', TRUE);
        $this->Write(0, 'Measurement Date: ' . date('d/m/Y', $this->objModel->intDateTs), '', 0, 'L', TRUE);

        $this->Write(0, 'Engine: ' . $this->objEngineParams->engine_name, '', 0, 'L');
        $this->SetX(90);
        $this->Write(0, 'Engine-Type: ' . $this->objEngineParams->engine_type, '', 0, 'L', FALSE);
        $this->SetX(160);
        $this->Write(0, 'Shipping Company: ' . Text::limit_words($this->cleanContent($objShippingCompany->company_name), 2, ''), '', 0, 'L', TRUE);
        $this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array('R' => 192, 'G' => 192, 'B' => 192)));
        $this->Cell(0, 0, '', 'B', 1, 'C', 0, '', 0, FALSE, 'M', 'M');
    }

    public function Footer()
    {
        // Position at 25 mm from bottom
        $this->SetY(-18);
        // Set font
        $this->SetFont('freesans', '', 9);
        $this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array('R' => 0, 'G' => 0, 'B' => 0)));
        // Page number
        $this->Cell(0, 8, 'page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 'T', 1, 'C', 0, '', 0, FALSE, 'T', 'M');
        $this->SetFont('freesans', '', 6);
        $this->SetTextColor(0, 0, 0);
//        $this->addY(1);
//        $this->Write(0, 'Owner of the listed data is the shipping company. The shipping company is responsible for the content. The report service is powered by MARIDIS GmbH.', '', FALSE, 'L', FALSE);
        $this->Write(0, 'Owner of the listed data is the company owning the engine. The company is responsible for the content. The report service is powered by MARIDIS GmbH.', '', FALSE, 'C', TRUE);
        $this->SetTextColor(0, 0, 255);
        $this->SetX(120);
        $this->Write(0, 'www.maridis.de', 'http://www.maridis.de', FALSE, 'L', FALSE);
        $this->SetTextColor(0, 0, 0);
        $this->Write(0, ' email ', '', FALSE, 'L', FALSE);
        $this->SetTextColor(0, 0, 255);
        $this->Write(0, 'maridis@maridis.de', 'maridis@maridis.de', FALSE, 'L', FALSE);
    }


}
