<?php

namespace App\Command;

use App\Command\Engine\ReportCommand as EngineReportCommand;
use App\Entity\UsrWeb71\ShipTable as UsrWeb71ShipTable;
use App\Entity\UsrWeb71\Users;
use App\Kohana\Arr;
use App\Service\Maridis\Model\User;
use App\Service\Maridis\Model\Voyage;
use NilPortugues\Sql\QueryBuilder\Builder\GenericBuilder;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Qipsius\TCPDFBundle\Controller\TCPDFController;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use function DeepCopy\deep_copy;

/**
 * Dieser Task führt die Generierung aller monatlichen Reports aus.
 *
 * Als Parameter werden folgende akzeptiert:
 *
 * from_ts           string      "2015-01-01 00:00:00"  sucht in der DB nach Daten >= 01.01.2015 00:00:00
 * to_ts             string      "2015-01-01 23:59:59"  sucht in der DB nach Daten <= 01.01.2015 00:00:00
 * period            string      monthly                wenn weder from_ts noch to_ts gesetzt, dann werden sie wie folgt gesetzt:
 *                                                      from_ts: 1. Tag des letzten Monats 00:00:00
 *                                                      to_ts: Letzter Tag des letzten Monats 23:59:59
 *                               yearly                 from_ts: 1. Tag des letzten Monats 00:00:00 - 12 Monate
 *                                                      to_ts: Letzter Tag des letzten Monats 23:59:59
 *                                                      Diese Option ist nur für CO2-, Performance-, Voyage-Report wichtig
 * imo               string      879546                 hab es hier mit auf aufgenommen, weil Task_Email das als einziger abgeleiteter Task (auser Task:Engine verwenden kann)
 *                                                      Dann werden nur die Reports für dieses Schiff versendet
 * @todo         : imo auch für die Report-Generierung (wo sinnvoll) einbauen
 * email             string      month                  Es werden die in diesem Monat erstellten Reports versendet (sinnvoll für debugging)
 *                               today                  Es werden die heute erstellten Reports erstellt (default bei cron)
 *                               created                Es werden die Reports verschickt, bei denen das create-datum = dem Tag (from_ts) ist (sinnvoll, um reports nachzuschicken)
 *                               ''                     Es werden keine E-Mails verschickt (default)
 * dry_run           string|boolean                      wenn angegeben (auch leer), dann wird nichts in die DB geschrieben und auch keien E-Mail verschickt
 * dry_email         string                             wenn gesetzt, werden alle Nachrichten an diese Adresse geschickt
 * http_host         string      msc.maridis.de         Wird nur benötigt, um Error-E-Mails vernünfigt zu generieren
 *
 *
 *
 * (c)  rolf.staege@lumturo.net
 *
 * @copyright    Copyright (c) 2016 rolf.staege@lumturo.net
 */
class ReportCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'msc:report';

    protected $objContainer = null;

    protected $objPropertyAccess = null; // für einfachen Zugriff auf Arrays

    protected $objLogger = null;

    protected $objSwiftMailer = null;

    /**
     * @var Doctrine\Common\Persistence\ManagerRegistry
     */
    protected $objDoctrineManagerRegistry = null;
    /**
     * @var Doctrine\Commom\Persistance\ObjectManager
     */
    protected $objDoctrineDefaultManager = null;
    protected $objTCPDFController = null;

    protected $objShipTableRepository = null;

    protected $intFromTs = 0;
    protected $intToTs = 0;
    protected $strPeriod = 'monthly';

    protected $objUserService;
    protected $objVoyageService;

    public function __construct(ContainerInterface $container, LoggerInterface $appLogger, \Swift_Mailer $mailer, TCPDFController $tcpdf, User $objUser, Voyage $objVoyageService)
    {
        $this->objUserService = $objUser; //$container->get('maridis.model.user');
        $this->objVoyageService = $objVoyageService; //$container->get('maridis.model.voyage');

        parent::__construct();
        $this->objContainer = $container;
        $this->objLogger = $appLogger;
        $this->objSwiftMailer = $mailer;
        $this->objDoctrineManagerRegistry = $container->get('doctrine');
        $this->objDoctrineDefaultManager = $this->objDoctrineManagerRegistry->getManager();
        $this->objShipTableRepository = $this->objDoctrineDefaultManager->getRepository(UsrWeb71ShipTable::class);
        $this->objTCPDFController = $tcpdf;
        $this->objPropertyAccess = PropertyAccess::createPropertyAccessor();
    }

    protected function configure()
    {
        // ...
        $this->addOption('from_ts', null, InputOption::VALUE_OPTIONAL, '', 0)
            ->addOption('to_ts', null, InputOption::VALUE_OPTIONAL, '', 0)
            ->addOption('period', null, InputOption::VALUE_OPTIONAL, '', 'monthly')
            ->addOption('email', null, InputOption::VALUE_OPTIONAL, '', 'yesterday')
            ->addOption('imo', null, InputOption::VALUE_OPTIONAL, '', 0)
            ->addOption('dry_run', null, InputOption::VALUE_OPTIONAL, '', false)
            ->addOption('dry_email', null, InputOption::VALUE_OPTIONAL, '', '')
            ->addOption('http_host', null, InputOption::VALUE_OPTIONAL, '', 'localhost');
    }

    protected function initReport(InputInterface $input)
    {
        $arrCommandlineParameters = [
            'from_ts' => $input->getOption('from_ts'),
            'to_ts' => $input->getOption('to_ts'),
            'period' => $input->getOption('period'),
            'imo' => $input->getOption('imo'),
            'email' => $input->getOption('email'),
            'dry_run' => $input->getOption('dry_run'),
            'dry_email' => $input->getOption('dry_email'),
            'http_host' => $input->getOption('http_host'),
        ];
        $this->setTimeInterval($arrCommandlineParameters);
        $this->setHttpHost($arrCommandlineParameters);
        return $arrCommandlineParameters;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arrCommandlineParameters = $this->initReport($input);
        $this->sendEmail($this->initReport($input));
        return 0;
    }

    /**
     * setzt from-/to-Timestamp, wenn nicht übergeben.
     *
     * Bei den Engine-Report's wird ein Intervall von einem Tag gesetzt (wenn gar nichts übergeben, dann 'gestriger Tag'.
     * Bei den Vessel/CO2-Reports wird zwischen monatlichen und jährlichen Reports unteschieden.
     * Besonderheit: wenn für jährlichen Report kein from/to übergeben wird, dann wird automatisch 01.01.vorheriges Jahr - 31.12. vorheriges Jahr gesetzt
     *
     *
     * Dazu wird $arrParams['period'] betrachtet:
     * 'monthly' = letzter Monat
     * 'yearly' = letztes Jahr
     *
     * @param array $arrParams
     */
    protected function setTimeInterval($arrParams)
    {
        $this->strPeriod = Arr::get($arrParams, 'period'); // default: monthly
        // $this->strPeriod = $this->objPropertyAccess->getValue($arrParams, '[period]'); // default: monthly
        // $this->intFromTs = strtotime($this->objPropertyAccess->getValue($arrParams, '[from_ts]')); // default: 0
        // $this->intToTs = strtotime($this->objPropertyAccess->getValue($arrParams, '[to_ts]')); // default: 0
        $this->intFromTs = strtotime(Arr::get($arrParams, 'from_ts')); // int oder false
        $this->intToTs = strtotime(Arr::get($arrParams, 'to_ts')); // int oder false

        if (!$this->intFromTs) {
            if (!$this->intToTs) {
                // default für from_ts
                if ($this instanceof EngineReportCommand) { //Task_Engine_Report) {
                    //                    $this->intToTs = strtotime(date('Y-m-d', strtotime('today')) . '00:00:00') - 1; // gestern 23:59:59
                    $this->intToTs = strtotime(date('Y-m-d', strtotime('tomorrow')) . '00:00:00') - 1; // heute 23:59:59
                } else {
                    $this->intToTs = strtotime(date('Y-m-d', strtotime('first day of this month')) . '00:00:00') - 1; // 23:59:59 des letzten Tages des letzten Monats
                }
            }
            // errechne anhand von from_ts nun to_ts
            if ($this instanceof EngineReportCommand) { //Task_Engine_Report) {
                // Parameterwert oder gestern 00:00:00
                $this->intFromTs = $this->intToTs - strtotime('1 day', 0) + 1; //Date::DAY + 1;
            } else {
                switch ($this->strPeriod) {
                    case 'yearly':
                        // wenn jährlich und kein Datum angegeben
                        if (!Arr::get($arrParams, 'from_ts') && !Arr::get($arrParams, 'to_ts')) {
                            // if (!$this->objPropertyAccess->getValue($arrParams, '[from_ts]') && !$this->objPropteryAccess->getValue($arrParams, '[to_ts]')) {
                            $this->intFromTs = mktime(0, 0, 0, 1, 1, (int) date('Y') - 1);
                            $this->intToTs = mktime(0, 0, 0, 12, 31, (int) date('Y') - 1);
                        } else {
                            $this->intFromTs = mktime(0, 0, 0, date('m', $this->intToTs), 1, (int) date('Y', $this->intToTs) - 1);
                        }
                        break;
                    default:
                        $this->intFromTs = mktime(0, 0, 0, date('m', $this->intToTs), 1, date('Y', $this->intToTs));
                        break;
                }
            }
        } else {
            if (!$this->intToTs) {
                if ($this instanceof EngineReportCommand) { //Task_Engine_Report) {
                    $this->intToTs = $this->intFromTs + strtotime('1 day', 0) - 1; //Date::DAY - 1;
                } else {
                    switch ($this->strPeriod) {
                        case 'yearly':
                            $this->intToTs = strtotime('+13 month', $this->intFromTs) - 1; // -1 -> 23:59:59 am tag vorher
                            break;
                        default:
                            $this->intToTs = strtotime('+1 month', $this->intFromTs) - 1; // . ' 00:00:00');
                            break;
                    }
                }
            }
        }
    }

    protected function logForShip(UsrWeb71ShipTable $objShip, $strLevel, $strMessage, $arrValues = [])
    {
        $this->objLogger->{$strLevel}(strtr('IMO=:imo;name=:name : ' . $strMessage,
            [
                ':imo' => $objShip->getImoNo(),
                ':name' => str_pad(substr($objShip->getAktName(), 0, 20), 20, ' ', STR_PAD_RIGHT),
            ] + $arrValues
        ));
    }
    protected function logForUser(Users $objUser, $strLevel, $strMessage, $arrValues = [])
    {
        $this->objLogger->{$strLevel}(strtr('User=:username; id=:user_id : ' . $strMessage,
            [
                ':username' => $objUser->getUsername(),
                ':user_id' => $objUser->getId(),
            ] + $arrValues
        ));
    }

    protected function setHttpHost($objParams)
    {
        $_SERVER['HTTP_HOST'] = $this->objPropertyAccess->getValue($objParams, '[http_host]'); // default: localhost
    }

    /**
     * Diese Methode sendet nun alle Reports per E-Mail
     *
     * @param string $strReportActuality - 'today', dann nur die, die heute erstellt wurden
     *                                   - 'month', dann nur die, die diesen Monat erstellt wurden
     *                                   - 'created', dann nur die, die innerhalb des Intervalls (from_ts/to_ts) erstellt wurden
     */
