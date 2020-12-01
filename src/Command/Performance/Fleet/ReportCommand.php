<?php

// src/Command/CreateUserCommand.php
namespace App\Command\Performance\Fleet;

use App\Command\ReportCommand as MscReportCommand;
use App\Entity\Marnoon\Voyagereport;
use App\Entity\UsrWeb71\GeneratedReports;
use App\Entity\UsrWeb71\Users;
use App\Service\Maridis\Model\User;
use App\Service\Maridis\Model\Voyage;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Qipsius\TCPDFBundle\Controller\TCPDFController;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Dieser Task führt die Generierung der Voyage-Reports aus.
 *
 * Dieser Report wird typischerweise immer für einen Monat oder ein Jahr erstellt.
 * Dazu wird zuerst geschaut, ob vom letzten Tag des Intervals ein Datensatz existiert.
 * Solange das nicht der Fall ist, wird auch kein Report erstellt.
 *
 * Als Parameter werden folgende akzeptiert:
 *
 * from_ts           string      "2015-01-01 00:00:00"  sucht in der DB nach Daten >= 01.01.2015 00:00:00
 * to_ts             string      "2015-01-01 23:59:59"  sucht in der DB nach Daten <= 01.01.2015 00:00:00
 * period            string      monthly                wenn weder from_ts noch to_ts gesetzt, dann werden sie wie folgt gesetzt:
 *                                                      from_ts: 1. Tag des letzten Monats 00:00:00
 *                                                      to_ts: Letzter Tag des letzten Monats 23:59:59
 *                               yearly                 from_ts: 1. Tag des letzten Monats 00:00:00 - 12 Monate
 *                                                      to_ts: Letzter Tag des vorherigen Monats im letzten Jahr 23:59:59
 *                                                      Diese Option ist nur für CO2-, Performance-, Voyage-Report wichtig
 * email             string      month                  Es werden die in diesem Monat erstellten Reports versendet (sinnvoll für debugging)
 *                               today                  Es werden die heute erstellten Reports erstellt (default bei cron)
 *                               created                Es werden die Reports verschickt, bei denen das create-datum = dem Tag (from_ts) ist (sinnvoll, um reports nachzuschicken)
 *                               ''                     Es werden keine E-Mails verschickt (default)
 * dry_run           string|boolean                      wenn angegeben (auch leer), dann wird nichts in die DB geschrieben und auch keien E-Mail verschickt
 * dry_email         string                             wenn gesetzt, werden alle Nachrichten an diese Adresse geschickt
 * http_host         string      msc.maridis.de         Wird nur benötigt, um Error-E-Mails vernünfigt zu generieren
 */
class ReportCommand extends MscReportCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'msc:performance-fleet-report';

    protected $objVoyageReportsRepository = null;

    protected $objGeneratedReportsRepository = null;

    protected $objUserRepository = null;

    protected $objUserService = null;
    protected $objVoyageService = null;

    public function __construct(ContainerInterface $container, LoggerInterface $appLogger, \Swift_Mailer $mailer, TCPDFController $tcpdf, User $objUserService, Voyage $objVoyageService)
    {
        parent::__construct($container, $appLogger, $mailer, $tcpdf);

        $this->objUserService = $objUserService;
        $this->objVoyageService = $objVoyageService;

        $this->objVoyageReportsRepository = $this->objDoctrineManagerRegistry
            ->getManager('marnoon')
            ->getRepository(VoyageReport::class);
        $this->objGeneratedReportsRepository = $this->objDoctrineDefaultManager
            ->getRepository(GeneratedReports::class);
        $this->objUserRepository = $this->objDoctrineDefaultManager
            ->getRepository(Users::class);

    }

    protected function configure()
    {
        // ...
        $this->addOption('from_ts', null, InputOption::VALUE_OPTIONAL, '', null)
            ->addOption('to_ts', null, InputOption::VALUE_OPTIONAL, '', null)
            ->addOption('period', null, InputOption::VALUE_OPTIONAL, '', 'monthly')
            ->addOption('email', null, InputOption::VALUE_OPTIONAL, '', '')
            ->addOption('dry_run', null, InputOption::VALUE_OPTIONAL, '', false)
            ->addOption('dry_email', null, InputOption::VALUE_OPTIONAL, '', '')
            ->addOption('http_host', null, InputOption::VALUE_OPTIONAL, '', 'localhost');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arrCommandlineParameters = [
            'from_ts' => $input->getOption('from_ts'),
            'to_ts' => $input->getOption('to_ts'),
            'period' => $input->getOption('period'),
            'dry_run' => $input->getOption('dry_run'),
            'dry_email' => $input->getOption('dry_email'),
            'http_host' => $input->getOption('http_host'),
        ];

        $this->setTimeInterval($arrCommandlineParameters);
        $this->setHttpHost($arrCommandlineParameters);

        $arrUsers = $this->objUserRepository->findByResourcePrivilege('reports', 'send_via_email');
        foreach ($arrUsers as $objUser) {
            $this->logForUser($objUser, 'info', 'Erstelle Fleet_Report');
            $this->objUserService->setUser($objUser);
            // Schiffe zum User
            $arrShips = $this->objVoyageService->getAllShipsForUser();
            // Hash zum Identifizieren der Flotte (in der DB)
            // Flotte = alle seine Schiffe
            $strHash = $this->objVoyageService->generateHash($arrShips);

            // $objDbReport = $this->objGeneratedReportsRepository->findByShip($objShip, )

            // $objDbReport = Jelly::query('Row_Generated_Report')->where('type', '=', $this->strPeriod . '-fleet')
            //     ->where('fleet_hash', '=', $strHash)
            //     ->where('period', '=', date('Y-m', $this->intFromTs))
            //     ->limit(1)
            //     ->execute();

            // if ($objDbReport->loaded())
            // {
            //     $this->logForUser($objUser, 'Fleet-Report bereits erstellt.');
            //     continue;
            // }

            // erstelle den Report
            // weil Probleme mit Speicher => eigener Prozess
            $strCommand = strtr('nohup :interpreter :script --from=:intFromTs --to=:intToTs --user_id=:user_id --period=:period :dryRun --http_host=:http_host &', array(
                ':interpreter' => PHP_BINARY,
                ':script' => $this->objContainer->get('kernel')->getProjectDir() . '/bin/console msc:performance-fleet-report-worker',
                ':intFromTs' => $this->intFromTs,
                ':intToTs' => $this->intToTs,
                ':user_id' => $objUser->getId(),
                ':period' => $arrCommandlineParameters['period'],
                ':dryRun' => ($arrCommandlineParameters['dry_run'] === false) ? '' : '--dry_run=1',
                ':http_host' => $arrCommandlineParameters['http_host']
            ));
            echo $strCommand . "\n";

            // $arrOutput = array();
            // $intRet = 0;
            // exec($strCommand, $arrOutput, $intRet);

            // $this->logForUser($objUser, 'Fleet-Performance-Report fertig.');












        }
        return 0;
    }
}
