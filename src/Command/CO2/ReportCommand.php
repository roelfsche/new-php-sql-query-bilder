<?php

namespace App\Command\CO2;

use App\Command\ReportCommand as MscReportCommand;
use App\Entity\Marnoon\Voyagereport;
use App\Entity\UsrWeb71\GeneratedReports;
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
 * imo               string      879546        nur für dieses Schiff werden Reports erstellt (so Daten vorhanden)
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
    protected static $defaultName = 'msc:co2-report';

    protected $objVoyageReportsRepository = null;

    protected $objGeneratedReportsRepository = null;

    public function __construct(ContainerInterface $container, LoggerInterface $appLogger, \Swift_Mailer $mailer, TCPDFController $tcpdf)
    {
        parent::__construct($container, $appLogger, $mailer, $tcpdf);

        $this->objVoyageReportsRepository = $this->objDoctrineManagerRegistry
            ->getManager('marnoon')
            ->getRepository(VoyageReport::class);
        $this->objGeneratedReportsRepository = $this->objDoctrineDefaultManager
            ->getRepository(GeneratedReports::class);

    }

    protected function configure()
    {
        // ...
        $this->addOption('from_ts', null, InputOption::VALUE_OPTIONAL, '', null)
            ->addOption('to_ts', null, InputOption::VALUE_OPTIONAL, '', null)
            ->addOption('imo', null, InputOption::VALUE_OPTIONAL, '', 0)
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
            'imo' => $input->getOption('imo'),
            'period' => $input->getOption('period'),
            'dry_run' => $input->getOption('dry_run'),
            'dry_email' => $input->getOption('dry_email'),
            'http_host' => $input->getOption('http_host'),
        ];

        $this->setTimeInterval($arrCommandlineParameters);
        $this->setHttpHost($arrCommandlineParameters);

        if ($arrCommandlineParameters['imo']) {
            $arrShipCollection = $this->objShipTableRepository->findByImoNumber($arrCommandlineParameters['imo']); //array(Model_Ship::byImo(Arr::get($params, 'imo')));
        } else {
            // $objShipCollection = Model_Ship::getAllFromEngineParams($this->intFromTs, Arr::get($params, 'shipping_company', ''));
            $arrShipCollection = $this->objShipTableRepository->findAllForVoyageReport($this->intFromTs);
        }
        if (!$arrShipCollection || !count($arrShipCollection)) {
            return 0; // nichts zu tun
        }

        foreach ($arrShipCollection as $objShip) {
            $this->logForShip($objShip, 'info', 'erstelle CO2-Report (:from_date-:to_date)', array(
                ':from_date' => date('Y-m-d H:i:s', $this->intFromTs),
                ':to_date' => date('Y-m-d H:i:s', $this->intToTs),
            ));

            /** @var GeneratedReport $objDbReport */
            $objDbReport = $this->objGeneratedReportsRepository->findByShip($objShip, $this->strPeriod . '-voyage', date('Y-m', $this->intFromTs), $this->intFromTs, $this->intToTs);

            if ($objDbReport) {
                $this->logForShip($objShip, 'info', 'Vessel-Performance-Report (:from_date-:to_date) bereits erstellt', array(
                    ':from_date' => date('Y-m-d H:i:s', $this->intFromTs),
                    ':to_date' => date('Y-m-d H:i:s', $this->intToTs),
                ));
                if ($this->objContainer->get('kernel')->getEnvironment() == 'prod') {
                    continue;
                }
            }

            // schaue, ob Datensatz vom letzten Tag vorhanden
            $boolHasLastRow = $this->objVoyageReportsRepository->doesExist($objShip->imo_number, $this->intToTs);

            if (!$boolHasLastRow) {
                $this->logForShip($objShip, 'info', 'Letzter Datensatz noch nicht verfügbar; Erstellung noch nicht möglich!');
                continue;
            }

            // erstelle den Report
            // weil Probleme mit Speicher => eigener Prozess
            $strCommand = strtr('nohup :interpreter :script --from=:intFromTs --to=:intToTs --period=:period --ship_id=:ship_id :dryRun --http_host=:http_host &', array(
                ':interpreter' => PHP_BINARY,
                ':script' => $this->objContainer->get('kernel')->getProjectDir() . '/bin/console msc:co2-report-worker',
                ':intFromTs' => $this->intFromTs,
                ':intToTs' => $this->intToTs,
                ':period' => $this->strPeriod,
                ':ship_id' => $objShip->id,
                ':dryRun' => ($arrCommandlineParameters['dry_run'] === false) ? '' : '--dry_run=1',
                ':http_host' => $arrCommandlineParameters['http_host'],
            ));
            echo $strCommand . "\n";

            // $arrOutput = array();
            // $intRet = 0;
            // exec($strCommand, $arrOutput, $intRet);

            // $this->logForShip($objShip, 'info', 'Vessel-Performance-Report (:from_date-:to_date) fertig.', array(
            // // $this->logForShip($objShip, 'info', 'Erstelle -Reports (:from_ts)', array(
            //     ':from_ts' => date('Y-m-d H:i:s', $this->intFromTs),
            // ));

            // if (strlen($arrCommandlineParameters['email']))
            // {
            //     $this->sendEmail($arrCommandlineParameters, array('fleet' => FALSE, 'vessel' => TRUE, 'co2' => FALSE, 'engine' => FALSE));
            // }
        }

        return 0;
    }
}