//    protected function sendEmail($strReportActuality = 'today')
    protected function sendEmail($arrParams = array(), $arrSendReportType = array('fleet' => true, 'vessel' => true, 'co2' => true, 'engine' => true, 'interface-upload' => true))
    {
        /** @var App\Service\Maridis\Model\User $objUserService */
        // $objUserService = $this->objContainer->get('maridis.model.user');
        /** @var App\Service\Maridis\Model\Voyage $objVoyageService */
        // $objVoyageService = $this->objContainer->get('maridis.model.voyage');
        /** @var App\Service\Maridis\Model\Voyage $objVoyageService */
        $objVesselService = $this->objContainer->get('maridis.model.report.performance.vessel');

        // Helper_Email::bootstrapZendFramework();
        $strReportActuality = Arr::get($arrParams, 'email', 'yesterday');

        // authorisierte User
        $arrUsers = $this->objDoctrineDefaultManager->getRepository(Users::class)->findByResourcePrivilege('reports', 'send_via_email');

        # erstelle die Basis-Query, die dann gecloned und weiter spezifiziert wird (für jeden User aber neu)
        $objBuilder = new GenericBuilder();
        $objDbReportQuery = $objBuilder->select('generated_reports');
        // $objDbReportQuery = Jelly::query('Row_Generated_Report');

        // wenn nur zu einer imo-Nummer...
        if (Arr::get($arrParams, 'imo')) {
            $objShip = $this->objShipTableRepository->findOneBy(['imoNo' => $arrParams['imo']]);
            $objDbReportQuery->where()
                ->equals('ship_id', $objShip->getId());
            // $objShip = Model_Ship::byImo($arrParams['imo']);
            // $objDbReportQuery->where('ship', '=', $objShip->id);
        }

        // Zeitstempel, nach dem die Reports erstellt sein müssen
        switch ($strReportActuality) {
            // kreiert in diesem Monat
            case 'month':
                $intCreateTs = strtotime(date('Y-m-d', strtotime('first day of this month')) . '00:00:00');
                $intToCreateTs = strtotime(date('Y-m-d', strtotime('first day of next month')) . '00:00:00');
                $objDbReportQuery->where()
                    ->greaterThanOrEqual('create_ts', $intCreateTs)
                    ->lessThanOrEqual('create_ts', $intToCreateTs);
                break;
            //die Reports, die im Intervall kreiiert wurden
            case 'created':
                $objDbReportQuery->where()
                    ->greaterThanOrEqual('create_ts', $this->intFromTs)
                    ->lessThanOrEqual('create_ts', $this->intToTs);
                break;
            case 'yesterday':
                $intCreateTs = strtotime(date('Y-m-d', strtotime('yesterday')) . '00:00:00');
                $intToCreateTs = strtotime(date('Y-m-d', strtotime('today')) . '00:00:00');
                $objDbReportQuery->where()
                    ->greaterThanOrEqual('create_ts', $intCreateTs)
                    ->lessThanOrEqual('create_ts', $intToCreateTs);
                break;
            default: //today --> kreiert heute
                $intCreateTs = strtotime(date('Y-m-d', strtotime('today')) . '00:00:00');
                $intToCreateTs = strtotime(date('Y-m-d', strtotime('tomorrow')) . '00:00:00');
                $objDbReportQuery->where()
                    ->greaterThanOrEqual('create_ts', $intCreateTs)
                    ->lessThanOrEqual('create_ts', $intToCreateTs);
                break;
        }

        foreach ($arrUsers as $objUser) {
            $this->objUserService->setUser($objUser);
            $boolSendFleetReport = $objUser->isSendFleetReport() & $arrSendReportType['fleet']; //Arr::path($objUser->data, 'reports.performance', FALSE);
            $boolSendVoyageReport = $objUser->isSendVoyageReport() & $arrSendReportType['vessel']; //Arr::path($objUser->data, 'reports.voyage', FALSE);
            $boolSendCO2Report = $objUser->isSendCO2Report() & $arrSendReportType['co2']; //Arr::path($objUser->data, 'reports.co2', FALSE);
            $boolSendEngineReport = $objUser->isSendEngineReport() & $arrSendReportType['engine']; //Arr::path($objUser->data, 'reports.engine', FALSE);
            $boolSendInterfaceUploadReport = $objUser->isSendEngineReport() & $arrSendReportType['interface-upload']; //Arr::path($objUser->data, 'reports.engine', FALSE);

            if (!($boolSendFleetReport || $boolSendVoyageReport || $boolSendCO2Report || $boolSendEngineReport || $boolSendInterfaceUploadReport)) {
//                $this->logForUser($objUser, 'User bekommt keine E-Mail, weil kein Report-Typ in seiner Config angeklickt');
                continue;
            }

            if (Arr::get($arrParams, 'imo')) {
                $arrShips = $this->objVoyageService->getAllShipsForUser(true, [$arrParams['imo']]);
                // $objShipCollection = Model_Voyage_Report::getAllShipsForUser($objUser, true, array($arrParams['imo']));
            } else {
                $arrShips = $this->objVoyageService->getAllShipsForUser(true);
                // $objShipCollection = Model_Voyage_Report::getAllShipsForUser($objUser, true);
            }

            # Voyage-Report/CO2-Report
            # rufe ich sowohl für yearly als auch für monthly auf
            if ($boolSendVoyageReport || $boolSendCO2Report) {
                foreach (array('monthly', 'yearly') as $strPeriod) {
                    $objVesselService->init(Arr::get($arrShips, 0), $this->intFromTs, $this->intToTs);
                    $objVesselService->sendEmailForUser($objUser, $arrShips, deep_copy($objDbReportQuery), $arrParams, $this->objSwiftMailer, $this->objLogger);
                    // $objTask = Minion_Task::factory(array_merge(
                    //     Minion_CLI::options(),
                    //     array(
                    //         'task' => 'Vessel:Performance:Report',
                    //         'email' => $this->_options['email'],
                    //         'period' => $strPeriod,
                    //     )));

                    // setze das Zeitinterval (mache ich sonst in $objTask->_execute)
                    // $objTask->setTimeInterval($objTask->get_options());
                    // $objTask->sendEmailForUser($objUser, $arrShips, clone $objDbReportQuery, $arrParams);
                }
            } elseif (!($boolSendVoyageReport || $boolSendCO2Report)) {

                # Flotten report
                if ($boolSendFleetReport) {
                    foreach (array('monthly', 'yearly') as $strPeriod) {
                        // $objTask = Minion_Task::factory(array_merge(
                        //     Minion_CLI::options(),
                        //     array(
                        //         'task' => 'Fleet:Performance:Report',
                        //         'email' => $this->_options['email'],
                        //         'period' => $strPeriod,
                        //     )));

                        // // setze das Zeitinterval (mache ich sonst in $objTask->_execute)
                        // $objTask->setTimeInterval($objTask->get_options());
                        // $objTask->sendEmailForUser($objUser, clone $objDbReportQuery, $arrParams);
                    }
                }

                # Enginereport
                if ($boolSendEngineReport || $boolSendInterfaceUploadReport) {
//                 if (Arr::get($arrParams, 'imo')) {
                    //                     $objQuery = Jelly::query('Row_Ship')->where('imo_number', '=', $arrParams['imo']);
                    //                 } else {
                    //                     $objQuery = null;
                    //                 }
                    //                 // hole die Favoriten
                    //                 $arrShips = $objUser->getAllowedShips(null, false, null, 'list', true, $objQuery);

//                 if ($arrShips->count()) {
                    //                     $objTask = Minion_Task::factory(array_merge(
                    //                         Minion_CLI::options(),
                    //                         array(
                    //                             'task' => 'Engine:Report',
                    //                             'email' => $this->_options['email'],
                    //                         )));

//                     // setze das Zeitinterval (mache ich sonst in $objTask->_execute)
                    //                     $objTask->setTimeInterval($objTask->get_options());
                    //                     $objTask->sendEmailForUser($objUser, $arrShips, clone $objDbReportQuery, $arrParams, $arrSendReportType);
                    //                 } else {
                    // //                    $this->logForUser($objUser, 'E-Mail mit Engine-Reports NICHT an <:recipient> versendet, weil keine Favoriten zur Selektion gefunden.', array(
                    //                     //                        ':recipient' => $objUser->email
                    //                     //                    ));
                    //                 }
                } else {
                    /*
                //                $this->logForUser($objUser, 'E-Mail mit Engine-Reports NICHT an <:recipient> versendet, weil in seinem Profil nicht aktiviert.', array(
                //                    ':recipient' => $objUser->email
                //                ));
                 */
                }
            }

            // sende jetzt noch alle Engine-Reports an die E-Mail-Adressen der Schiffe
            // $objEngineTask = Minion_Task::factory(array_merge(
            //     Minion_CLI::options(),
            //     array(
            //         'task' => 'Engine:Report',
            //         'email' => $this->_options['email'],
            //     )));

            // setze das Zeitinterval (mache ich sonst in $objTask->_execute)
            // $objEngineTask->sendReportsToShip(clone $objDbReportQuery, $arrParams, $arrSendReportType);

        }
    }
}
