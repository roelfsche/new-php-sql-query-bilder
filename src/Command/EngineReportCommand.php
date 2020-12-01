<?php

// src/Command/CreateUserCommand.php
namespace App\Command;

use App\Repository\UsrWeb71\ShipTableRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EngineReportCommand extends ReportCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'msc:engine-report';

    // public function __construct(ContainerInterface $container, LoggerInterface $appLogger, \Swift_Mailer $mailer)
    // {
    //     parent::__construct();
    //     $this->objContainer = $container;
    //     $this->objLogger = $appLogger;
    //     $this->objSwiftMailer = $mailer;

    //     $this->objPropertyAccess = PropertyAccess::createPropertyAccessor();
    // }

    protected function configure()
    {
        // ...
        $this->addOption('from_date', null, InputOption::VALUE_OPTIONAL, '', null)
            ->addOption('email', null, InputOption::VALUE_OPTIONAL, '', '')
            ->addOption('imo', null, InputOption::VALUE_OPTIONAL, '', 0)
            ->addOption('shipping_company', null, InputOption::VALUE_OPTIONAL, '', '')
            ->addOption('dry_run', null, InputOption::VALUE_OPTIONAL, '', false)
            ->addOption('dry_email', null, InputOption::VALUE_OPTIONAL, '', '')
            ->addOption('http_host', null, InputOption::VALUE_OPTIONAL, '', 'localhost');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arrCommandlineParameters = [
            'from_date' => $input->getOption('from_date'),
            'imo' => $input->getOption('imo'),
            'shipping_company' => $input->getOption('shipping_company'),
            'dry_run' => $input->getOption('dry_run'),
            'dry_email' => $input->getOption('dry_email'),
            'http_host' => $input->getOption('http_host'),
        ];
        // kopiere das Datum in ts, da in setTimeInterval() danach geschaut wird
        if ($this->objPropertyAccess->getValue($arrCommandlineParameters, '[from_date]')) {
            $arrCommandlineParameters['from_ts'] = $arrCommandlineParameters['[from_date]'] . ' 00:00:00';
        }
        $this->setTimeInterval($arrCommandlineParameters);
        $this->setHttpHost($arrCommandlineParameters);

        if ($arrCommandlineParameters['imo'])
        {
            $objShipCollection = $this->objShipTableRepository->findByImoNumber($arrCommandlineParameters['imo']); //array(Model_Ship::byImo(Arr::get($params, 'imo')));
        }
        else
        {
            // $objShipCollection = Model_Ship::getAllFromEngineParams($this->intFromTs, Arr::get($params, 'shipping_company', ''));
            $objShipCollection = $this->objShipTableRepository->findByDateOrShippingCompany($this->intFromTs, $arrCommandlineParameters['shipping_company']);
        }


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
        $this->strPeriod = $this->objPropertyAccess->getValue($arrParams, '[period]'); // default: monthly
        $this->intFromTs = $this->objPropertyAccess->getValue($arrParams, '[from_ts]'); // default: 0
        $this->intToTs = $this->objPropertyAccess->getValue($arrParams, '[to_ts]'); // default: 0
        // $this->intFromTs = strtotime(Arr::get($arrParams, 'from_ts')); // int oder false
        // $this->intToTs = strtotime(Arr::get($arrParams, 'to_ts')); // int oder false

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
                        // if (!Arr::get($arrParams, 'from_ts') && !Arr::get($arrParams, 'to_ts')) {
                        if (!$this->objPropertyAccess->getValue($arrParams, '[from_ts]') && !$this->objPropteryAccess->getValue($arrParams, '[to_ts]')) {
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
    protected function setHttpHost($objParams)
    {
        $_SERVER['HTTP_HOST'] = $this->objPropertyAccess->getValue($objParams, '[http_host]'); // default: localhost
    }
}
