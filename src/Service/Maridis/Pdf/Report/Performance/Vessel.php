<?php

namespace App\Service\Maridis\Pdf\Report\Performance;

use App\Entity\UsrWeb71\ShipTable;
use App\Kohana\Arr;
use App\Kohana\Text;
use App\Service\Maridis\Pdf\Report;
use Psr\Container\ContainerInterface;

class Vessel extends Report
{

    protected $objShip = null;

    // Zeile aus der Tabelle -> stdObject
    protected $objEngineParams = null;

    /**
     * Modell, dass alle nötigen Berechnungen anstellt
     * @var $objModel App\Service\Maridis\Model\Report
     */
    public $objModel = null;

    /**
     * Modell, dass alle nötigen Berechnungen anstellt für die vorherige Periode
     * @var $objModel App\Service\Maridis\Model\Report
     */
    public $objPrevModel = null;


    public function __construct(ContainerInterface $objContainer, ShipTable $objShip, $intFromTs, $intToTs)
    {
        parent::__construct($objContainer);
        $this->objShip = $objShip;
        $this->objReedereiService->setShip($objShip);
        // $this->intFromTs = $intFromTs;
        // $this->intToTs = $intToTs;

        $this->objModel = $objContainer->get('maridis.model.report.performance.vessel');
        $this->objModel->init($objShip, $intFromTs, $intToTs);

        // Zeitraum für prev-Model
        $intTimeDiff = $this->objModel->intToTs - $this->objModel->intFromTs;
        $this->objPrevModel = $objContainer->get('maridis.model.report.performance.vessel');
        $this->objPrevModel->init($objShip, $this->objModel->intFromTs - $intTimeDiff, $this->objModel->intFromTs - 1);
    }

    /**
     * Diese Methode berechnet die Daten und druckt die Tabellen
     *
     * @return bool - wenn FALSE, dann keine Daten
     */
    public function create()
    {
        $this->AddPage();
        if (!$this->vesselPerformance()) {
            $this->SetY($this->GetY() + 50);
            $this->Cell(0, 0, 'no data available', 0, false, 'C', 0, '', 0, false, 'T', 'M');
            return false;
        }

        $this->AddPage();
        $this->machineryPerformance();
        $this->Ln();
        $this->auxEnginePerformance();
        return true;
    }

