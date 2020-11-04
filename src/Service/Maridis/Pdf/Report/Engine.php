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

    public function __construct(ContainerInterface $objContainer, ShipTable $objShip, $arrEngineParams, $intCreateTs)
    {
        parent::__construct($objContainer);
        $this->objShip = $objShip;
        $this->objEngineParams = (object)$arrEngineParams;

        $this->objModel = $objContainer->get('maridis.model.report.engine');
        $this->objModel->init($objShip, $this->objEngineParams, $intCreateTs);
    }
}
