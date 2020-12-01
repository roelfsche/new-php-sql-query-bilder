<?php

namespace App\Command\Performance\Fleet;

use App\Command\ReportCommand;
use App\Entity\UsrWeb71\GeneratedReports;
use App\Repository\UsrWeb71\GeneratedReportRepository;
use App\Service\Maridis\Model\User;
use App\Service\Maridis\Model\Voyage;
use App\Service\Maridis\Pdf\Report\Performance\Fleet;
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
    protected static $defaultName = 'msc:performance-fleet-report-worker';

    protected $objPdfReport = null;

    /**
     * @var App\Repository\Maprime\EngineParamsRepository
     */
    protected $objEngineParamsRepository = null;

    /**
     * @var GeneratedReportRepository
     *
     */
    protected $objGeneratedReportsRepository = null;

    /**
     * Voyage-Service
     *
     * @var App\Service\Maridis\Model\Voyage
     */
    protected $objVoyageService = null;

    /**
     * User Service
     *
     * @var App\Service\Maridis\Model\User
     */
    protected $objUserService = null;

    public function __construct(ContainerInterface $container, LoggerInterface $appLogger, \Swift_Mailer $mailer, TCPDFController $tcpdf, User $objUserService, Voyage $objVoyageService)
    {
        parent::__construct($container, $appLogger, $mailer, $tcpdf);

        $this->objGeneratedReportsRepository = $this->objDoctrineManagerRegistry
            ->getManager()
            ->getRepository(GeneratedReports::class);

        $this->objUserService = $objUserService;
        $this->objVoyageService = $objVoyageService;

    }

    protected function configure()
    {
        // ...
        $this->addOption('user_id', null, InputOption::VALUE_REQUIRED, '', 0)
            ->addOption('from', null, InputOption::VALUE_REQUIRED, '', 0)
            ->addOption('to', null, InputOption::VALUE_REQUIRED, '', 0)
            ->addOption('period', null, InputOption::VALUE_OPTIONAL, '', 'monthly')
            ->addOption('dry_run', null, InputOption::VALUE_OPTIONAL, '', false)
            ->addOption('http_host', null, InputOption::VALUE_OPTIONAL, '', 'localhost');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->objDoctrineManagerRegistry->getManager('default');
        $arrCommandlineParameters = [
            'user_id' => $input->getOption('user_id'),
            'from' => $input->getOption('from'),
            'to' => $input->getOption('to'),
            'period' => $input->getOption('period'),
            'dry_run' => $input->getOption('dry_run'),
            'http_host' => $input->getOption('http_host'),
        ];

        $this->setHttpHost($arrCommandlineParameters);

        $intFromTs = (int) $arrCommandlineParameters['from'];
        $intToTs = (int) $arrCommandlineParameters['to'];
        $objUser = $this->objUserService->getRepository()->find($arrCommandlineParameters['user_id']);
        $this->objUserService->setUser($objUser);

        // Schiffe zum User
        $arrShips = $this->objVoyageService->getAllShipsForUser();
        // Hash zum Identifizieren der Flotte (in der DB)
        // Flotte = alle seine Schiffe
        $strHash = $this->objVoyageService->generateHash($arrShips);

        /** @var GeneratedReport $objDbReport */
        $objDbReport = $this->objGeneratedReportsRepository->findFleetReport($strHash, $arrCommandlineParameters['period'] . '-voyage', date('Y-m', $intFromTs));
        // $objDbReport = $this->objGeneratedReportsRepository->findByShip($objShip, $this->strPeriod . '-voyage', date('Y-m', $this->intFromTs), $this->intFromTs, $this->intToTs);

        if (!$objDbReport) {
            $objDbReport = new GeneratedReports();
            // $objDbReport->setShipId($objShip->getId());
            $objDbReport->setType($arrCommandlineParameters['period'] . '-voyage');
            $objDbReport->setPeriod(date('Y-m-d', $intFromTs));
            $objDbReport->setFromTs($intFromTs);
            $objDbReport->setToTs($intToTs);
            $objDbReport->setFilename('');
            $objDbReport->setModifyTs(0);
            $objDbReport->setCreateTs(time());
        }

        $objPdfReport = new Fleet($this->objContainer, $arrShips, $intFromTs, $intToTs);
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

        $objPdfReport->Output('/tmp/fleet-neu.pdf', 'F');

        // $this->logForUser($objUser, 'WORKER::dryRun Flotten-Report für hash :hash erfolgreich erstellt.', array(
        //     ':hash' => $strHash,
        //     ':dryRun' => ($arrCommandlineParameters['dry_run']) ? '[dry_run]:' : '',
        // ));

        return 0;
    }

}
