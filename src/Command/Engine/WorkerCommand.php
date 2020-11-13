<?php

namespace App\Command\Engine;

use App\Command\ReportCommand;
use App\Entity\Marprime\EngineParams;
use App\Service\Maridis\Pdf\Report\Engine;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Qipsius\TCPDFBundle\Controller\TCPDFController;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class WorkerCommand extends ReportCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'msc:engine-report-worker';

    protected $objPdfReport = null;

    /**
     * @var App\Repository\Maprime\EngineParamsRepository
     */
    protected $objEngineParamsRepository = null;

    public function __construct(ContainerInterface $container, LoggerInterface $appLogger, \Swift_Mailer $mailer, TCPDFController $tcpdf)
    {
        parent::__construct($container, $appLogger, $mailer, $tcpdf);
        $this->objEngineParamsRepository = $this->objDoctrineManagerRegistry
            ->getManager('marprime')
            ->getRepository(EngineParams::class);

            // $this->objTCPDFController->setClassName('App\Service\Maridis\Pdf\Report\Engine');

    }

    protected function configure()
    {
        // ...
        $this->addOption('from_date', null, InputOption::VALUE_OPTIONAL, '', 'today')
            ->addOption('ship_id', null, InputOption::VALUE_REQUIRED, '', '')
            ->addOption('dry_run', null, InputOption::VALUE_OPTIONAL, '', false)
            ->addOption('http_host', null, InputOption::VALUE_OPTIONAL, '', 'localhost');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arrCommandlineParameters = [
            'from_date' => $input->getOption('from_date'),
            'ship_id' => $input->getOption('ship_id'),
            'dry_run' => $input->getOption('dry_run'),
            'http_host' => $input->getOption('http_host'),
        ];

        $this->setHttpHost($arrCommandlineParameters);
        $objShip = $this->objShipTableRepository->find($arrCommandlineParameters['ship_id']);
        $intFromCreateTs = strtotime($arrCommandlineParameters['from_date']);

        $arrEngineResults = $this->objEngineParamsRepository->findByMarprimeNumber($objShip->getCleanMarprimeSerialNumber());
        foreach ($arrEngineResults as $arrEngineParams) {
            $objPdfReport = new Engine($this->objContainer, $objShip, $arrEngineParams, $intFromCreateTs);
            $objPdfReport->create();
            $objPdfReport->output('/tmp/report.pdf', 'F');
            // $objPdfReport = $this->objTCPDFController->create($this->objContainer, $objShip, $arrEngineParams, $intFromCreateTs);

        }

        return 0;
    }

}