    /**
     * druckt die Tabelle "Vessel Performance"
     *
     * @param $intFromTs
     * @param $intToTs
     * @param $intLastFromTs
     * @param $intLastToTs
     * @throws Kohana_Exception
     */
    protected function vesselPerformance()
    {
        $arrVoyages = $this->objModel->calculateData();
        if (!count($arrVoyages)) {
            return false;
        }
        $arrPrevVoyages = $this->objPrevModel->calculateData();

        $this->addHeadline('Vessel Performance', 'h2');
        $this->addHeadline('Voyage Data', 'h3');
        $this->addY(Arr::path($this->arrReportConfig, 'pdf.default.table.margin_top'));
        // $this->addY(Arr::get(Kohana::$config->load('report.pdf.default.table'), 'margin_top', 0));
        $this->setTableHead(array(
            'Start',
            'Destination',
            'Time at Sea [h]',
            'Time at River [h]',
            'Time at Port [h]',
            'Miles at Sea [nm]',
            'Theo. Miles [nm]',
            'Miles at River [nm]',
            'Speed** [knots]',
            'Slip** [%]',
            'Cargo [MT]',
            'FOC* [t]',
        ), array(11, 12, 7, 8, 7, 8, 9, 9, 8, 7, 7, 7));

        $intFlag = 0;

        foreach ($arrVoyages as $intIndex => $objRow) {
            $intFlag ^= 1; // xor 1
            if (!$this->addTableRow(array(
                $objRow->getVoyfrom(),
                $objRow->getVoyto(),
                ($objRow->getTimeatsea()) ? number_format($objRow->getTimeatsea(), 1, '.', ',') : '0',
                ($objRow->getTimeatriver()) ? number_format($objRow->getTimeatriver(), 1, '.', ',') : '0',
                ($objRow->getTimeatport()) ? number_format($objRow->getTimeatport(), 1, '.', ',') : '0',
                ($objRow->getSeamiles()) ? number_format($objRow->getSeamiles(), 0, '.', ',') : '0',
                ($objRow->getTheomiles()) ? number_format($objRow->getTheomiles(), 0, '.', ',') : '0',
                ($objRow->getRivermiles()) ? number_format($objRow->getRivermiles(), 0, '.', ',') : '0',
                ($objRow->getSpeedthroughwater()) ? number_format($objRow->getSpeedthroughwater(), 1, '.', ',') : '0',
                ($objRow->getSlipthroughwater()) ? number_format($objRow->getSlipthroughwater(), 1, '.', ',') : '0',
                ($objRow->getCargototal()) ? number_format($objRow->getCargototal(), 0, '.', ',') : '0',
                ($objRow->getOverallFuelOilConsumption()) ? number_format($objRow->getOverallFuelOilConsumption(), 0, '.', ',') : '0',
            ), (($intFlag) ? 'table.odd_cell' : 'table.even_cell'))
            ) {
                $this->AddPage();
                // Zeile nochmal, aber odd, d.h. mit Hintergrund Hintergrund
                $this->addTableRow(array(
                    $objRow->getVoyfrom(),
                    $objRow->getVoyto(),
                    ($objRow->getTimeatsea()) ? number_format($objRow->getTimeatsea(), 1, '.', ',') : '0',
                    ($objRow->getTimeatriver()) ? number_format($objRow->getTimeatriver(), 1, '.', ',') : '0',
                    ($objRow->getTimeatport()) ? number_format($objRow->getTimeatport(), 1, '.', ',') : '0',
                    ($objRow->getSeamiles()) ? number_format($objRow->getSeamiles(), 0, '.', ',') : '0',
                    ($objRow->getTheomiles()) ? number_format($objRow->getTheomiles(), 0, '.', ',') : '0',
                    ($objRow->getRivermiles()) ? number_format($objRow->getRivermiles(), 0, '.', ',') : '0',
                    ($objRow->getSpeedthroughwater()) ? number_format($objRow->getSpeedthroughwater(), 1, '.', ',') : '0',
                    ($objRow->getSlipthroughwater()) ? number_format($objRow->getSlipthroughwater(), 1, '.', ',') : '0',
                    ($objRow->getCargototal()) ? number_format($objRow->getCargototal(), 0, '.', ',') : '0',
                    ($objRow->getOverallFuelOilConsumption()) ? number_format($objRow->getOverallFuelOilConsumption(), 0, '.', ',') : '0',
                ), 'table.odd_cell');
                if (!$intFlag) {
                    $intFlag = 1; // wieder auf "weissen Hintergrund" stellen
                }
            }
        }

        $arrSum = $this->objModel->arrSum;
        // TabellenFooter
        $this->addTableRow(array(
            '',
            array(
                'value' => 'Total',
                'config' => 'table.footer_label_cell',
            ),
            ($arrSum['tas']) ? number_format($arrSum['tas'], 1, '.', ',') : '0',
            ($arrSum['tar']) ? number_format($arrSum['tar'], 1, '.', ',') : '0',
            ($arrSum['tap']) ? number_format($arrSum['tap'], 1, '.', ',') : '0',
            ($arrSum['mas']) ? number_format($arrSum['mas'], 0, '.', ',') : '0',
            ($arrSum['tm']) ? number_format($arrSum['tm'], 0, '.', ',') : '0',
            ($arrSum['mar']) ? number_format($arrSum['mar'], 0, '.', ',') : '0',
            '-',
            '-',
            '-',
            ($arrSum['foc']) ? number_format($arrSum['foc'], 0, '.', ',') : '0',
        ), 'table.footer_cell');

        // Tabellenunterschrift
        $this->addY(1);
        $this->addCell('* include AE and Boiler; ** average through water', $this->arrConfig['table_caption']);

        return true;
    }

