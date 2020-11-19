<?php

namespace App\Command\Performance\Vessel;

use App\Command\ReportCommand;
use App\Entity\UsrWeb71\GeneratedReports;
use App\Repository\UsrWeb71\GeneratedReportRepository;
use App\Service\Maridis\Pdf\Report\Performance\Vessel;
// use App\Service\Maridis\Model\Report\Performance\Vessel;
// use App\Service\Maridis\Pdf\Report\Engine;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Qipsius\TCPDFBundle\Controller\TCPDFController;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WorkerCommand extends ReportCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'msc:performance-vessel-report-worker';

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
        // $this->objEngineParamsRepository = $this->objDoctrineManagerRegistry
        //     ->getManager('marprime')
        //     ->getRepository(EngineParams::class);

        $this->objGeneratedReportRepository = $this->objDoctrineManagerRegistry
            ->getManager()
            ->getRepository(GeneratedReports::class);

    }

    protected function configure()
    {
        // ...
        $this->addOption('from', null, InputOption::VALUE_REQUIRED, '', 0)
            ->addOption('to', null, InputOption::VALUE_REQUIRED, '', 0)
            ->addOption('period', null, InputOption::VALUE_OPTIONAL, '', 'monthly')
            ->addOption('ship_id', null, InputOption::VALUE_REQUIRED, '', '')
            ->addOption('dry_run', null, InputOption::VALUE_OPTIONAL, '', false)
            ->addOption('http_host', null, InputOption::VALUE_OPTIONAL, '', 'localhost');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->objDoctrineManagerRegistry->getManager('default');
        $arrCommandlineParameters = [
            'from' => $input->getOption('from'),
            'to' => $input->getOption('to'),
            'ship_id' => $input->getOption('ship_id'),
            'period' => $input->getOption('period'),
            'dry_run' => $input->getOption('dry_run'),
            'http_host' => $input->getOption('http_host'),
        ];

        $this->setHttpHost($arrCommandlineParameters);
        $objShip = $this->objShipTableRepository->find($arrCommandlineParameters['ship_id']);

        $intFromTs = (int)$arrCommandlineParameters['from'];
        $intToTs = (int)$arrCommandlineParameters['to'];

        /** @var GeneratedReport $objDbReport */
        // $objDbReport = $this->objGeneratedReportsRepository->findByShip($objShip, $this->strPeriod . '-voyage', date('Y-m', $this->intFromTs), $this->intFromTs, $this->intToTs);

        $objDbReport = new GeneratedReports();
        $objDbReport->setShipId($objShip->getId());
        $objDbReport->setType($arrCommandlineParameters['period'] . '-voyage');
        $objDbReport->setPeriod(date('Y-m-d', $intFromTs));
        $objDbReport->setFromTs($intFromTs);
        $objDbReport->setToTs($intToTs);
        $objDbReport->setFilename('');
        $objDbReport->setModifyTs(0);
        $objDbReport->setCreateTs(time());
        
        $objPdfReport = new Vessel($this->objContainer, $objShip, $intFromTs, $intToTs);
        // $objReport = new Report_Pdf_Vessel_Performance($objShip, $intFromTs, $intToTs, Kohana::$config->load('report.pdf.monthly_performance'));
        // wenn keine Daten in dem Zeitraum
        if (!$objPdfReport->create()) {
            return;
        }

        // $objDbReport->setToTs($objPdfReport->objModel->intDateTs);
        if ($arrCommandlineParameters['dry_run'] === false) {
            $objPdfReport->save($objDbReport);
        }
        $em->persist($objDbReport);
        $em->flush();

        $objPdfReport->Output('/tmp/vessel.pdf', 'F');
        
        $this->logForShip($objShip, 'info', 'WORKER: :dryRun Vessel-Performance-Report erfolgreich erstellt.', array(
            ':dryRun' => ($arrCommandlineParameters['dry_run']) ? '[dry-run]:' : '',
        ));

        return 0;
    }

}
