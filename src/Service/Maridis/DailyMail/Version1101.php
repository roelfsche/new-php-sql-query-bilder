<?php

namespace App\Service\Maridis\DailyMail;

class Version1101 extends Version1001
{

    public function process()
    {
        while (!feof($this->resFileHandle)) {
            $text = fread($this->resFileHandle, 21);
            if (!feof($this->resFileHandle)) {
                switch ($text) {
                    case (strpos($text, "##_SYSTEM") !== false):
                        fseek($this->resFileHandle, -21, SEEK_CUR);
                        fseek($this->resFileHandle, strpos($text, "##_SYSTEM") + 10, SEEK_CUR);
                        $this->intMaxMeasTs = max($this->intMaxMeasTs, $this->system_data());
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
        unset($error_array);
        $sql_add_system = "";
        // $sql_add_exhaust_temps = "";
        $this->arrSession['datasetcounter']['system_data'] = 0;
        $this->arrSession['errorcounter']['system_data'] = 0;
        $test = fread($this->resFileHandle, 2);
        while (!feof($this->resFileHandle) && hex2bin('a7a7') === $test) //'��' === $test)
        {
            $text = '';
            $this->arrSession['datasetcounter']['system_data']++;
            for ($i = 1; $i <= 42; $i++) {
                $text .= fread($this->resFileHandle, 1);
            }

            $value = unpack("V1measure_time/s1revolution/s1fuel_consumption/s1speed_over_water/s1speed_over_ground/s1rudder_angle/s1propeller_pitch/s1shaft_speed/s1shaft_torque/s1shaft_power/s1draught_fwd/s1draught_aft/s1trim/s1heel/V1longitude/V1latitude/V1heading", $text);
            $intMaxMeasureTs = max($value['measure_time'], $intMaxMeasureTs);
            $value['revolution'] /= 10;
            $value['speed_over_ground'] /= 10;
            $value['speed_over_water'] /= 10;
            $value['rudder_angle'] /= 10;
            $value['propeller_pitch'] /= 10;
            $value['shaft_speed'] /= 10;
            $value['draught_fwd'] /= 10;
            $value['draught_aft'] /= 10;
            $value['trim'] /= 10;
            $value['heel'] /= 10;
            $value['longitude'] /= 100000;
            $value['latitude'] /= 100000;
            $value['heading'] /= 100;
            if ($value['measure_time'] < ($this->arrSession['ref_meas_time'] - 172800) || $value['measure_time'] > ($this->arrSession['ref_meas_time'] + 172800)) {
                $error_array[date("Y-m-d H:i:s", $value['measure_time'])][] = "Messzeit fehlerhaft";
            }

            if ($value['revolution'] < 10 || $value['revolution'] > 600) {
                $error_array[date("Y-m-d H:i:s", $value['measure_time'])][] = "Drehzahl fehlerhaft: " . $value['revolution'];
            }

            if (!empty($error_array)) {
                $this->arrSession['errors']['System'][] = $error_array;
                $this->arrSession['errorcounter']['system_data']++;
                unset($error_array);
            }
            $sql_add_system .= "('" . $this->arrSession['CDS_Serial'] . "', 'ME_0', '" . date("Y-m-d  H:i:s", $value['measure_time']) . "', '1101',  '" . $value['revolution'] . "', '" . $value['fuel_consumption'] . "', '" . $value['speed_over_water'] . "', '" . $value['speed_over_ground'] . "', '" . $value['rudder_angle'] . "', '" . $value['propeller_pitch'] . "', '" . $value['shaft_speed'] . "', '" . $value['shaft_torque'] . "', '" . $value['shaft_power'] . "', '" . $value['draught_fwd'] . "', '" . $value['draught_aft'] . "', '" . $value['trim'] . "', '" . $value['heel'] . "', '" . $value['longitude'] . "', '" . $value['latitude'] . "', '" . $value['heading'] . "'),";
            $test = fread($this->resFileHandle, 2);
        }
        $sql_string_system = "INSERT IGNORE INTO CDSData_Offdesign(CDS_SerialNo, Component, MeasTime, DataVersion, EngRevol, FuelCons, SpeedWater, SpeedGround, RudderAngle, PropellerPitch, ShaftSpeed, ShaftTorque, ShaftPower, DraughtFWD, DraughtAFT, Trim, Heel, Longitude, Latitude, Heading) VALUES" . substr($sql_add_system, 0, strlen($sql_add_system) - 1);
        // mysql_select_db($this->arrSession['DB1']);
        // $result = mysql_query($sql_string_system, $this->arrSession['DB_CONNECTION']['Daily_Transfer']);
        // echo "SYSTEM-Datensaetze: \t\t\t\t\t" . mysql_info() . "\n";
        $this->executeQuery($sql_string_system, 'daily_transfer');
        // @todo: Exception abfangen/loggen?
        // if (!$result) {
        //     echo mysql_error() . "\n";
        // }

        fseek($this->resFileHandle, 12, SEEK_CUR);
        return $intMaxMeasureTs;
    }
}
