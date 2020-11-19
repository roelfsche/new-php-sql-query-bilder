<?php

namespace App\Service\Maridis\Pdf\Report;

use App\Entity\UsrWeb71\ShipTable;
use App\Kohana\Arr;
use App\Kohana\Text;
use App\Service\Maridis\Pdf\Report;
use Psr\Container\ContainerInterface;

class CO2 extends Report
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

        $this->objModel = $objContainer->get('maridis.model.report.co2');
        $this->objModel->init($objShip, $intFromTs, $intToTs);
    }

    /**
     * Diese Methode berechnet die Daten und druckt die Tabellen
     *
     * @return bool - wenn FALSE, dann keine Daten
     */
    public function create()
    {
        $arrVoyages = $this->objModel->calculateData();
        $this->AddPage();
        $this->firstPage();

        if (count($arrVoyages)) {
            $this->AddPage();
            $this->secondPage($arrVoyages);
            $this->AddPage();
            $this->thirdPage($arrVoyages);
        }
        return true;
    }

    /**
     * erstellt das Deckblatt
     */
    public function firstPage()
    {
        $this->addY(14);
        // Überschrift
        $arrColor = Arr::path($this->arrConfig, 'color.light_gray');
        // $arrColor = Kohana::$config->load('report.co2.color.light_gray');
        $this->SetTextColor(Arr::get($arrColor, 'text_color_red', 0), Arr::get($arrColor, 'text_color_blue', 0), Arr::get($arrColor, 'text_color_green', 0));
//        $this->SetTextColor(Kohana::$config->load('report.co2.color.gray.text_color_red'), Kohana::$config->load('report.co2.color.gray.text_color_green'), Kohana::$config->load('report.co2.color.gray.text_color_blue'));
        //        $this->SetFont('freesans', 'B', 26);
        //        $this->writeHTML('CO<sub>2</sub>-Reporting By MRV regulation');

        $this->addY(10);
        $this->SetFont('freesans', '', 16);
        $intCellWidth = 60;
        $intMargin = 11;
        $this->Cell($intCellWidth, 0, 'Shipping Company:', 0, 0);
        $objShippingCompany = $this->objReedereiService->getReederei();
        // $objShippingCompany = $this->objShip->getShippingCompany();

        $this->Cell(0, 0, $objShippingCompany->getCompanyName(), 0, true);
        $this->addY($intMargin);
        $this->Cell($intCellWidth, 0, 'Address:', 0, 0);
        $this->Cell(0, 0, $objShippingCompany->getCompanyName() . ' ' . $objShippingCompany->getCompanyStreet() . ' ' . $objShippingCompany->getCompanyZip() . ' ' . $objShippingCompany->getCompanyCity(), 0, true);
        // Shipname
        $this->addY($intMargin);
        $this->Cell($intCellWidth, 0, 'Ship name:', 0, 0);
        $this->SetFont('freesans', 'B', 16);
        $this->Cell(0, 0, $this->objShip->getAktName(), 0, true);
        // IMO
        $this->addY($intMargin);
        $this->SetFont('freesans', '', 16);
        $this->Cell($intCellWidth, 0, 'Imo-No.:', 0, 0);
        $this->SetFont('freesans', 'B', 16);
        $this->Cell(0, 0, $this->objShip->getImoNo(), 0, true);
        // Port of registry
        $this->addY($intMargin);
        $this->SetFont('freesans', '', 16);
        $this->Cell($intCellWidth, 0, 'Port of registry:', 0, 0);
        $this->Cell(0, 0, '-', 0, true);
        // Report period
        $this->addY($intMargin);
        $this->SetFont('freesans', '', 16);
        $this->Cell($intCellWidth, 0, 'Report period:', 0, 0);
        $this->Cell(0, 0, date('d/m/Y H:i:s', $this->objModel->intFromTs) . ' - ' . date('d/m/Y H:i:s', $this->objModel->intToTs), 0, true);
    }

    public function secondPage($arrVoyages)
    {
        $this->addY(10);
        $this->addHeadline('Voyage data', 'h2');
        $this->addY(Arr::path($this->arrReportConfig, 'pdf.default.table.margin_top', 0));
        // $this->addY(Arr::get(Kohana::$config->load('report.pdf.default.table'), 'margin_top', 0));
        $this->setTableHead(array(
            'Port of departure',
            'Port of arrival',
            'Departure',
            'Arrival',
            'Time at Port [h]',
            'Time at Sea [h]',
            'Time at River [h]',
            'Distance [nm]',
            'Cargo [MT]',
            'Transport work [Mio. t*nm]',
            'FOC* [t]',
        ), array(10, 10, 9, 9, 9, 9, 9, 9, 9, 9, 8));

        $intSumTimeAtPort = $intSumTimeAtSea = $intSumTimeAtRiver = $intSumDistance = $intSumFoc = 0;
        $intFlag = 0;
        foreach ($arrVoyages as $intIndex => $objRow) {
            $intSumTimeAtPort += $objRow->getTimeatport();
            $intSumTimeAtSea += $objRow->getTimeatsea();
            $intSumTimeAtRiver += $objRow->getTimeatriver();
            $intSumDistance += $objRow->getOverallSeaMiles();
            $intSumFoc += $objRow->getOverallFuelOilConsumption();

            $intFlag ^= 1; // xor 1
            if (!$this->addTableRow(array(
                $objRow->getVoyfrom(),
                $objRow->getVoyto(),
                implode(' ', array($objRow->getDate()->format('Y-m-d'), $objRow->getVoystart())),
                implode(' ', array($objRow->end_date->format('Y-m-d'), $objRow->getVoyend())),
                ($objRow->getTimeatport()) ? number_format($objRow->getTimeatport(), 1, '.', ',') : '0',
                ($objRow->getTimeatsea()) ? number_format($objRow->getTimeatsea(), 1, '.', ',') : '0',
                ($objRow->getTimeatriver()) ? number_format($objRow->getTimeatriver(), 1, '.', ',') : '0',
                number_format($objRow->getOverallSeaMiles(), 0, '.', ','),
                ($objRow->getCargototal()) ? number_format($objRow->getCargototal(), 0, '.', ',') : '0',
                number_format($objRow->getCargototal() * $objRow->getOverallSeaMiles() / 1000000, 0, '.', ','),
                ($objRow->getOverallFuelOilConsumption()) ? number_format($objRow->getOverallFuelOilConsumption(), 0, '.', ',') : '0',
            ), (($intFlag) ? 'table.odd_cell' : 'table.even_cell'))
            ) {
                $this->AddPage();
//                // Zeile nochmal, aber odd, d.h. mit Hintergrund Hintergrund
                $this->addTableRow(array(
                    $objRow->getVoyfrom(),
                    $objRow->getVoyto(),
                    implode(' ', array($objRow->getDate()->format('Y-m-d'), $objRow->getVoystart())),
                    implode(' ', array($objRow->end_date->format('Y-m-d'), $objRow->getVoyend())),
                    ($objRow->getTimeatport()) ? number_format($objRow->getTimeatport(), 1, '.', ',') : '0',
                    ($objRow->getTimeatsea()) ? number_format($objRow->getTimeatsea(), 1, '.', ',') : '0',
                    ($objRow->getTimeatriver()) ? number_format($objRow->getTimeatriver(), 1, '.', ',') : '0',
                    number_format($objRow->getOverallSeaMiles(), 0, '.', ','),
                    ($objRow->getCargototal()) ? number_format($objRow->getCargototal(), 0, '.', ',') : '0',
                    number_format($objRow->getCargototal() * $objRow->getOverallSeaMiles() / 1000000, 0, '.', ','),
                    ($objRow->getOverallFuelOilConsumption()) ? number_format($objRow->getOverallFuelOilConsumption(), 0, '.', ',') : '0',
                ), 'table.odd_cell');
                if (!$intFlag) {
                    $intFlag = 1; // wieder auf "weissen Hintergrund" stellen
                }
            }
        }

        // TabellenFooter
        $arrTableFoodConfig = array(
            '',
            array(
                'value' => 'Total',
                'config' => 'table.footer_label_cell',
            ),
            '',
            '',
            ($intSumTimeAtPort) ? number_format($intSumTimeAtPort, 1, '.', ',') : '0',
            ($intSumTimeAtSea) ? number_format($intSumTimeAtSea, 1, '.', ',') : '0',
            ($intSumTimeAtRiver) ? number_format($intSumTimeAtRiver, 1, '.', ',') : '0',
            ($intSumDistance) ? number_format($intSumDistance, 0, '.', ',') : '0',
            '-',
            '-',
            ($intSumFoc) ? number_format($intSumFoc, 0, '.', ',') : '0',
        );
        if (!$this->addTableRow($arrTableFoodConfig, 'table.footer_cell')) {
            $this->AddPage();
            $this->addTableRow($arrTableFoodConfig, 'table.footer_cell');
        }

        // Tabellenunterschrift
        $this->addY(1);
        $this->addCell('* include ME, AE and Boiler', $this->arrConfig['table_caption']);
    }

    public function ThirdPage($arrVoyages)
    {
        $this->addY(10);
        $this->addHeadline('Detailed data of fuel consumption and emitted CO2', 'h2');
        $this->addY(Arr::path($this->arrReportConfig, 'pdf.default.table.margin_top', 0));
        // $this->addY(Arr::get(Kohana::$config->load('report.pdf.default.table'), 'margin_top', 0));
        $this->setTableHead(array(
            'Port of departure',
            'Port of arrival',
            'FOC ME [t]',
            'CO2-factor',
            'ME CO2-Emission [t]',
            'FOC AE [t]',
            'CO2-factor',
            'AE CO2-Emission [t]',
            'FOC Boiler [t]',
            'CO2-factor',
            'Boiler CO2-Emission [t]',
        ), array(10, 10, 9, 9, 9, 9, 9, 9, 9, 9, 8));

        $intFlag = 0;
        $funcFormat = function ($floatValue) {
            return number_format($floatValue, 1);
        };

        foreach ($arrVoyages as $intIndex => $objRow) {
            $arrCo2 = $objRow->get('co2');
            $intFlag ^= 1; // xor 1
            if (!$this->addTableRow(array(
                $objRow->getVoyfrom(),
                $objRow->getVoyto(),
                implode('/', array_map($funcFormat, array_values($arrCo2['me']))),
                implode('/', array_keys($arrCo2['me'])),
                number_format($this->objModel->arraySum($arrCo2['me']), 1),
                implode('/', array_map($funcFormat, array_values($arrCo2['ae']))),
                implode('/', array_keys($arrCo2['ae'])),
                number_format($this->objModel->arraySum($arrCo2['ae']), 1),
                implode('/', array_map($funcFormat, array_values($arrCo2['boiler']))),
                implode('/', array_keys($arrCo2['boiler'])),
                number_format($this->objModel->arraySum($arrCo2['boiler']), 1),
            ), (($intFlag) ? 'table.odd_cell' : 'table.even_cell')
            )
            ) {
                $this->AddPage();
//                // Zeile nochmal, aber odd, d.h. mit Hintergrund Hintergrund
                $this->addTableRow(array(
                    $objRow->getVoyfrom(),
                    $objRow->getVoyto(),
                    implode('/', array_map($funcFormat, array_values($arrCo2['me']))),
                    implode('/', array_keys($arrCo2['me'])),
                    number_format($this->objModel->arraySum($arrCo2['me']), 1),
                    implode('/', array_map($funcFormat, array_values($arrCo2['ae']))),
                    implode('/', array_keys($arrCo2['ae'])),
                    number_format($this->objModel->arraySum($arrCo2['ae']), 1),
                    implode('/', array_map($funcFormat, array_values($arrCo2['boiler']))),
                    implode('/', array_keys($arrCo2['boiler'])),
                    number_format($this->objModel->arraySum($arrCo2['boiler']), 1),
                ), 'table.odd_cell');
                if (!$intFlag) {
                    $intFlag = 1; // wieder auf "weissen Hintergrund" stellen
                }
            }
        }

        // TabellenFooter
        $arrTableFooterConfig = array(
            '',
            array(
                'value' => 'Total',
                'config' => 'table.footer_label_cell',
            ),
            implode('/', array_map($funcFormat, array_values($this->objModel->arrSum['me']))),
            implode('/', array_keys($this->objModel->arrSum['me'])),
            number_format($this->objModel->arraySum($this->objModel->arrSum['me']), 1),
            implode('/', array_map($funcFormat, array_values($this->objModel->arrSum['ae']))),
            implode('/', array_keys($this->objModel->arrSum['ae'])),
            number_format($this->objModel->arraySum($this->objModel->arrSum['ae']), 1),
            implode('/', array_map($funcFormat, array_values($this->objModel->arrSum['boiler']))),
            implode('/', array_keys($this->objModel->arrSum['boiler'])),
            number_format($this->objModel->arraySum($this->objModel->arrSum['boiler']), 1),
        );
        if (!$this->addTableRow($arrTableFooterConfig, 'table.footer_cell')) {
            $this->AddPage();
            $this->addTableRow($arrTableFooterConfig, 'table.footer_cell');
        }
    }

    /**
     * unterschdl. Header für Seite 1 und folgende
     */
    public function Header()
    {
        if ($this->page == 1) {
            // CO2-Reporting
            $arrCo2Config = Arr::path($this->arrReportConfig, 'pdf.co2');
            $this->AddY(15);
            $this->SetTextColor(Arr::path($arrCo2Config, 'color.dark_blue.text_color_red'), Arr::path($arrCo2Config, 'color.dark_blue.text_color_green'), Arr::path($arrCo2Config, 'color.dark_blue.text_color_blue'));
            $this->SetFont('freesans', 'B', 26);
            $this->writeHTML('CO<sub>2</sub>-Reporting', false);
            $this->addY(3);
            $this->SetX(81);
            $this->SetFont('freesans', '', 16);
            $arrColor = Arr::path($arrCo2Config, 'color.light_gray');
            $this->SetTextColor(Arr::get($arrColor, 'text_color_red', 0), Arr::get($arrColor, 'text_color_blue', 0), Arr::get($arrColor, 'text_color_green', 0));
            $this->Write(0, '[MRV - Ready statement is in progress at your classification society]', '', false, 'L', true);

            $this->AddY(15);
            $this->SetLineStyle(array('width' => 2 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array('R' => Arr::path($arrCo2Config, 'color.light_blue.text_color_red'), 'G' => Arr::path($arrCo2Config, 'color.light_blue.text_color_green'), 'B' => Arr::path($arrCo2Config, 'color.light_blue.text_color_blue'))));
            $this->Cell(0, 0, '', 'T', 1, 'C', 0, '', 0, false, 'M', 'M');
        } else {
            $this->addY(3);

            $strTmpLogoFilename = $this->objReedereiService->getLogoFilePath(null, 144);
            if ($strTmpLogoFilename) {
                $this->Image($strTmpLogoFilename, 0, 5, 0, 20, '', '', '', false, 300, 'R');
            }

            $this->setCellPaddings(0, 1, 0, 0);
            $this->SetFont('freesans', '', 12, '', true);
            $this->Write(0, 'Monitoring-Reporting-Verification (EU-MRV Regulation) ', '', 0, 'L', true); //, 0, '', 0, FALSE, 'M', 'M');
            $this->SetFont('freesans', '', 10, '', true);
            $this->Write(0, 'Shipping Company: ' . Text::limit_words($this->cleanContent($this->objReedereiService->getReederei()->getCompanyName()), 2, ''), '', 0, 'L');
            $this->SetX(90);
            $this->Write(0, 'IMO-No.: ' . $this->objShip->getImoNo(), '', 0, 'L', true);
            $this->Write(0, 'Report Date: ' . date('d/m/Y'), '', 0, 'L');
            $this->SetX(90);
            $this->Write(0, 'Period: ' . date('d/m/Y-H:i', $this->objModel->intFromTs) . ' - ' . date('d/m/Y-H:i', $this->objModel->intToTs), '', 0, 'L', true);
            $this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array('R' => 192, 'G' => 192, 'B' => 192)));
            $this->Cell(0, 0, '', 'B', 1, 'C', 0, '', 0, false, 'M', 'M');
        }
    }

    public function Footer()
    {
        $arrCo2Config = Arr::path($this->arrReportConfig, 'pdf.co2');

        if ($this->page == 1) {
            // Position at 25 mm from bottom
            $this->SetY(-28);
            // Set font
            $this->SetFont('freesans', '', 9);
            $this->SetLineStyle(array('width' => 2 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array('R' => Arr::path($arrCo2Config, 'color.light_blue.text_color_red'), 'G' => Arr::path($arrCo2Config, 'color.light_blue.text_color_green'), 'B' => Arr::path($arrCo2Config, 'color.light_blue.text_color_blue'))));
            $this->Cell(0, 8, 'page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 'T', false, 'C', 0, '', 0, false, 'T', 'M');
//            $this->Cell(0, 0, '', 'T', 1, 'C', 0, '', 0, FALSE, 'T', 'M');

//            $this->addY(3);
            $this->addY(5);
            $this->Write(0, 'The present report based on the Monitoring, Reporting and Verification of Carbon', '', false, 'L', true);
            $this->Write(0, 'Dioxide Emission from Maritime Transport and Amending Regulation (EU) No 525/2013', '', false, 'L', true);
            $this->Write(0, 'published by the European Commission', '', false, 'L', true);

            $this->SetY(-25);
            $intXPos = 180;
            // Maridis Logo: upload/flags/images/co2_report_maridis_logo.png
            $strMaridisLogo = $this->objContainer->get('kernel')->getProjectDir() . '/upload/flags/images/co2_report_maridis_logo.png';
            // $strMaridisLogo = Kohana::find_file('assets', 'images/co2_report_maridis_logo', 'png');
            if ($strMaridisLogo) {
                $this->Image($strMaridisLogo, $intXPos, 185, 25);
            }

            $this->SetFont('freesans', '', 10);
            $this->SetX($intXPos);
            $this->addY(8, false);
            $this->writeHTML('The CO<sub>2</sub>-Report is supported by Maridis');
            $this->SetX($intXPos);
            $this->addY(1, false);
            $this->Write(0, 'Maritime Diagnostic & Service GmbH', '', false, 'L', true);
            // Page number
        } else {
            // Position at 25 mm from bottom
            $this->SetY(-18);
            // Set font
            $this->SetFont('freesans', '', 9);
            $this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array('R' => 0, 'G' => 0, 'B' => 0)));
            // Page number
            $this->Cell(0, 8, 'page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 'T', 1, 'C', 0, '', 0, false, 'T', 'M');
            $this->SetFont('freesans', '', 6);
            $this->SetTextColor(0, 0, 0);
//            $this->addY(1);
            //            $this->Write(0, 'Owner of the listed data is the shipping company. The shipping company is responsible for the content. The report service is powered by MARIDIS GmbH.', '', FALSE, 'L', FALSE);
            $this->Write(0, 'Owner of the listed data is the company owning the engine. The company is responsible for the content. The report service is powered by MARIDIS GmbH.', '', false, 'C', true);
            $this->SetX(120);
            $this->SetTextColor(0, 0, 255);
            $this->Write(0, 'www.maridis.de', 'https://www.maridis.de', false, 'L', false);
            $this->SetTextColor(0, 0, 0);
            $this->Write(0, ' email ', '', false, 'L', false);
            $this->SetTextColor(0, 0, 255);
            $this->Write(0, 'maridis@maridis.de', 'maridis@maridis.de', false, 'L', false);
        }
    }
}