    /**
     * Engine Performance
     *
     * druckt die Tabelle der Engine-Performance
     *
     * @throws Kohana_Exception
     */
    protected function machineryPerformance()
    {
        $this->addHeadline('Engine Performance', 'h2');
        $this->addHeadline('Main Engine', 'h3');
        $arrData = $this->objModel->arrEnginePerformanceValues;
        $arrPrevData = $this->objPrevModel->arrEnginePerformanceValues;

        $intMePowerTotal = false; // errechne den Wert jetzt
        // $intMePowerCount = $this->objModel->getMaxMePowerCount();
        $intMePowerCount = (int)Arr::path($arrData, 'mainEngine.power.count');
        if ($intMePowerCount) {
            $intMePowerCountPrev = (int)Arr::path($arrPrevData, 'mainEngine.power.count');
            if ($intMePowerCountPrev) {
                $intMePowerTotal = $intMePowerCount - $intMePowerCountPrev;
            } else {
                $intMePowerTotal = false;
            }
        }

        $this->setTableHead(array(
            '',
            'Avg.',
            'Total',
            'Min',
            'Max',
            'Change last Report [%]',
        ), array(20, 16, 16, 16, 16, 16));
        $this->addTableRow(array(
            'Speed [rpm]',
            number_format($arrData['mainEngine']['speed']['avg'], 1, '.', ','),
            '-',
            number_format($arrData['mainEngine']['speed']['min'], 1, '.', ','),
            number_format($arrData['mainEngine']['speed']['max'], 1, '.', ','),
            number_format($this->getRelativeValue($arrData['mainEngine']['speed']['avg'], $arrPrevData['mainEngine']['speed']['avg']), 1, '.', ','),
        ), 'table.odd_cell');
        $this->addTableRow(array(
            'Power [kW]*',
            number_format($arrData['mainEngine']['power']['avg'], 1, '.', ','),
            (($intMePowerTotal) ? number_format($intMePowerTotal) : '-'),
            number_format($arrData['mainEngine']['power']['min'], 1, '.', ','),
            number_format($arrData['mainEngine']['power']['max'], 1, '.', ','),
            number_format($this->getRelativeValue($arrData['mainEngine']['power']['avg'], $arrPrevData['mainEngine']['power']['avg']), 1, '.', ','),
        ), 'table.even_cell');
        $this->addTableRow(array(
            'Fuel [g/kWh]**',
            number_format($arrData['mainEngine']['sfoc']['avg'], 1, '.', ','),
            number_format($arrData['mainEngine']['foc']['total'], 1, '.', ','),
            number_format($arrData['mainEngine']['sfoc']['min'], 1, '.', ','),
            number_format($arrData['mainEngine']['sfoc']['max'], 1, '.', ','),
            number_format($this->getRelativeValue($arrData['mainEngine']['foc']['total'], $arrPrevData['mainEngine']['foc']['total']), 1, '.', ','),
        ), 'table.odd_cell');
        $this->addTableRow(array(
            'Cyl. Oil [g/kWh]***',
            number_format($arrData['mainEngine']['cyl_oil']['avg'], 2, '.', ','),
            number_format($arrData['mainEngine']['cyl_oil']['total'], 1, '.', ','),
            number_format($arrData['mainEngine']['cyl_oil']['min'], 2, '.', ','),
            number_format($arrData['mainEngine']['cyl_oil']['max'], 2, '.', ','),
            number_format($this->getRelativeValue($arrData['mainEngine']['cyl_oil']['total'], $arrPrevData['mainEngine']['cyl_oil']['total']), 1, '.', ','),
        ), 'table.even_cell');
        $this->addTableRow(array(
            'Fuel Pump Index',
            number_format($arrData['mainEngine']['fpi']['avg'], 1, '.', ','),
            '-',
            number_format($arrData['mainEngine']['fpi']['min'], 1, '.', ','),
            number_format($arrData['mainEngine']['fpi']['max'], 1, '.', ','),
            number_format($this->getRelativeValue($arrData['mainEngine']['fpi']['avg'], $arrPrevData['mainEngine']['fpi']['avg']), 1, '.', ','),
        ), 'table.odd_cell');
        $this->addTableRow(array(
            'TC Speed [rpm]',
            number_format($arrData['mainEngine']['turbo_rpm']['avg'], 0, '.', ','),
            '-',
            number_format($arrData['mainEngine']['turbo_rpm']['min'], 0, '.', ','),
            number_format($arrData['mainEngine']['turbo_rpm']['max'], 0, '.', ','),
            number_format($this->getRelativeValue($arrData['mainEngine']['turbo_rpm']['avg'], $arrPrevData['mainEngine']['turbo_rpm']['avg']), 1, '.', ','),
        ), $this->compileConfigs(array('report.pdf.monthly_performance.table.even_cell', 'report.pdf.monthly_performance.border_bottom')));
        // Tabellenunterschrift
        $this->addY(1);
        $this->addCell('*Total in kWh; **Total in tons; ***Total in liter', $this->arrConfig['table_caption']);
    }

