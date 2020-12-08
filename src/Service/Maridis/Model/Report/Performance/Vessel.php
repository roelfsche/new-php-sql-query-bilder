<?php

namespace App\Service\Maridis\Model\Report\Performance;

use App\Entity\Marnoon\Voyagereport;
use App\Entity\UsrWeb71\ShipTable;
use App\Entity\UsrWeb71\Users;
use App\Kohana\Arr;
use App\Service\Maridis\Model\Report;
use Doctrine\Common\Persistence\ManagerRegistry;
use function DeepCopy\deep_copy;
use Monolog\Logger;
use NilPortugues\Sql\QueryBuilder\Manipulation\Select;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class Vessel extends Report
{
    /**
     * @var
     */
    public $objShip = null;

    /**
     * @var int
     */
    public $intFromTs = 0;

    /**
     * @var int
     */
    public $intToTs = 0;

    /**
     * enthält die Summen über einzelne Felder
     *
     * @var array
     */
    public $arrSum = null;

    /**
     * @var App\Repository\Marnoon\VoyagereportsRepository
     */
    public $objVoyageReportsRepository = null;

    public function __construct(ContainerInterface $objContainer, ManagerRegistry $objDoctrineRegistry, LoggerInterface $objLogger)
    {
        parent::__construct($objContainer, $objDoctrineRegistry, $objLogger);

        /** @var $this->objVoyageReportsRepository App\Repository\Marnoon\VoyagereportsRepository */
        $this->objVoyageReportsRepository = $objDoctrineRegistry
            ->getManager('marnoon')
            ->getRepository(Voyagereport::class);
    }

    /**
     * Daten-initialisierung dieses Services
     *
     * @param Model_Row_Ship $objShip
     * @param int            $intFromTs - Unix-TS: inkl. Intervalluntergrenze
     * @param int            $intToTs   - unix-ts: inkl. Intervallobergrenze
     */
    public function init(ShipTable $objShip, $intFromTs, $intToTs)
    {
        $this->objShip = $objShip;
        $this->intToTs = $intToTs;
        $this->intFromTs = $intFromTs;

        $this->objVoyageReportsRepository->init($objShip, $intFromTs, $intToTs);

        $this->arrSum = array('tas' => 0, 'tar' => 0, 'tap' => 0, 'mas' => 0, 'tm' => 0, 'mar' => 0, 'foc' => 0);

        $this->arrEnginePerformanceValues = array(
            'mainEngine' => array(
                'speed' => array('min' => INF, 'max' => 0, 'avg' => 0),
                'power' => array('min' => INF, 'max' => 0, 'total' => 0, 'avg' => 0, 'count' => 0),
                'foc' => array('min' => INF, 'max' => 0, 'total' => 0),
                'sfoc' => array('min' => INF, 'max' => 0, 'avg' => 0),
                'cyl_oil' => array('min' => INF, 'max' => 0, 'total' => 0, 'avg' => 0),
                'fpi' => array('min' => INF, 'max' => 0, 'avg' => 0),
                'turbo_rpm' => array('min' => INF, 'max' => 0, 'avg' => 0),
            ),
            'auxEngine' => array(
                'power' => array('min' => INF, 'max' => 0, 'total' => 0, 'avg' => 0),
                'foc' => array('min' => INF, 'max' => 0, 'total' => 0, 'avg' => 0),
                'lub_oil' => array('min' => INF, 'max' => 0, 'total' => 0),
                'boiler_foc' => array('min' => INF, 'max' => 0, 'total' => 0, 'avg' => 0),
            ),
        );
    }

    /**
     * Diese Methode holt die DB-Relationen und berechnet gleich die notwendigen Summen/Daten.
     *
     * Sie liefert die Relationen zurück.
     *
     *
     */
    public function calculateData()
    {
        $this->objVoyageReportsRepository->init($this->objShip, $this->intFromTs, $this->intToTs);
        $arrRows = $this->objVoyageReportsRepository->retrieveAllForShip();
        $arrVoyages = array();

        $this->getEngineValuesFromDb();

        if (count($arrRows)) {
            $arrVoyages = $this->compileVoyage($arrRows);

            foreach ($arrVoyages as $objRow) {
                // Durchschnitte für Vessel-Performance
                $this->arrSum['tas'] += $objRow->getTimeatsea();
                $this->arrSum['tar'] += $objRow->getTimeatriver();
                $this->arrSum['tap'] += $objRow->getTimeatport();
                $this->arrSum['mas'] += $objRow->getSeamiles();
                $this->arrSum['tm'] += $objRow->getTheomiles();
                $this->arrSum['mar'] += $objRow->getRivermiles();
                $this->arrSum['foc'] += $objRow->getOverallFuelOilConsumption(); // overall_fuel_oil_consumption;
            }

        }

        // setze nun alle min-Werte, die noch INF sind auf 0
        $this->arrEnginePerformanceValues = $this->resetInfValues($this->arrEnginePerformanceValues);

        return $arrVoyages;
    }

    /**
     * da eine Reise von Hafen 1 zu Hafen 2 über mehrere Einträge geht, "gruppiere" ich sie hier zu einer.
     *
     * @param Voyagereport[] $arrVoyagereports
     * @return array
     */
    public function compileVoyage($arrVoyagereports, $boolTotal = false)
    {
        $arrRet = array();
        $objActRow = new Voyagereport();
        // $objActRow = Jelly::factory('Row_Voyage_Report');

        $intSumSpeedThroughWaterTime = 0;
        $intSumSlipThrouhtWaterTime = 0;

        foreach ($arrVoyagereports as $objVoyagereport) {
            // wenn neue Reise...
            //            if ($objActRow->getVoyfrom() != $objVoyagereport->getVoyfrom() || $objActRow->getVoyto() != $objVoyagereport->getVoyto())
            if (($objActRow->getVoyfrom() != $objVoyagereport->getVoyfrom() || $objActRow->getVoyto() != $objVoyagereport->getVoyto()) && ($boolTotal == false || $objActRow->getId() == 0)) {
                // .. dann Durchschnitte berechnden
                if ($objActRow->getId() != 0) {
                    // Durchschnitte errechnen
                    if ($intSumSpeedThroughWaterTime) {
                        $objActRow->setSpeedthroughwater($objActRow->getSpeedthroughwater() / $intSumSpeedThroughWaterTime);
                    }
                    if ($intSumSlipThrouhtWaterTime) {
                        $objActRow->setSlipthroughwater($objActRow->getSlipthroughwater() / $intSumSlipThrouhtWaterTime);
                    }

                    // merken
                    $arrRet[] = $objActRow;
                }

                $objActRow = $objVoyagereport;
                // sanitize die Werte des ersten Eintrags
                $objActRow->setTimeatsea($this->sanitizeValue($objVoyagereport->getTimeatsea(), 'time_at_sea'));
                $objActRow->setTimeatriver($this->sanitizeValue($objVoyagereport->getTimeatriver(), 'time_at_river'));
                $objActRow->setTimeatport($this->sanitizeValue($objVoyagereport->getTimeatport(), 'time_at_port'));
                $objActRow->setSeamiles($this->sanitizeValue($objVoyagereport->getSeamiles(), 'miles_at_sea'));
                $objActRow->setTheoMiles($this->sanitizeValue($objVoyagereport->getTheoMiles(), 'miles_theoretical'));
                $objActRow->setRivermiles($this->sanitizeValue($objVoyagereport->getRivermiles(), 'miles_at_river'));
                $objActRow->setOverallFuelOilConsumtpion($objVoyagereport->getOverallFuelOilConsumption()); //$objVoyagereport->overall_fuel_oil_consumption;
                $objActRow->setMefueloilconsum($this->sanitizeValue($objVoyagereport->getMefueloilconsum(), 'me_fuel_oil_consumption'));
                $objActRow->setMecyloilinput($this->sanitizeValue($objVoyagereport->getMecyloilinput(), 'me_cyl_oil_input'));
                $objActRow->setAeluboilinput($this->sanitizeValue($objVoyagereport->getAeluboilinput(), 'ae_lub_oil_input'));

                $intSumSpeedThroughWaterTime = 0;
                $intSumSlipThrouhtWaterTime = 0;
            } else {
                // .. sonst Werte aufaddieren
                if ($objActRow->id != $objVoyagereport->id) {
                    $objActRow->addTimeatsea($this->sanitizeValue($objVoyagereport->getTimeatsea(), 'time_at_sea'));
                    $objActRow->addTimeatriver($this->sanitizeValue($objVoyagereport->getTimeatriver(), 'time_at_river'));
                    $objActRow->addTimeatport($this->sanitizeValue($objVoyagereport->getTimeatport(), 'time_at_port'));
                    $objActRow->addSeamiles($this->sanitizeValue($objVoyagereport->getSeamiles(), 'miles_at_sea'));
                    $objActRow->addTheomiles($this->sanitizeValue($objVoyagereport->getTheomiles(), 'miles_theoretical'));
                    $objActRow->addRivermiles($this->sanitizeValue($objVoyagereport->getRivermiles(), 'miles_at_river'));
                    $objActRow->addOverallFuelOilConsumption($objVoyagereport->getOverallFuelOilConsumption()); //$objVoyagereport->overall_fuel_oil_consumption;
                    $objActRow->addMefueloilconsum($this->sanitizeValue($objVoyagereport->getMefueloilconsum(), 'me_fuel_oil_consumption'));
                    $objActRow->addMecyloilinput($this->sanitizeValue($objVoyagereport->getMecyloilinput(), 'me_cyl_oil_input'));
                    $objActRow->addAeluboilinput($this->sanitizeValue($objVoyagereport->getAeluboilinput(), 'ae_lub_oil_input'));
                }

                // avg-Werte: gewichte hier über die Zeit (wert1 * zeitspanne1) + ... + (wertn * zeitspannen) / zeitspanne1 + ... + zeitspannen
                // brauche ich für Speed in Knoten für Tabelle Voyage Data
                if ($this->isValid($objVoyagereport->getTimeatsea(), 'time_at_sea')) {
                    if ($objVoyagereport->getSpeedthroughwater() && $this->isValid($objVoyagereport->getSpeedthroughwater(), 'speed_through_water')) {
                        if ($objActRow->id == $objVoyagereport->id) {
                            $objActRow->setSpeedthroughwater($objVoyagereport->speed_through_water * ($objVoyagereport->getTimeatsea() /* + $objVoyagereport->getTimeatriver()*/));
                        } else {
                            $objActRow->addSpeedthroughwater($objVoyagereport->speed_through_water * ($objVoyagereport->getTimeatsea() /* + $objVoyagereport->getTimeatriver()*/));
                        }
                        $intSumSpeedThroughWaterTime += $objVoyagereport->getTimeatsea(); // + $objVoyagereport->getTimeatriver();
                    }

                    // brauche ich für slip für Tabelle Voyage Data
                    if ($objVoyagereport->slip_through_water && $this->isValid($objVoyagereport->slip_through_water, 'slip_through_water')) {
                        if ($objActRow->id == $objVoyagereport->id) {
                            $objActRow->setSlipthroughwater($objVoyagereport->slip_through_water * ($objVoyagereport->getTimeatsea() /* + $objVoyagereport->getTimeatriver()*/));
                        } else {
                            $objActRow->addSlipthroughwater($objVoyagereport->slip_through_water * ($objVoyagereport->getTimeatsea() /* + $objVoyagereport->getTimeatriver()*/));
                        }
                        $intSumSlipThrouhtWaterTime += $objVoyagereport->getTimeatsea(); // + $objVoyagereport->getTimeatriver();
                    }
                }
            }
        }

        // Durchschnitte für die letzte Reise errechnen
        if ($intSumSpeedThroughWaterTime) {
            $objActRow->setSpeedthroughwater($objActRow->getSpeedthroughwater() / $intSumSpeedThroughWaterTime);
        }
        if ($intSumSlipThrouhtWaterTime) {
            $objActRow->setSlipthroughwater($objActRow->getSlipthroughwater() / $intSumSlipThrouhtWaterTime);
        }

        // merken
        $arrRet[] = $objActRow;
        return $arrRet;
    }

    /**
     * Diese Methode berechnet alle Werte für die Motorentabellen (main engine, aux)
     * @throws Msc_Exception
     */
    public function getEngineValuesFromDb()
    {

        // alle Aggregates pro Feld einzeln, da ich nur die Werte aus der DB holen darf, die innerhalb des Intervalls liegen
        foreach (array(
            array(
                'field_expression' => array(
                    array('MAX(a.mepowercount)', 'mainEngine__power__count'),
                ),
                'constraints' => array(
                    'a.mepoweravg' => 'power',
                ),
            ),
            // Speed min
            array(
                'field_expression' => array(
                    array('MIN(NULLIF(a.mespeedavg, 0))', 'mainEngine__speed__min'),
                    array('MAX(a.mespeedavg)', 'mainEngine__speed__max'),
                ),
                'constraints' => array('a.mespeedavg' => 'speed_rpm'),
            ),
            // Speed avg
            array(
                'field_expression' => array(
                    array('SUM(a.timeatsea * a.mespeedavg) / SUM(a.timeatsea)', 'mainEngine__speed__avg'),
                ),
                'constraints' => array(
                    'a.mespeedavg' => 'speed_rpm',
                    'a.timeatsea' => 'time_at_sea',
                ),
            ),
            array(
                'field_expression' => array(
                    array('MIN(NULLIF(a.mepoweravg, 0))', 'mainEngine__power__min'),
                    array('MAX(a.mepoweravg)', 'mainEngine__power__max'),
                ),
                'constraints' => array('a.mepoweravg' => 'power'),
            ),

            array(
                'field_expression' => array(
                    array('SUM(a.timeatsea * a.mepoweravg) / SUM(a.timeatsea)', 'mainEngine__power__avg'),
                ),
                'constraints' => array(
                    'a.mepoweravg' => 'power',
                    'a.timeatsea' => 'time_at_sea',
                ),
            ),
            array(
                'field_expression' => array(
                    array('MIN(NULLIF(a.mesfoc, 0))', 'mainEngine__sfoc__min'),
                    array('MAX(a.mesfoc)', 'mainEngine__sfoc__max'),
                ),
                'constraints' => array('a.mesfoc' => 'me_specific_fuel_oil_consumption'),
            ),
            array(
                'field_expression' => array(
                    array('SUM(a.timeatsea * a.mesfoc) / SUM(a.timeatsea)', 'mainEngine__sfoc__avg'),
                ),
                'constraints' => array(
                    'a.mesfoc' => 'me_specific_fuel_oil_consumption',
                    'a.timeatsea' => 'time_at_sea',
                ),
            ),
            array(
                'field_expression' => array(
                    array('SUM(a.mefueloilconsum) / 1000', 'mainEngine__foc__total'),
                ),
                'constraints' => array('a.mefueloilconsum' => 'me_fuel_oil_consumption'),
            ),
            array(
                'field_expression' => array(
                    array('MIN(NULLIF(a.mecyloilconsum, 0))', 'mainEngine__cyl_oil__min'),
                    array('MAX(a.mecyloilconsum)', 'mainEngine__cyl_oil__max'),
                ),
                'constraints' => array('a.mecyloilconsum' => 'me_specific_cylinder_oil_consumption'),
            ),
            array(
                'field_expression' => array(
                    array('SUM(a.mecyloilinput)', 'mainEngine__cyl_oil__total'),
                ),
                'constraints' => array('a.mecyloilinput' => 'me_cyl_oil_input'),
            ),
            array(
                'field_expression' => array(
                    array('SUM(a.timeatsea * a.mecyloilconsum) / SUM(a.timeatsea)', 'mainEngine__cyl_oil__avg'),
                ),
                'constraints' => array(
                    'a.mecyloilconsum' => 'me_specific_cylinder_oil_consumption',
                    'a.timeatsea' => 'time_at_sea',
                ),
            ),
            array(
                'field_expression' => array(
                    array('MAX(a.mecyloilconsum)', 'mainEngine__cyl_oil__max'),
                ),
                'constraints' => array('a.mecyloilconsum' => 'me_specific_cylinder_oil_consumption'),
            ),
            array(
                'field_expression' => array(
                    array('MIN(NULLIF(a.fuelpumpindex, 0))', 'mainEngine__fpi__min'),
                    array('MAX(a.fuelpumpindex)', 'mainEngine__fpi__max'),
                ),
                'constraints' => array('a.fuelpumpindex' => 'fuel_pump_index'),
            ),
            array(
                'field_expression' => array(
                    array('SUM(a.timeatsea * a.fuelpumpindex) / SUM(a.timeatsea)', 'mainEngine__fpi__avg'),
                ),
                'constraints' => array(
                    'a.fuelpumpindex' => 'fuel_pump_index',
                    'a.timeatsea' => 'time_at_sea',
                ),
            ),
            array(
                'field_expression' => array(
                    array('MIN(NULLIF(a.meturborpm, 0))', 'mainEngine__turbo_rpm__min'),
                    array('MAX(a.meturborpm)', 'mainEngine__turbo_rpm__max'),
                ),
                'constraints' => array('a.meturborpm' => 'me_turbo_rpm'),
            ),
            array(
                'field_expression' => array(
                    array('SUM(a.timeatsea * a.meturborpm) / SUM(a.timeatsea)', 'mainEngine__turbo_rpm__avg'),
                ),
                'constraints' => array(
                    'a.meturborpm' => 'me_turbo_rpm',
                    'a.timeatsea' => 'time_at_sea',
                ),
            ),
            array(
                'field_expression' => array(
                    array('MIN(NULLIF(a.aepower, 0))', 'auxEngine__power__min'),
                    array('MAX(a.aepower)', 'auxEngine__power__max'),
                    array('SUM(a.aepower)', 'auxEngine__power__total'),
                    array('AVG(NULLIF(a.aepower, 0))', 'auxEngine__power__avg'),
                ),
                'constraints' => array('a.aepower' => 'ae_power'),
            ),
            array(
                'field_expression' => array(
                    array('MIN(NULLIF(a.aesfoc, 0))', 'auxEngine__foc__min'),
                    array('MAX(a.aesfoc)', 'auxEngine__foc__max'),
                    array('AVG(NULLIF(a.aesfoc, 0))', 'auxEngine__foc__avg'),
                ),
                'constraints' => array('a.aesfoc' => 'ae_specific_fuel_oil_consumption'),
            ),
            array(
                'field_expression' => array(
                    array('SUM(a.aefueloilconsum) / 1000', 'auxEngine__foc__total'),
                ),
                'constraints' => array('a.aefueloilconsum' => 'ae_fuel_oil_consumption'),
            ),
            array(
                'field_expression' => array(
                    array('MIN(NULLIF(a.aeluboilinput, 0))', 'auxEngine__lub_oil__min'),
                    array('MAX(a.aeluboilinput)', 'auxEngine__lub_oil__max'),
                    array('SUM(a.aeluboilinput)', 'auxEngine__lub_oil__total'),
                ),
                'constraints' => array('a.aeluboilinput' => 'ae_lub_oil_input'),
            ),
            array(
                'field_expression' => array(
                    array('MIN(NULLIF(a.boilerfuelconsum, 0))', 'auxEngine__boiler_foc__min'),
                    array('MAX(a.boilerfuelconsum)', 'auxEngine__boiler_foc__max'),
                    array('SUM(a.boilerfuelconsum)', 'auxEngine__boiler_foc__total'),
                ),
                'constraints' => array('a.boilerfuelconsum' => 'boiler_fuel_oil_consumption'),
            ),
        ) as $arrQueryConfig) {
            $arrData = $this->objVoyageReportsRepository->retrieveVoyageValues($arrQueryConfig);
            // $arrData = $this->doVoyageReportQuery($arrQueryConfig);
            if (!$arrData) {
                continue;
            }

            foreach ($arrData as $strIndex => $mixedValue) {
                if (strpos($strIndex, '_')) {
                    Arr::set_path($this->arrEnginePerformanceValues, $strIndex, $mixedValue, '__');
                }
            }
        }
    }

    // /**
    //  * Diese Methode holt den max. PowerCount aus der Datenbank
    //  * Wird benötigt, um ME->Power->Total zu errechnen (max diesen Monat - max letzter Monat)
    //  * @return integer
    //  */
    // public function getMaxMePowerCount()
    // {
    //     // Telefonat mit Hans 15.12.16: Da MePowerAvg nicht vom user eingegeben, sondern von SW errechnet wird,
    //     // nehme ich den Wert, um zu checken, ob me_power_count valide

    //     $arrQueryConfig = array(
    //         'field_expression' => array(
    //             array('MAX(a.mepowercount)', 'power_count'),
    //         ),
    //         'constraints' => array(
    //             'a.mepoweravg' => 'power',
    //         ),
    //     );
    //     $arrData = $this->objVoyageReportsRepository->retrieveVoyageValues($arrQueryConfig);
    //     if (!$arrData) {
    //         return false;
    //     }

    //     return (int) $arrData['power_count'];
    // }

    /**
     * Diese Methode sendet für alle Schiffe in der Collection jeweils eine E-Mail an den User mit den Voyage/CO2-Reports.
     *
     * Sie ist protected und wird nur über {@link Task_Report::sendEmail()} aufgerufen
     *
     * @param App\Entity\UsrWeb71\Users $objUser
     * @param App\Entity\UsrWeb71\ShipTable[] $arrShips                         - array
     * @param NilPortugues\Sql\QueryBuilder\Manipulation\Select $objReportQuery - Query-Objek, dass schon den Zeitraum eingeschränkt hat, siehe {@link Task_Report::sendEmail()}
     * @param array $arrParams                                                  - enthält die Parameter, die letztendlich _execute() übergeben wurden
     */
    public function sendEmailForUser(Users $objUser, $arrShips, Select $objReportQuery, $arrParams, \Swift_Mailer $objSwiftMailer, Logger $objLogger)
    {
        $boolSendVoyageReport = $objUser->isSendVoyageReport();
        $boolSendCO2Report = $objUser->isSendCO2Report();
        $strEmailSubjectSuffix = '';

        if (!($boolSendCO2Report || $boolSendVoyageReport)) {
            $this->logForUser($objUser, 'E-Mail mit Vessel-Performance-/CO2-Reports NICHT an <:recipient> versendet, weil in seinem Profil nicht aktiviert.', array(
                ':recipient' => $objUser->email,
            ));
            return false;
        }

        // wenn ich nach dem Kreierungsdatum schaue, dann kann das im letzten Monat liegen, wobei der Report aber in diesem
        // Monat erstellt wurde. Brauche dann nicht nach period selektieren, weil sonst 0 (oder zufällig) ein paar Reports
        // selektiert würden
        if ($arrParams['email'] != 'created') {
            $objReportQuery->where()
                ->equals('period', date('Y-m', $this->intFromTs));
            // $objReportQuery->where('period', '=', date('Y-m', $this->intFromTs));
        }

        $objSubWhere = $objReportQuery->where()
            ->subWhere('OR');
        // $objReportQuery->and_where_open();

        if ($boolSendVoyageReport) {
            $objSubWhere->equals('type', $arrParams['period'] . '-voyage');
            // $objReportQuery->where('type', '=', $this->strPeriod . '-voyage');
            $strEmailSubjectSuffix .= 'Performance';
        }
        if ($boolSendCO2Report) {

            $objSubWhere->equals('type', '=', $arrParams['period'] . '-co2');
            // $objReportQuery->or_where('type', '=', $this->strPeriod . '-co2');
            $strEmailSubjectSuffix .= ((strlen($strEmailSubjectSuffix)) ? '- and ' : '') . 'CO2';
        }

        // $objReportQuery->and_where_close();
        $strEmailSubjectSuffix .= '-Report(s)';

        foreach ($arrShips as $objShip) {
            // foreach ($objShipCollection as $objShip) {
            $objQuery = deep_copy($objReportQuery);
            $objQuery->where()
                ->equals('ship_id', $objShip->id);
            // $objQuery->where('ship', '=', $objShip->id);

            $arrReports = $this->objGeneratedReportsRepository->findBySelect($objQuery);
            // $objReportCollection = $objQuery->execute();
            if (!count($arrReports)) {
                $this->logForShip($objShip, 'info', 'E-Mail mit Vessel-Performance-/CO2-Reports NICHT an :username <:recipient> versendet, weil keine aktuellen vorhanden.', array(
                    ':recipient' => $objUser->getEmail(),
                    ':username' => $objUser->getUsername(),
                ));
                continue;
            }

            // $message = (new \Swift_Message('Hello Email'))
            //     ->setFrom('schnittstellen_test@maridis-support.de')
            //     ->setTo('rolf.staege@lumturo.net')
            //     ->setBody('<h1>test</h1>', 'text/html');
            // $objSwiftMailer->send($message);

            // $mailer->send($email);

            $message = (new \Swift_Message('Hello Email'))
                ->setFrom('schnittstellen_test@maridis-support.de')
                ->setTo('rolf.staege@lumturo.net')
                ->setBody('<h1>test</h1>', 'text/html');

            $objEmail = new Zend_Mail('UTF-8');
//            $objEmail->setType(Zend_Mime::MULTIPART_RELATED)//ALTERNATIVE)
            $objEmail->setType(Zend_Mime::MULTIPART_MIXED) //ALTERNATIVE)
                ->setBodyHtml(View::factory('email/report/html'), 'UTF-8', Zend_Mime::ENCODING_8BIT)
                ->setBodyText(View::factory('email/report/text'), 'UTF-8', Zend_Mime::ENCODING_8BIT)
                ->setFrom('support@maridis.de')
                ->addTo((($arrParams['dry_email'] != '') ? $arrParams['dry_email'] : $objUser->email))
                ->setSubject(strtr(':name - :period_type (:period) :suffix', array(
                    ':name' => $objShip->actual_name,
                    ':period_type' => ucfirst(Arr::get($this->get_options(), 'period', '')), // montly/yearly
                    ':period' => date('Y-m', $this->intFromTs),
                    ':suffix' => $strEmailSubjectSuffix,
                )));

            foreach ($objReportCollection as $objReport) {

                if (strpos($objReport->type, 'co2') !== false) {
                    $strFilename = 'CO2-Monitoring-Report';
                } else {
                    $strFilename = 'Vessel-Performance-Report';
                }
                $objEmail->createAttachment($objReport->getPdfContent(), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, strtr(':filename-:name-:from---:to.pdf', array(
                    ':filename' => $strFilename,
                    ':name' => $objShip->actual_name,
                    ':from' => date('Y-m-d', $this->intFromTs),
                    ':to' => date('Y-m-d', $this->intToTs),
                )));
            }

            //  E-Mail senden
            if ($objEmail->hasAttachments) {
                if (Arr::get($arrParams, 'dry_run', false) === false || $arrParams['dry_email'] != '') {
                    Helper_Email::sendMail($objEmail);
                }
                $this->log($objShip, ':dryRun E-Mail mit :count Vessel-Performance-/CO2-Reports an :username <:recipient> versendet.', array(
                    ':dryRun' => (Arr::get($arrParams, 'dry_run', false) !== false) ? '[dry-run]' : '',
                    ':count' => $objEmail->getPartCount(),
                    ':username' => $objUser->username,
                    ':recipient' => $objUser->email,
                ));

            } else {
                $this->log($objShip, 'E-Mail mit Vessel-Performance-/CO2-Reports NICHT an :username <:recipient> versendet, weil keine aktuellen vorhanden.', array(
                    ':username' => $objUser->username,
                    ':recipient' => $objUser->email,
                ));
            }
        }
    }

}
