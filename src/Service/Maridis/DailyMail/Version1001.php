<?php

namespace App\Service\Maridis\DailyMail;

use App\Entity\UsrWeb71\ShipTable as UsrWeb71ShipTable;
use App\Maridis\File\DailyMail;
use stdClass;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Version1001 extends DailyMail
{
    // protected $resFileHandle = null;

    protected $arrSession = null;

    /**
     * übergebe zusätzlich die Daten von @identification_data
     */
    public function __construct(ContainerInterface $objContainer, $resFileHandle, $arrIdentificationData)
    {
        parent::__construct($objContainer, $resFileHandle);

        $objShipRepository = $this->objDoctrineDefaultManager->getRepository(UsrWeb71ShipTable::class);
        $this->objShip = $objShipRepository->findByCdsSerialNumber($arrIdentificationData['cds_serial']);

        // baue das Array so, wie es die Orginal-Schnittstelle verwendete
        $this->arrSession = [
            'dm_err_msg' => '',
            'datasetcounter' => [
                'systemdata' => 0,
                'rundata' => 0,
                'pradata' => 0,
                'wear_data' => 0,
                'alarm_data' => 0,
            ],
            'errorcounter' => [
                'systemdata' => 0,
                'rundata' => 0,
                'pradata' => 0,
                'wear_data' => 0,
                'alarm_data' => 0,
            ],
            'errors' => [
                'System' => [],
                'RUN' => [],
                'PRA' => [],
                'WEAR' => [],
                'ALARM' => [],
            ],
            'alarmlist_data' => [],
            'cds_log' => [],
            'ref_meas_time' => $arrIdentificationData['daily_file_date'],
            'CDS_Serial' => $arrIdentificationData['cds_serial'],
            'IMO_No' => (($arrIdentificationData['imo_nr'] == '-') ? $arrIdentificationData['cds_serial'] : $arrIdentificationData['imo_nr']),
        ];

        if ($this->objShip) {
            $objShipData = new stdClass();
            $objShipData->id = $this->objShip->getId();
            $objShipData->cyl_count = $this->objShip->getCylCount();
            $objShipData->taufnahme = $this->objShip->getTaufname();
            $objShipData->CDS_SerialNo = $this->objShip->getCdsSerialno();

            $this->arrSession['ship_data'] = $objShipData;
        }
    }

    public function getSession(): array
    {
        return $this->arrSession;
    }

    /**
     * Auslesen erster Daten.
     *
     * Ist in der Original-Schnittstelle noch vor der Differenzierung der Versionen aufgerufen.
     */
    public static function identification_data($text)
    {
        $values = [];
        $values = unpack("v1ddc/a10imo_nr/C1serial_count", $text);
        $values = unpack("v1ddc/a10imo_nr/C1serial_count/a" . $values['serial_count'] . "cds_serial/C1ship_name_count", $text);
        $values = unpack("v1ddc/a10imo_nr/C1serial_count/a" . $values['serial_count'] . "cds_serial/C1ship_name_count/a" . $values['ship_name_count'] . "ship_name/C1hull_no_count", $text);
        $values = unpack("v1ddc/a10imo_nr/C1serial_count/a" . $values['serial_count'] . "cds_serial/C1ship_name_count/a" . $values['ship_name_count'] . "ship_name/C1hull_no_count/a" . $values['hull_no_count'] . "hull_no/C1engine_name_count", $text);
        $values = unpack("v1ddc/a10imo_nr/C1serial_count/a" . $values['serial_count'] . "cds_serial/C1ship_name_count/a" . $values['ship_name_count'] . "ship_name/C1hull_no_count/a" . $values['hull_no_count'] . "hull_no/C1engine_name_count/a" . $values['engine_name_count'] . "engine_name/V1daily_file_date/V1last_config_changed", $text);
        // hatte bei einigen Schiffen ein ^@ bzw. \0 am Stringende
        $values['cds_serial'] = trim($values['cds_serial']);

        if ("02080531" == $values['cds_serial']) {
            $values['cds_serial'] = '02080572';
        }

        /*
        $this->arrSession['CDS_Serial'] = $values['cds_serial'];
        echo "Last config changed: " . date("Y-m-d H:i:s", $values['last_config_changed']) . "\n";
        if ("-" == $values['imo_nr']) {
        $this->arrSession['IMO_No'] = $values['cds_serial'];
        } else {
        $this->arrSession['IMO_No'] = $values['imo_nr'];
        }

        $this->arrSession['ref_meas_time'] = $values['daily_file_date'];
        $sql_string = "SELECT * FROM ship_table WHERE CDS_SerialNo ='" . trim($values['cds_serial']) . "' LIMIT 1";
        //      mysql_select_db($this->arrSession['DB2']);
        $result = mysql_query($sql_string, $this->arrSession['DB_CONNECTION']['usr_web7_1']);
        unset($this->arrSession['error_code']);
        if (!$result) {
        $this->arrSession['error_code'] = "Unbekannte CDS-Seriennummer!";
        } else {
        while ($row = mysql_fetch_object($result)) {
        $this->arrSession['ship_data'] = $row;
        }

        }
         */
        return $values;
    }

    public function process()
    {
        while (!feof($this->resFileHandle)) {
            $text = fread($this->resFileHandle, 21);
            if (!feof($this->resFileHandle)) {
                switch ($text) {
                    case (strpos($text, "##_SYSTEM") !== false):
                        fseek($this->resFileHandle, -21, SEEK_CUR);
                        fseek($this->resFileHandle, strpos($text, "##_SYSTEM") + 10, SEEK_CUR);
                        $intMaxSystemTs = $this->system_data($this->resFileHandle);
                        $this->intMaxMeasTs = max($this->intMaxMeasTs, $intMaxSystemTs);
                        break;
                    case (strpos($text, "##_RUN") !== false):
                        fseek($this->resFileHandle, -21, SEEK_CUR);
                        fseek($this->resFileHandle, strpos($text, "##_RUN") + 7, SEEK_CUR);
                        $intMaxRunTs = $this->run_data();
                        $this->intMaxMeasTs = max($this->intMaxMeasTs, $intMaxRunTs);
                        break;
                    case (strpos($text, "##_PRA") !== false):
                        fseek($this->resFileHandle, -21, SEEK_CUR);
                        fseek($this->resFileHandle, strpos($text, "##_PRA") + 7, SEEK_CUR);
                        $intMaxPraTs = $this->pra_data();
                        $this->intMaxMeasTs = max($this->intMaxMeasTs, $intMaxPraTs);
                        break;
                    case (strpos($text, "##_WEAR") !== false):
                        fseek($this->resFileHandle, -21, SEEK_CUR);
                        fseek($this->resFileHandle, strpos($text, "##_WEAR") + 8, SEEK_CUR);
                        $intMaxWearTs = $this->wear_data();
                        $this->intMaxMeasTs = max($this->intMaxMeasTs, $intMaxWearTs);
                        break;
                    case (strpos($text, "##_ALARM") !== false):
                        fseek($this->resFileHandle, -21, SEEK_CUR);
                        fseek($this->resFileHandle, strpos($text, "##_ALARM") + 9, SEEK_CUR);
                        $this->alarm_data();
                        break;
                    case (strpos($text, "##_ERROR") !== false):
                        fseek($this->resFileHandle, -21, SEEK_CUR);
                        fseek($this->resFileHandle, strpos($text, "##_ERROR") + 9, SEEK_CUR);
                        $this->error_data();
                        break;
                    case (strpos($text, "##_CDSLOG") !== false):
                        fseek($this->resFileHandle, -21, SEEK_CUR);
                        fseek($this->resFileHandle, strpos($text, "##_CDSLOG") + 10, SEEK_CUR);
                        $this->cdslog_data();
                        break;
                    default:
                        // echo "Nix gefunden. - " . $text . "\n";
                        break;
                }
            }
        }
        return $this->intMaxMeasTs;
    }
    protected function system_data()
    {
        $intMaxMeasureTs = 0; // grösster Messzeitpunkt
        $error_array = [];
        $sql_add_system = "";
        $sql_add_exhaust_temps = "";
        $this->arrSession['datasetcounter']['system_data'] = 0;
        $this->arrSession['errorcounter']['system_data'] = 0;
        // $this->arrSession['datasetcounter'][system_data] = 0;
        // $this->arrSession['errorcounter'][system_data] = 0;
        $test = fread($this->resFileHandle, 2);
        while (!feof($this->resFileHandle) && hex2bin('a7a7') === $test) //'��' === $test)
        {
            $text = '';
            $this->arrSession['datasetcounter']['system_data']++;
            for ($i = 1; $i <= 17; $i++) {
                $text .= fread($this->resFileHandle, 1);
            }

            $value = unpack("V1measure_time/V1running_hours/s1revolution/v1torque/v1fuel_consumption/v1speed_over_ground/C1counter", $text);
            $intMaxMeasureTs = max($value['measure_time'], $intMaxMeasureTs);
            for ($i = 1; $i <= (2 * $value['counter']); $i++) {
                $text .= fread($this->resFileHandle, 1);
            }

            $value = unpack("V1measure_time/V1running_hours/s1revolution/v1torque/v1fuel_consumption/v1speed_over_ground/C1counter/s" . $value['counter'] . "exhaust_temperature", $text);
            $value['revolution'] /= 10;
            //$value['fuel_consumption'] *=1000;
            $value['speed_over_ground'] /= 10;
            if ($value['measure_time'] < ($this->arrSession['ref_meas_time'] - 172800) || $value['measure_time'] > ($this->arrSession['ref_meas_time'] + 172800)) {
                $error_array[date("Y-m-d H:i:s", $value['measure_time'])][] = "Messzeit fehlerhaft";
            }

            if ($value['revolution'] < 10 || $value['revolution'] > 200) {
                $error_array[date("Y-m-d H:i:s", $value['measure_time'])][] = "Drehzahl fehlerhaft: " . $value['revolution'];
            }

            if ($value['torque'] < 0 || $value['torque'] > 10000) {
                $error_array[date("Y-m-d H:i:s", $value['measure_time'])][] = "Drehmoment fehlerhaft: " . $value['torque'];
            }

            for ($i = 1; $i <= $value['counter']; $i++) {
                $temp_string = "exhaust_temperature" . $i;
                if ($value[$temp_string] < 50 || $value[$temp_string] > 600) {
                    $error_array[date("Y-m-d H:i:s", $value['measure_time'])][] = "Abgastemperatur " . $i . " fehlerhaft: " . $value['$temp_string'];
                }

            }
            if (!empty($error_array)) {
                $this->arrSession['errors']['System'][] = $error_array;
                $this->arrSession['errorcounter']['system_data']++;
                unset($error_array);
            }
            $sql_add_system .= "('" . $this->arrSession['CDS_Serial'] . "', 'ME_0', '" . date("Y-m-d  H:i:s", $value['measure_time']) . "', 'DDC', '" . $value['running_hours'] . "', '" . $value['revolution'] . "', '" . $value['torque'] . "', '" . $value['fuel_consumption'] . "', '" . $value['speed_over_ground'] . "'),";

            for ($i = 1; $i <= $value['counter']; $i++) {
                $tempstring = "exhaust_temperature" . $i;
                $sql_add_exhaust_temps .= "('" . $i . "', '" . $value[$tempstring] . "', '" . $this->arrSession['CDS_Serial'] . "', '" . date("Y-m-d H:i:s", $value['measure_time']) . "'),";
            }
            $test = fread($this->resFileHandle, 2);
        }
        // mysql_select_db($this->arrSession['DB1']);
        $sql_string_system = "INSERT IGNORE INTO CDSData_Operation(CDS_SerialNo, Component, MeasTime, DataVersion, OperHours, EngRevol, EngTorque, FuelCons, SpeedGround) VALUES" . substr($sql_add_system, 0, strlen($sql_add_system) - 1);
        // $result = mysql_query($sql_string_system, $this->arrSession['DB_CONNECTION']['Daily_Transfer']);
        $this->executeQuery($sql_string_system, 'daily_transfer');
        // @todo: Exception abfangen/loggen?
        // echo "SYSTEM-Datensaetze: \t\t\t\t" . mysql_info($this->arrSession['DB_CONNECTION']['Daily_Transfer']) . "\n";
        // if (!$result) {
        //     echo mysql_error() . "\n";
        // }

        if (0 != $value['counter']) {
            $sql_string_system = "INSERT IGNORE INTO CDSData_Operation_ExhaustTemps(exhaust_num, exhaust_temp, CDS_SerialNo, MeasTime) VALUES" . substr($sql_add_exhaust_temps, 0, strlen($sql_add_exhaust_temps) - 1);
            // $result = mysql_query($sql_string_system, $this->arrSession['DB_CONNECTION']['Daily_Transfer']);
            $this->executeQuery($sql_string_system, 'daily_transfer');
            // @todo: Exception abfangen/loggen?
            // echo "Abgastemperaturen-Datenaetze: \t\t\t" . mysql_info($this->arrSession['DB_CONNECTION']['Daily_Transfer']) . "\n";
            // if (!$result) {
            //     echo mysql_error() . "\n";
            // }

        }
        fseek($this->resFileHandle, 12, SEEK_CUR);
        return $this->intMaxMeasureTs;
    }

    protected function run_data()
    {
        $intMaxMeasTs = 0; // akt. Messzeitpunkt
        $this->arrSession['datasetcounter']['run_data'] = 0;
        $this->arrSession['errorcounter']['run_data'] = 0;
        // $this->arrSession['datasetcounter'][run_data] = 0;
        // $this->arrSession['errorcounter'][run_data] = 0;
        // unset($error_array);
        $error_array = [];
        $sql_add_run = "";
        $test = fread($this->resFileHandle, 2);
        while (!feof($this->resFileHandle) && hex2bin('a7a7') === $test) //'��' === $test)
        {
            $text = '';
            for ($i = 1; $i <= 12; $i++) {
                $text .= fread($this->resFileHandle, 1);
            }

            $this->arrSession['datasetcounter']['run_data']++;
            $value = unpack("V1measure_time/s1revolution/v1uniformity/s1kurbel_a/s1kurbel_b", $text);
            $intMaxMeasTs = max($intMaxMeasTs, $value['measure_time']);
            $value['revolution'] /= 10;
            $value['uniformity'] /= 1000;
            $value['kurbel_a'] /= 1000;
            $value['kurbel_b'] /= 1000;
            if ($value['measure_time'] < ($this->arrSession['ref_meas_time'] - 172800) || $value['measure_time'] > ($this->arrSession['ref_meas_time'] + 172800)) {
                $error_array[date("Y-m-d H:i:s", $value['measure_time'])][] = "Messzeit fehlerhaft";
            }

            if ($value['revolution'] < 10 || $value['revolution'] > 200) {
                $error_array[date("Y-m-d  H:i:s", $value['measure_time'])]['revolution'][] = "Drehzahl fehlerhaft: " . $value['revolution'];
            }

            if ($value['uniformity'] < 0 && $value['uniformity'] > 2) {
                $error_array[date("Y-m-d  H:i:s", $value['measure_time'])]['uniformity'][] = "Drehungleichf&ouml;rmigkeit fehlerhaft: " . $value['uniformity'];
            }

            if (!empty($error_array)) {
                $this->arrSession['errors']['RUN'][] = $error_array;
                $this->arrSession['errorcounter']['run_data']++;
                unset($error_array);
            }
            $sql_add_run .= "('" . $this->arrSession['CDS_Serial'] . "', 'ME_0', '" . date("Y-m-d  H:i:s", $value['measure_time']) . "', 'DDC', '" . $value['revolution'] . "', '" . $value['kurbel_a'] . "', '" . $value['kurbel_b'] . "', '" . $value['uniformity'] . "'),";
            $test = fread($this->resFileHandle, 2);
        }
        $sql_string = "INSERT IGNORE INTO CDSData_RUNTrend(CDS_SerialNo, Component, MeasTime, DataVersion, EngineRevol, AK, BK, Uniformity) VALUES" . substr($sql_add_run, 0, strlen($sql_add_run) - 1);

        // mysql_select_db($this->arrSession['DB1']);
        // $result = mysql_query($sql_string, $this->arrSession['DB_CONNECTION']['Daily_Transfer']);
        // echo "RUN-Datensaetze: \t\t\t\t\t" . mysql_info($this->arrSession['DB_CONNECTION']['Daily_Transfer']) . "\n";
        $this->executeQuery($sql_string, 'daily_transfer');
        // @todo: Exception abfangen/loggen?
        // if (!$result) {
        //     echo mysql_error() . "\n";
        // }

        // mysql_free_result($result);
        fseek($this->resFileHandle, 8, SEEK_CUR);
        return $intMaxMeasTs;
    }

    protected function pra_data()
    {
        $intMaxMeasTs = 0;
        // unset($min_wear);
        // unset($max_wear);
        // unset($mean_wear);
        // unset($wear_limit_counter);
        $min_wear = [];
        $max_wear = [];
        $mean_wear = [];
        $wear_limit_counter = [];
        $verschleiss_limit = 50;
        $prozent_limit = 60;
        $sulzer_verschleiss_schiffe = array('00000031',
            '02010031',
            '02020032',
            '02030137',
            '02020244',
            '02020347',
            '02040349',
            '02010565',
            '02040568',
            '02090573',
            '02140578',
            '02020682',
            '02080688',
            '02010793',
            '02050797',
            '02060798',
            '020907101',
            '020108102',
            '020208103',
            '020308104');
        $this->arrSession['datasetcounter']['pra_data'] = 0;
        $this->arrSession['errorcounter']['pra_data'] = 0;
        // unset($error_array);
        // unset($msc_array);
        $error_array = [];
        $msc_array = [];
        $msc_counter = 1;
        $sql_add_trend = "";
        $sql_add_trend_conditions = "";
        $sql_add_wear = "";
        $test = fread($this->resFileHandle, 2);
        while (!feof($this->resFileHandle) && hex2bin('a7a7') === $test) // '��' === $test)
        {
            $text = '';
            for ($i = 1; $i <= 19; $i++) {
                $text .= fread($this->resFileHandle, 1);
            }

            $this->arrSession['datasetcounter']['pra_data']++;
            $temp = unpack("V1measure_time/C1cyl_no/C1ring_count", $text);
            $intMaxMeasTs = max($intMaxMeasTs, $temp['measure_time']);
            $unpack_string = "V1measure_time/C1cyl_no/C1ring_counter/s1revolution/C1thermal_load/C1temp_s1_o/C1temp_s2_o/C1temp_s1_u/C1temp_s2_u/C1piston_condition/C1ring1_condition/C1ring2_condition/C1ring3_condition/C1ring4_condition/C1ring5_condition";
            $rings = '';
            if (4 == $temp['ring_count']) {
                for ($i = 1; $i <= 5; $i++) {
                    $rings .= "/c1air" . $i . "/c1piston_entry$i/c1fire_landing$i/c1piston_exit$i/c1ring1_entry_y_max$i/c1ring1_exit_y_min$i/c1ring2_entry_y_max$i/c1ring2_exit_y_min$i/c1ring3_entry_y_max$i/c1ring3_exit_y_min$i/c1ring4_entry_y_max$i/c1ring4_exit_y_min$i";
                }
            } elseif (5 == $temp['ring_count']) {
                for ($i = 1; $i <= 5; $i++) {
                    $rings .= "/c1air$i/c1piston_entry$i/c1fire_landing$i/c1piston_exit$i/c1ring1_entry_y_max$i/c1ring1_exit_y_min$i/c1ring2_entry_y_max$i/c1ring2_exit_y_min$i/c1ring3_entry_y_max$i/c1ring3_exit_y_min$i/c1ring4_entry_y_max$i/c1ring4_exit_y_min$i/c1ring5_entry_y_max$i/c1ring5_exit_y_min$i";
                }
            }

            for ($i = 1; $i <= (20 + 10 * $temp['ring_count']); $i++) {
                $text .= fread($this->resFileHandle, 1);
            }

            $unpack_string .= $rings;
            unset($temp);
            $value = unpack($unpack_string, $text);
            $intMaxMeasTs = max($intMaxMeasTs, $value['measure_time']);
            $value['temp_s1_o'] *= 2;
            $value['temp_s2_o'] *= 2;
            $value['thermal_load'] /= 100;
            $value['revolution'] /= 10;
            if ($value['measure_time'] < ($this->arrSession['ref_meas_time'] - 172800) || $value['measure_time'] > ($this->arrSession['ref_meas_time'] + 172800)) {
                $error_array[date("Y-m-d  H:i:s", $value['measure_time'])][] = "Messzeit fehlerhaft";
            } else {
                if ($value['revolution'] > 120 && $value['revolution'] < 0) {
                    $error_array[date("Y-m-d  H:i:s", $value['measure_time'])][$value['cyl_no]']]['revolution'][] = "Out of range";
                }

                if ($value['cyl_no'] < 1 && $value['cyl_no'] > 12) {
                    $error_array[date("Y-m-d  H:i:s", $value['measure_time'])][$value['cyl_no']][] = "Out of range";
                }

                if ($value['temp_s1_o'] < 50 && $value['temp_s1_o'] > 250) {
                    if ($value['temp_s1_o'] == 0 || empty($value['temp_s1_o'])) {
                        $value['temp_s1_o'] = -1;
                    } else {
                        $error_array[date("Y-m-d  H:i:s", $value['measure_time'])][$value['cyl_no']]['temp_s1_o'][] = "Out of range" . $value['temp_s1_o'];
                    }
                }

                if ($value['temp_s2_o'] < 50 && $value['temp_s2_o'] > 250) {
                    if ($value['temp_s2_o'] == 0 || empty($value['temp_s2_o'])) {
                        $value['temp_s2_o'] = -1;
                    } else {
                        $error_array[date("Y-m-d  H:i:s", $value['measure_time'])][$value['cyl_no']]['temp_s2_o'][] = "Out of range" . $value['temp_s2_o'];
                    }
                }

                if ($value['temp_s1_u'] < 50 && $value['temp_s1_u'] > 250) {
                    if ($value['temp_s1_u'] == 0 || empty($value['temp_s1_u'])) {
                        $value['temp_s1_u'] = -1;
                    } else {
                        $error_array[date("Y-m-d  H:i:s", $value['measure_time'])][$value['cyl_no']]['temp_s1_u'][] = "Out of range" . $value['temp_s1_u'];
                    }
                }

                if ($value['temp_s2_u'] < 50 && $value['temp_s2_u'] > 250) {
                    if ($value['temp_s2_u'] == 0 || empty($value['temp_s2_u'])) {
                        $value['temp_s2_u'] = -1;
                    } else {
                        $error_array[date("Y-m-d  H:i:s", $value['measure_time'])][$value['cyl_no']]['temp_s2_u'][] = "Out of range" . $value['temp_s2_u'];
                    }
                }

                $sql_add_trend .= "('" . $this->arrSession['CDS_Serial'] . "', 'ME_0', '" . date("Y-m-d  H:i:s", $value['measure_time']) . "', '" . $value['cyl_no'] . "', '" . $value['ring_counter'] . "', 'DDC', '" . $value['revolution'] . "', '" . $value['thermal_load'] . "', '" . $value['temp_s1_o'] . "', '" . $value['temp_s2_o'] . "', '" . $value['temp_s1_u'] . "', '" . $value['temp_s2_u'] . "', '" . $value['piston_condition'] . "', '" . $value['ring1_condition'] . "', '" . $value['ring2_condition'] . "', '" . $value['ring3_condition'] . "', '" . $value['ring4_condition'] . "', '" . $value['ring5_condition'] . "'),";
                for ($i = 1; $i <= 5; $i++) {
                    $air_string = "air" . $i;
                    $piston_entry_string = "piston_entry" . $i;
                    $fire_landing_string = "fire_landing" . $i;
                    $piston_exit_string = "piston_exit" . $i;
                    $ring_entry_y_max1_string = "ring1_entry_y_max" . $i;
                    $ring_exit_y_min1_string = "ring1_exit_y_min" . $i;
                    $ring_entry_y_max2_string = "ring2_entry_y_max" . $i;
                    $ring_exit_y_min2_string = "ring2_exit_y_min" . $i;
                    $ring_entry_y_max3_string = "ring3_entry_y_max" . $i;
                    $ring_exit_y_min3_string = "ring3_exit_y_min" . $i;
                    $ring_entry_y_max4_string = "ring4_entry_y_max" . $i;
                    $ring_exit_y_min4_string = "ring4_exit_y_min" . $i;
                    $ring_entry_y_max5_string = "ring5_entry_y_max" . $i;
                    $ring_exit_y_min5_string = "ring5_exit_y_min" . $i;
                    $sql_add_trend_conditions .= "('" . $i . "', '" . $value[$air_string] / 10 . "', '" . $value[$piston_entry_string] / 10 . "', '" . $value[$fire_landing_string] / 10 . "', '" . $value[$piston_exit_string] / 10 . "', '" . $value[$ring_entry_y_max1_string] / 10 . "', '" . $value[$ring_exit_y_min1_string] / 10 . "', '" . $value[$ring_entry_y_max2_string] / 10 . "', '" . $value[$ring_exit_y_min2_string] / 10 . "', '" . $value[$ring_entry_y_max3_string] / 10 . "', '" . $value[$ring_exit_y_min3_string] / 10 . "', '" . $value[$ring_entry_y_max4_string] / 10 . "', '" . $value[$ring_exit_y_min4_string] / 10 . "', '" . $value[$ring_entry_y_max5_string] / 10 . "', '" . $value[$ring_exit_y_min5_string] / 10 . "', '" . $this->arrSession['CDS_Serial'] . "','" . date("Y-m-d  H:i:s", $value['measure_time']) . "', '" . $value['cyl_no'] . "'),";
//////////////////////////////////

                    if (0 < array_search($this->arrSession['CDS_Serial'], $sulzer_verschleiss_schiffe) /*)*/ &&
                        0 < $value[$ring_entry_y_max1_string] &&
                        0 < $value[$ring_entry_y_max2_string] &&
                        0 < $value[$ring_entry_y_max3_string] &&
                        0 < $value[$ring_entry_y_max4_string] &&
                        (empty($value[$ring_entry_y_max5_string]) || 0 < $value[$ring_entry_y_max5_string]) &&
                        0 > $value[$ring_exit_y_min1_string] &&
                        0 > $value[$ring_exit_y_min2_string] &&
                        0 > $value[$ring_exit_y_min3_string] &&
                        0 > $value[$ring_exit_y_min4_string] &&
                        (empty($value[$ring_exit_y_min5_string]) || 0 > $value[$ring_exit_y_min5_string])) {
                        $wear[$value['cyl_no']] = 200 * round(((abs($value[$ring_entry_y_max2_string]) + abs($value[$ring_exit_y_min2_string]) + abs($value[$ring_entry_y_max3_string]) + abs($value[$ring_exit_y_min3_string]) + abs($value[$ring_entry_y_max4_string]) + abs($value[$ring_exit_y_min4_string])) / 3 - (abs($value[$ring_entry_y_max1_string]) + abs($value[$ring_exit_y_min1_string]))) / ((abs($value[$ring_entry_y_max2_string]) + abs($value[$ring_exit_y_min2_string]) + abs($value[$ring_entry_y_max3_string]) + abs($value[$ring_exit_y_min3_string]) + abs($value[$ring_entry_y_max4_string]) + abs($value[$ring_exit_y_min4_string])) / 3), 4);
                        if ($min_wear[$value['cyl_no']] > $wear[$value['cyl_no']] || empty($min_wear[$value['cyl_no']])) {
                            $min_wear[$value['cyl_no']] = $wear[$value['cyl_no']];
                        }

                        if ($max_wear[$value['cyl_no']] < $wear[$value['cyl_no']]) {
                            $max_wear[$value['cyl_no']] = $wear[$value['cyl_no']];
                        }

                        if ($wear[$value['cyl_no']] < $verschleiss_limit) {
                            $wear_limit_counter[$value['cyl_no']]++;
                        }

                        $mean_wear[$value['cyl_no']] += $wear[$value['cyl_no']];
                    }

                }
                if (empty($msc_array)) {
                    $msc_array[1] = $value;
                    $msc_counter = 2;
                } else
                if ($msc_array[1]['cyl_no'] == $value['cyl_no'] || 900 < (($value['measure_time']) - $msc_array[1]['measure_time'])) {
                    for ($i = 1; $i < $msc_counter; $i++) {
                        $msc_array[$i]['thermal_load'] *= 100;
                        $sql_add_wear .= "('" . $this->arrSession['ship_data']->id . "', '" . date("Y-m-d", $msc_array[1]['measure_time']) . "', '" . date("H:i:s", $msc_array[1]['measure_time']) . "', '" . $msc_array[$i]['cyl_no'] . "', '" . $msc_array[$i]['thermal_load'] . "', '" . $msc_array[1]['revolution'] . "', '0', '-1', '" . $msc_array[$i]['temp_s1_u'] . "'),";
                    }
                    unset($msc_array);
                    $msc_array[1] = $value;
                    $msc_counter = 2;
                } else {
                    $msc_array[$msc_counter] = $value;
                    $msc_counter += 1;
                }
            }
            if (!empty($error_array)) {
                $this->arrSession['errors']['PRA'][] = $error_array;
                $this->arrSession['errorcounter']['pra_data']++;
                unset($error_array);
            }
            $test = fread($this->resFileHandle, 2);
        }
        $sql_string_trend = "INSERT IGNORE INTO CDSData_PRATrend(CDS_SerialNo, Component, MeasTime, Cylinder, Rings, DataVersion, EngineRevol, ThermLoad, Temperature_U1, Temperature_U2, Temperature_L1, Temperature_L2, PistonCond, RingCond_1, RingCond_2, RingCond_3, RingCond_4, RingCond_5) VALUES" . substr($sql_add_trend, 0, strlen($sql_add_trend) - 1);
        // mysql_select_db($this->arrSession['DB1']);

        // $result = mysql_query($sql_string_trend, $this->arrSession['DB_CONNECTION']['Daily_Transfer']);
        // echo "PRATrend-Datensaetze: \t\t\t\t" . mysql_info($this->arrSession['DB_CONNECTION']['Daily_Transfer']) . "\n";
        // $pra_trend_counter = explode(" ", mysql_info($this->arrSession['DB_CONNECTION']['Daily_Transfer']));
        $this->executeQuery($sql_string_trend, 'daily_transfer');
        // @todo: Exception abfangen/loggen?
        // if (!$result) {
        //     echo mysql_error() . "\n";
        // }

        // mysql_free_result($result);
        $sql_string_trend_conditions = "INSERT IGNORE INTO CDSData_PRATrend_Conditions(measurement_cycle, air, piston_entry, fire_landing, piston_exit, ring_entry_1, ring_leaving_1, ring_entry_2, ring_leaving_2, ring_entry_3, ring_leaving_3, ring_entry_4, ring_leaving_4, ring_entry_5, ring_leaving_5, CDS_SerialNo, MeasTime, Cylinder) VALUES" . substr($sql_add_trend_conditions, 0, strlen($sql_add_trend_conditions) - 1);

        // $result = mysql_query($sql_string_trend_conditions, $this->arrSession['DB_CONNECTION']['Daily_Transfer']);
        // echo "PRATrend_Conditions-Datensaetze: \t\t" . mysql_info($this->arrSession['DB_CONNECTION']['Daily_Transfer']) . "\n";
        $this->executeQuery($sql_string_trend_conditions, 'daily_transfer');
        // @todo: Exception abfangen/loggen?
        // if (!$result) {
        //     echo mysql_error() . "\n";
        // }

        // mysql_free_result($result);
        // mysql_select_db($this->arrSession['DB2']);
        $sql_string = "INSERT IGNORE INTO wear_history(ship_table_id, measure_date, measure_time, cyl_no, therm_load, revolution, running_hours, wear_reserve, prm_temperature) VALUES" . substr($sql_add_wear, 0, strlen($sql_add_wear) - 1);
        // $result = mysql_query($sql_string, $this->arrSession['DB_CONNECTION']['usr_web7_1']);
        // echo "wear_history-Datensaetze: \t\t\t\t" . mysql_info($this->arrSession['DB_CONNECTION']['usr_web7_1']) . "\n";
        $this->executeQuery($sql_string);
        // @todo: Exception abfangen/loggen?
        // if (!$result) {
        //     echo mysql_error() . "\n";
        // }

        // mysql_free_result($result);
        fseek($this->resFileHandle, 8, SEEK_CUR);
        /*
        if (0 < array_search($this->arrSession['CDS_Serial'], $sulzer_verschleiss_schiffe)) {
        echo "Schiff für Sulzer Verschleiss Daten!\n";
        $text = '';
        for ($i = 1; $i <= $this->arrSession['ship_data']->cyl_count; $i++) {
        $prozent = round($wear_limit_counter[$i] / $pra_trend_counter[1] * 100, 1);
        if ($prozent_limit < $prozent) {
        $mail_senden = true;
        $text .= "Zylinder " . $i . " : " . round($mean_wear[$i] / $pra_trend_counter[1], 2) . "% (" . $wear_limit_counter[$i] . " / " . $pra_trend_counter[1] . " Messwerten der aktuellen Dailymail) \n";
        }
        }
        if ($mail_senden) {
        $message = "Datum: " . date("Y-m-d H:i:s", $this->arrSession['ref_meas_time']) . " \n\n" . $text;
        if (mail("alarmliste@maridis.de", "Verschleisswarnung:  " . $this->arrSession['ship_data']->taufname, $message, "From: Maridis Server <server@maridis.de>")) {
        echo "Mail an alarmliste@maridis.de versendet\n";
        } else {
        echo "Problem beim Mailversand!\n";
        }

        } else {
        echo "mail_senden = FALSE\n";
        }
        }
         */
        return $intMaxMeasTs;
    }
    protected function error_data()
    {
        $temp = fread($this->resFileHandle, 1024);
        $this->arrSession['dm_err_msg'] = "";
        // unset($err_msg);
        $err_msg = '';
        //    $errors = explode("�", $temp);
        $errors = explode(hex2bin('a7'), $temp);
        foreach ($errors as $trash => $text) {
            if ("" != $text) {
                switch ($text) {
                    case (substr($text, 0, 16) == "OPEN_REG_KEY_ERR"): $err_msg = "Registry Key kann nicht geöffnet werden";
                        break;
                    case (substr($text, 0, 13) == "NO_REGVAL_ERR"): $err_msg = "Registry Wert kann nicht gefunden werden";
                        break;
                    case (substr($text, 0, 8) == "COPY_ERR"): $err_msg = "Datei / Verzeichnis kann nicht kopiert werden";
                        break;
                    case (substr($text, 0, 11) == "NO_FILE_ERR"): $err_msg = "Datei wird nicht gefunden";
                        break;
                    case (substr($text, 0, 11) == "OPEN_DB_ERR"): $err_msg = "Trend Datenbank kann nicht geöffnet werden";
                        break;
                    case (substr($text, 0, 11) == "READ_DB_ERR"): $err_msg = "Fehler beim Lesen aus der Trend Datenbank";
                        break;
                    case (substr($text, 0, 12) == "WRITE_DB_ERR"): $err_msg = "Fehler beim Schreiben der Daten in die E-Mail-Datei";
                        break;
                    case (substr($text, 0, 7) == "WIN_ERR"): $err_msg = "Fehlermeldung von Windows oder dem Programm";
                        break;
                    case (substr($text, 0, 7) == "ZIP_ERR"): $err_msg = "Zip-Datei kann nicht erstellt werden";
                        break;
                }
                $length = strcspn($text, "$?") - 1;
                // $text = substr($text, 1, $length);
                $text = substr($text, 0, $length);
                // echo "Fehler: " . $err_msg . " : " . strstr($text, '\'') . "\n";
                if ("" != $err_msg) {
                    $this->arrSession['dm_err_msg'] .= $err_msg . " " . strstr($text, '\'') . "\n";
                }

            }
        }
    }

    protected function wear_data()
    {
        $intMaxMeasTs = 0;
        $this->arrSession['datasetcounter']['wear_data'] = 0;
        $this->arrSession['errorcounter']['wear_data'] = 0;
        unset($error_array);
        $sql_prawear_add = "";
        $test = fread($this->resFileHandle, 2);
        while (!feof($this->resFileHandle) && hex2bin('a7a7') === $test) //'��' === $test)
        {
            $text = '';
            for ($i = 1; $i <= 27; $i++) {
                $text .= fread($this->resFileHandle, 1);
            }

            $this->arrSession['datasetcounter']['wear_data']++;
            $value = unpack("V1measure_time/C1cyl_no/s1revolution/s1air/s1piston_entry/s1ring_average/s1piston_exit/s1ring_max/s1ring_min/s1slot_max/s1slot_min/s1wear/s1delta", $text);
            $intMaxMeasTs = $value['measure_time'];
            if ($value['measure_time'] < ($this->arrSession['ref_meas_time'] - 172800) || $value['measure_time'] > ($this->arrSession['ref_meas_time'] + 172800)) {
                $error_array[date("Y-m-d H:i:s", $value['measure_time'])][] = "Messzeit fehlerhaft";
            }

            if ($value['cyl_no'] < 1 && $value['cyl_no'] > 12) {
                $error_array[date("Y-m-d  H:i:s", $value['measure_time'])][$value['cyl_no']][] = "Zylindernummer fehlerhaft: " . $value['cyl_no'];
            }

            if ($value['revolution'] < 120 && $value['revolution'] > 0) {
                $error_array[date("Y-m-d  H:i:s", $value['measure_time'])][$value['cyl_no']]['revolution'][] = "Drehzahl fehlerhaft: " . $value['revolution'];
            }

            if ($value['air'] < -15 && $value['air'] > 15) {
                $error_array[date("Y-m-d  H:i:s", $value['measure_time'])][$value['cyl_no']]['air'][] = "air fehlerhaft: " . $value['air'];
            }

            if ($value['piston_entry'] < -15 && $value['piston_entry'] > 15) {
                $error_array[date("Y-m-d  H:i:s", $value['measure_time'])][$value['cyl_no']]['piston_entry'][] = "piston_entry fehlerhaft: " . $value['piston_entry'];
            }

            if ($value['ring_average'] < -15 && $value['ring_average'] > 15) {
                $error_array[date("Y-m-d  H:i:s", $value['measure_time'])][$value['cyl_no']]['ring_average'][] = "ring_average fehlerhaft: " . $value['ring_average'];
            }

            if ($value['piston_exit'] < -15 && $value['piston_exit'] > 15) {
                $error_array[date("Y-m-d  H:i:s", $value['measure_time'])][$value['cyl_no']]['piston_exit'][] = "piston_exit fehlerhaft: " . $value['piston_exit'];
            }

            if ($value['ring_max'] < -15 && $value['ring_max'] > 15) {
                $error_array[date("Y-m-d  H:i:s", $value['measure_time'])][$value['cyl_no']]['ring_max'][] = "ring_max fehlerhaft: " . $value['ring_max'];
            }

            if ($value['ring_min'] < -15 && $value['ring_min'] > 15) {
                $error_array[date("Y-m-d  H:i:s", $value['measure_time'])][$value['cyl_no']]['ring_min'][] = "ring_min fehlerhaft: " . $value['ring_min'];
            }

            if ($value['slot_max'] < -15 && $value['slot_max'] > 15) {
                $error_array[date("Y-m-d  H:i:s", $value['measure_time'])][$value['cyl_no']]['slot_max'][] = "slot_max fehlerhaft: " . $value['slot_max'];
            }

            if ($value['slot_min'] < -15 && $value['slot_min'] > 15) {
                $error_array[date("Y-m-d  H:i:s", $value['measure_time'])][$value['cyl_no']]['slot_min'][] = "slot_min fehlerhaft: " . $value['slot_min'];
            }

            if ($value['wear'] < -15 && $value['wear'] > 15) {
                $error_array[date("Y-m-d  H:i:s", $value['measure_time'])][$value['cyl_no']]['wear'][] = "wear fehlerhaft: " . $value['wear'];
            }

            if ($value['delta'] < -15 && $value['delta'] > 15) {
                $error_array[date("Y-m-d  H:i:s", $value['measure_time'])][$value['cyl_no']]['delta'][] = "delta fehlerhaft: " . $value['delta'];
            }

            $sql_prawear_add .= "('" . $this->arrSession['CDS_Serial'] . "', 'ME_0', '" . date("Y-m-d  H:i:s", $value['measure_time']) . "', '" . $value['cyl_no'] . "', '" . $value['revolution'] / 10 . "', '" . $value['air'] / 2000 . "', '" . $value['piston_entry'] / 2000 . "', '" . $value['ring_average'] / 2000 . "', '" . $value['piston_exit'] / 2000 . "', '" . $value['ring_max'] / 2000 . "', '" . $value['ring_min'] / 2000 . "', '" . $value['slot_max'] / 2000 . "', '" . $value['slot_min'] / 2000 . "', '" . $value['wear'] / 1000 . "', '" . $value['delta'] / 1000 . "'),";
            if (!empty($error_array)) {
                $this->arrSession['errors']['WEAR'][] = $error_array;
                $this->arrSession['errorcounter']['wear_data']++;
                unset($error_array);
            }
            $test = fread($this->resFileHandle, 2);
        }
        if ("" != $sql_prawear_add) {
            $sql_string = "INSERT IGNORE INTO CDSData_PRAWear(CDS_SerialNo, Component, MeasTime, Cylinder, EngineRevol, AirVal, PistEntry, RingAvg, PistLeave, RingMax, RingMin, SlotMax, SlotMin, Wear, Delta) VALUES" . substr($sql_prawear_add, 0, strlen($sql_prawear_add) - 1);
            // mysql_select_db($this->arrSession['DB1']);
            // $result = mysql_query($sql_string, $this->arrSession['DB_CONNECTION']['Daily_Transfer']);
            //   echo "PRAWear-Datensaetze: \t\t\t\t".mysql_info($this->arrSession['DB_CONNECTION']['Daily_Transfer'])."\n";
            $this->executeQuery($sql_string, 'daily_transfer');
            // @todo: Exception abfangen/loggen?
            // if(!$result)
            //     echo mysql_error()."\n";
        }
        fseek($this->resFileHandle, 9, SEEK_CUR);
        return $intMaxMeasTs;
    }

    protected function alarm_data()
    {
        $this->arrSession['datasetcounter']['alarm_data'] = 0;
        $this->arrSession['errorcounter']['alarm_data'] = 0;
        $error_array = [];
        unset($error_array);
        $sql_add_alarm = "";
        $test = fread($this->resFileHandle, 2);
        while (!feof($this->resFileHandle) && hex2bin('a7a7') === $test) //'��' === $test)
        {
            $text = '';
            for ($i = 1; $i <= 10; $i++) {
                $text .= fread($this->resFileHandle, 1);
            }

            $this->arrSession['datasetcounter']['alarm_data']++;
            $value = unpack("V1measure_time/C1modul_id/C1status/V1error_no", $text);
            if ($value['measure_time'] < ($this->arrSession['ref_meas_time'] - 172800) || $value['measure_time'] > ($this->arrSession['ref_meas_time'] + 172800)) {
                $error_array[date("Y-m-d H:i:s", $value['measure_time'])] = "Messzeit fehlerhaft";
            }

            $alarmlist_data = new stdClass();
            $alarmlist_data->AlarmTime = date("Y-m-d H:i:s", $value['measure_time']);
            $alarmlist_data->Modul = $value['modul_id'];
            $alarmlist_data->Component = "ME_0";
            $alarmlist_data->Status = $value['status'];
            $alarmlist_data->AlarmNo = $value['error_no'];
            $this->arrSession['alarmlist_data'][] = $alarmlist_data;
            if (!empty($error_array)) {
                $this->arrSession['errors']['ALARM'][] = $error_array;
                $this->arrSession['errorcounter']['alarm_data']++;
                unset($error_array);
            }
            unset($alarmlist_data);
            $sql_add_alarm .= "('" . $this->arrSession['CDS_Serial'] . "', 'ME_0', '" . date("Y-m-d  H:i:s", $value['measure_time']) . "', '" . $value['modul_id'] . "', '" . $value['status'] . "', '" . $value['error_no'] . "'),";
            $test = fread($this->resFileHandle, 2);
        }
        $sql_string = "INSERT IGNORE INTO CDSData_Alarm(CDS_SerialNo, Component, AlarmTime, Modul, Status, AlarmNo) VALUES" . substr($sql_add_alarm, 0, strlen($sql_add_alarm) - 1);
        // mysql_select_db($this->arrSession['DB1']);
        // $result = mysql_query($sql_string, $this->arrSession['DB_CONNECTION']['Daily_Transfer']);
        // echo "Alarm-Datensaetze: \t\t\t\t\t" . mysql_info($this->arrSession['DB_CONNECTION']['Daily_Transfer']) . "\n";
        $this->executeQuery($sql_string, 'daily_transfer');
        // @todo: Exception abfangen/loggen?
        // if (!$result) {
        //     echo mysql_error() . "\n";
        // }

        fseek($this->resFileHandle, 10, SEEK_CUR);
    }

    protected function cdslog_data()
    {
        foreach ($this->_einlesen() as $key => $text) {
            $_SESSION['cdslog'][] = $key . " - " . $text;
        }

        fseek($this->resFileHandle, 13, SEEK_CUR);
    }
    private function _einlesen()
    {
        $abbruch = false;
        $buffer = '';
        while (!$abbruch) {
            $temp = fread($this->resFileHandle, 1);
            if ('#' == $temp) {
                $temp2 = fread($this->resFileHandle, 5);
                if ('#_END' == $temp2) {
                    fseek($this->resFileHandle, -6, SEEK_CUR);
                    $abbruch = true;
                } else {
                    $buffer .= $temp . $temp2;
                }

            } elseif (feof($this->resFileHandle)) {
                $abbruch = true;
            } else {
                $buffer .= $temp;
            }

        }
        return explode(hex2bin('a7a7'), $buffer);
    }
}