    /**
     * druckt die Tabelle der Aux-Engine
     *
     * @throws Kohana_Exception
     */
    protected function auxEnginePerformance()
    {
        $this->addHeadline('Auxiliary', 'h3');

        $arrData = $this->objModel->arrEnginePerformanceValues;
        $arrPrevData = $this->objPrevModel->arrEnginePerformanceValues;

        $this->setTableHead(array(
            '',
            'Avg.',
            'Total',
            'Min',
            'Max',
            'Change last Report [%]',
        ), array(20, 16, 16, 16, 16, 16));

        $this->addTableRow(array(
            'AE Power [kW]*',
            number_format($arrData['auxEngine']['power']['avg'], 1, '.', ','),
//            '-',
            number_format($arrData['auxEngine']['power']['total']),
            number_format($arrData['auxEngine']['power']['min'], 1, '.', ','),
            number_format($arrData['auxEngine']['power']['max'], 1, '.', ','),
            number_format($this->getRelativeValue($arrData['auxEngine']['power']['avg'], $arrPrevData['auxEngine']['power']['avg']), 1, '.', ','),
        ), 'table.odd_cell');
        $this->addTableRow(array(
            'AE Fuel [g/kWh]**',
            number_format($arrData['auxEngine']['foc']['avg'], 1, '.', ','),
            number_format($arrData['auxEngine']['foc']['total'], 1, '.', ','),
            number_format($arrData['auxEngine']['foc']['min'], 1, '.', ','),
            number_format($arrData['auxEngine']['foc']['max'], 1, '.', ','),
            number_format($this->getRelativeValue($arrData['auxEngine']['foc']['avg'], $arrPrevData['auxEngine']['foc']['avg']), 1, '.', ','),
        ), 'table.even_cell');
        $this->addTableRow(array(
            'AE Lub. Oil [kg]',
            '-',
            number_format($arrData['auxEngine']['lub_oil']['total'], 1, '.', ','),
            number_format($arrData['auxEngine']['lub_oil']['min'], 1, '.', ','),
            number_format($arrData['auxEngine']['lub_oil']['max'], 1, '.', ','),
            number_format($this->getRelativeValue($arrData['auxEngine']['lub_oil']['total'], $arrPrevData['auxEngine']['lub_oil']['total']), 1, '.', ','),
        ), 'table.odd_cell');
        $this->addTableRow(array(
            'Boiler Fuel [kg]',
            '-',
            number_format($arrData['auxEngine']['boiler_foc']['total'], 1, '.', ','),
            number_format($arrData['auxEngine']['boiler_foc']['min'], 1, '.', ','),
            number_format($arrData['auxEngine']['boiler_foc']['max'], 1, '.', ','),
            number_format($this->getRelativeValue($arrData['auxEngine']['boiler_foc']['total'], $arrPrevData['auxEngine']['boiler_foc']['total']), 1, '.', ','),
        ), $this->compileConfigs(array('report.pdf.monthly_performance.table.even_cell', 'report.pdf.monthly_performance.border_bottom')));
        // Tabellenunterschrift
        $this->addY(1);
        $this->addCell('*Total in kWh; **Total in tons', $this->arrConfig['table_caption']);
    }
    /**
     * liefert die prozentuale Differenz zurück.
     *
     * @param $floatBaseValue
     * @param $floatSecondValue
     * @return string
     */
    private function getRelativeValue($floatBaseValue, $floatSecondValue)
    {
        if (!$floatBaseValue) {
            return 0;
        }
        return ($floatSecondValue - $floatBaseValue) * 100 / $floatBaseValue;
    }

    public function Header()
    {
        $this->addY(3);
        $strTmpLogoFilename = $this->objReedereiService->getLogoFilePath(null, 144);
        if ($strTmpLogoFilename) {
            $this->Image($strTmpLogoFilename, 0, 5, 0, 20, '', '', '', false, 300, 'R');
        }
        $intSecondColumnStart = 95;
        $this->setCellPaddings(0, 1, 0, 0);
        $this->SetFont('freesans', '', 12, '', true);
        // check, if monthly/yearly
        if (date('Y', $this->objModel->intToTs) == date('Y', $this->objModel->intFromTs)) {
            $strPeriod = 'Monthly';
        } else {
            $strPeriod = 'Yearly';
        }
        $this->Write(0, $strPeriod . '-Performance-Report "' . $this->objShip->getAktName() . '"', '', 0, 'L', true); //, 0, '', 0, FALSE, 'M', 'M');
        $this->SetFont('freesans', '', 10, '', true);
        $this->Write(0, 'Shipping Company: ' . Text::limit_words($this->cleanContent($this->objReedereiService->getReederei()->getCompanyName()), 2, ''), '', 0, 'L', true);
        $this->SetX($intSecondColumnStart);
        $this->Write(0, 'IMO-No.: ' . $this->objShip->getImoNo(), '', 0, 'L', true);
        $this->Write(0, 'Period: ' . date('d/m/Y-H:i', $this->objModel->intFromTs) . ' - ' . date('d/m/Y-H:i', $this->objModel->intToTs), '', 0, 'L');
        $this->SetX($intSecondColumnStart);
        $this->Write(0, 'Report Date: ' . date('d/m/Y'), '', 0, 'L', true);
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
//        $this->Write(0, 'The report service is powered by MARIDIS GmbH.', '', FALSE, 'L', FALSE);
        $this->SetTextColor(0, 0, 255);
        $this->SetX(80);
        $this->Write(0, 'www.maridis.de', 'https://www.maridis.de', false, 'L', false);
        $this->SetTextColor(0, 0, 0);
        $this->Write(0, ' email ', '', false, 'L', false);
        $this->SetTextColor(0, 0, 255);
        $this->Write(0, 'maridis@maridis.de', 'maridis@maridis.de', false, 'L', false);
    }

}
