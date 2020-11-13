<?php

namespace App\Command\Engine;

use App\Command\ReportCommand;
use App\Entity\Marprime\EngineParams;
use App\Entity\UsrWeb71\GeneratedReports;
use App\Repository\UsrWeb71\GeneratedReportRepository;
use App\Service\Maridis\Pdf\Report\Engine;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Qipsius\TCPDFBundle\Controller\TCPDFController;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WorkerCommand extends ReportCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'msc:engine-report-worker';

    protected $objPdfReport = null;

    /**
     * @var App\Repository\Maprime\EngineParamsRepository
     */
    protected $objEngineParamsRepository = null;

    /**
     * @var GeneratedReportRepository
     *
     */
    protected $objGeneratedReportRepository = null;

    public function __construct(ContainerInterface $container, LoggerInterface $appLogger, \Swift_Mailer $mailer, TCPDFController $tcpdf)
    {
        parent::__construct($container, $appLogger, $mailer, $tcpdf);
        $this->objEngineParamsRepository = $this->objDoctrineManagerRegistry
            ->getManager('marprime')
            ->getRepository(EngineParams::class);

        $this->objGeneratedReportRepository = $this->objDoctrineManagerRegistry
            ->getManager()
            ->getRepository(GeneratedReports::class);

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
        $em = $this->objDoctrineManagerRegistry->getManager('default');
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
        /** @var $objEngineParams EngineParams */
        foreach ($arrEngineResults as $objEngineParams) {
            /** @var GeneratedReport $objDbReport */
            $objDbReport = $this->objGeneratedReportRepository->findByShipAndEngineParams($objShip, $objEngineParams, $intFromCreateTs);

            if ($objDbReport) {
                $this->logForShip($objShip, 'info', 'WORKER: Engine-Report für die Maschine :engine_type bereits am :date erstellt.', array(
                    ':engine_type' => $objEngineParams->getEngineType(),
                    ':date' => date('d.m.Y H:i:s', $objDbReport->create_ts),
                ));
                if ($this->objContainer->get('kernel')->getEnvironment() == 'prod') {
                    continue;
                }
            }

            $objDbReport = new GeneratedReports();
            $objDbReport->setShipId($objShip->getId());
            $objDbReport->setType('engine-' . $objEngineParams->getEngineName() . '_' . $objEngineParams->getEngineType());
            $objDbReport->setPeriod(date('Y-m-d', $intFromCreateTs));
            $objDbReport->setFromTs($intFromCreateTs);
            $objDbReport->setFilename('');
            $objDbReport->setModifyTs(0);
            $objDbReport->setCreateTs(time());

            $objPdfReport = new Engine($this->objContainer, $objShip, $objEngineParams, $intFromCreateTs);
            $objPdfReport->create();

            $objDbReport->setToTs($objPdfReport->objModel->intDateTs);
            if ($arrCommandlineParameters['dry_run'] === false) {
                $objPdfReport->save($objDbReport);
            }
            $em->persist($objDbReport);
            $em->flush();

            $this->logForShip($objShip, 'info', 'WORKER: :dryRun Engine-Report für die Maschine :engine_type zum Datum :date erstellt.', array(
                ':dryRun' => ($arrCommandlineParameters['dry_run']) ? '[dry-run]:' : '',
                ':engine_type' => $objEngineParams->getEngineType(),
                ':date' => date('Y-m-d H:i:s', $objDbReport->getToTs()),
            ));
        }

        return 0;
    }

}
