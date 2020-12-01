<?php

namespace App\Service\Maridis\Pdf\Report\Performance;

use App\Entity\UsrWeb71\ShipTable;
use App\Kohana\Arr;
use App\Kohana\Text;
use App\Service\Maridis\Pdf\Report;
use Psr\Container\ContainerInterface;

class Fleet extends Report
{

    protected $arrShips;

    /**
     * Modell, dass alle nötigen Berechnungen anstellt
     * 
     * @var $objModel App\Service\Maridis\Model\Report\Performance\Fleet
     */
    public $objModel = null;

    /**
     * Modell, dass alle nötigen Berechnungen anstellt für die vorherige Periode
     * 
     * @var $objModel App\Service\Maridis\Model\Report\Performance\Fleet
     */
    public $objPrevModel = null;


    public function __construct(ContainerInterface $objContainer, $arrShips, $intFromTs, $intToTs)
    {
        parent::__construct($objContainer);

        $this->objModel = $objContainer->get('maridis.model.report.performance.fleet');
        $this->objModel->init($arrShips, $intFromTs, $intToTs);
    }

    /**
     * Diese Methode berechnet die Daten und druckt die Tabellen
     *
     * @return bool - wenn FALSE, dann keine Daten
     */
    public function create()
    {
        $this->AddPage();
        $this->fleetTable();
        return TRUE;
    }

        /**
     * druckt die Tabelle
     *
     * @throws Kohana_Exception
     */
    protected function fleetTable()
    {
        $arrShips = $this->objModel->calculateData('actual_name', 'asc');//, Date::YEAR) ;
        $this->setTableHead(array(
            "No",
            "Vessel",
            "IMO No.",
            "Last Data",
            "FOC ME per Mile [t/nm]",
            "Rank\n" . \TCPDF_FONTS::unichr(8 * 16 + 11) . \TCPDF_FONTS::unichr(9 * 16 + 7),
            "EEOI\n[gCO2/\nt*nm]",
            "Rank\n" . \TCPDF_FONTS::unichr(8 * 16 + 11) . \TCPDF_FONTS::unichr(9 * 16 + 7),
            "SFOC*\n[g/kWh]",
            "Rank\n" . \TCPDF_FONTS::unichr(8 * 16 + 11) . \TCPDF_FONTS::unichr(9 * 16 + 7),
            "FOC ME*\n[t]",
            "FOC Aux.**\n[t]",
            "Cyl. Oil\n[l]",
            "Power***\n[kW]",
            "Cyl. Oil***\n[g/kWh]",
            "Speed***\n[knots]",
            "Sea Miles\n[nM]",
            "Time at\nSea [h]",
            "Time at\nPort [h]"
        ), array(5, 10, 5, 7, 5, 4, 5, 4, 5, 4, 6, 5, 5, 5, 5, 5, 5, 5, 5));

        $intFlag = 0;

        foreach ($arrShips as $intIndex => $arrRow)
        {
            $intFlag ^= 1; // xor 1
            if (!$this->addTableRow(array(
                $intIndex + 1,
                $arrRow['objShip']->getAktName(),
                $arrRow['objShip']->getImoNo(),
                $arrRow['last_date'],
                ($arrRow['foc_per_mile'] == PHP_INT_MAX) ? 'n.a.' : number_format($arrRow['foc_per_mile'] / 1000, 2),
                ($arrRow['foc_per_mile'] == PHP_INT_MAX) ? '-' : $arrRow['foc_per_mile_ranking'],
                ($arrRow['eeoi'] == PHP_INT_MAX) ? 'n.a.' : number_format($arrRow['eeoi']),
                ($arrRow['eeoi'] == PHP_INT_MAX) ? '-' : $arrRow['eeoi_ranking'],
                ($arrRow['sfoc'] == PHP_INT_MAX) ? 'n.a.' : number_format($arrRow['sfoc']),
                ($arrRow['sfoc_ranking'] == PHP_INT_MAX) ? '-' : $arrRow['sfoc_ranking'],
                number_format($arrRow['foc_me'], 0),
                number_format($arrRow['foc_aux'], 0),
                number_format($arrRow['cyl_oil'], 0),
                number_format($arrRow['power'], 0),
                number_format($arrRow['cyl_oil_avg'], 1),
                number_format($arrRow['speed'], 1),
                number_format($arrRow['sea_miles'], 0),
                number_format($arrRow['sum_time_at_sea'], 0),
                number_format($arrRow['time_at_port'], 0)
            ), (($intFlag) ? 'table.odd_cell' : 'table.even_cell')
            )
            )
            {
                $this->AddPage();
                $this->setTableHead(array(
                    "No",
                    "Vessel",
                    "IMO No.",
                    "Last Data",
                    "FOC ME per Mile [t/nm]",
                    "Rank",
                    "EEOI\n[gCO2/\nt*nm]",
                    "Rank",
                    "SFOC*\n[g/kWh]",
                    "Rank",
                    "FOC ME*\n[t]",
                    "FOC Aux.**\n[t]",
                    "Cyl. Oil\n[l]",
                    "Power***\n[kW]",
                    "Cyl. Oil***\n[g/kWh]",
                    "Speed***\n[knots]",
                    "Sea Miles\n[nM]",
                    "Time at\nSea [h]",
                    "Time at\nPort [h]"
                ), array(5, 10, 5, 7, 5, 4, 5, 4, 5, 4, 6, 5, 5, 5, 5, 5, 5, 5, 5));
                // Zeile nochmal, aber odd, d.h. mit Hintergrund Hintergrund
                $this->addTableRow(array(
                    $intIndex + 1,
                    $arrRow['objShip']->getAktName(),
                    $arrRow['objShip']->getImoNo(),
                    $arrRow['last_date'],
                    ($arrRow['foc_per_mile'] == PHP_INT_MAX) ? 'n.a.' : number_format($arrRow['foc_per_mile'] / 1000, 2),
                    ($arrRow['foc_per_mile'] == PHP_INT_MAX) ? '-' : $arrRow['foc_per_mile_ranking'],
                    ($arrRow['eeoi'] == PHP_INT_MAX) ? 'n.a.' : number_format($arrRow['eeoi']),
                    ($arrRow['eeoi'] == PHP_INT_MAX) ? '-' : $arrRow['eeoi_ranking'],
                    ($arrRow['sfoc'] == PHP_INT_MAX) ? 'n.a.' : number_format($arrRow['sfoc']),
                    ($arrRow['sfoc_ranking'] == PHP_INT_MAX) ? '-' : $arrRow['sfoc_ranking'],
                    number_format($arrRow['foc_me'], 0),
                    number_format($arrRow['foc_aux'], 0),
                    number_format($arrRow['cyl_oil'], 0),
                    number_format($arrRow['power'], 0),
                    number_format($arrRow['cyl_oil_avg'], 1),
                    number_format($arrRow['speed'], 1),
                    $arrRow['sea_miles'],
                    $arrRow['sum_time_at_sea'],
                    number_format($arrRow['time_at_port'], 0)
                ), 'table.odd_cell'
                );
                if (!$intFlag)
                {
                    $intFlag = 1; // wieder auf "weissen Hintergrund" stellen
                }
            }
        }
        // Tabellenunterschrift
        $this->addY(2);
//        $this->addCell('* average; ** auxiliary engine and boiler; FOC ME, FOC Aux. and Sea Miles are sum values over the period', $this->arrConfig['table_caption']);
        $this->addCell('* sea passage; ** auxiliary engine and boiler; weighted average', $this->arrConfig['table_caption']);
    }
}
