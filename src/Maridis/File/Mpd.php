<?php

namespace App\Maridis\File;

use App\Entity\UsrWeb71\ShipTable as UsrWeb71ShipTable;
use App\Exception\MscException;
use App\Maridis\File;
use App\Maridis\FileInterface;
use Exception;
use ShipTable;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Mpd extends File implements FileInterface
{

    protected $strMpdSerialNumber = null;

    /**
     * Statischer Check, ob es ein Mpd-File ist.
     *
     * Wenn ja, wird eine Instanz hiervon zurück gegeben
     *
     * @return FileInterface
     */
    public static function getIf(ContainerInterface $objContainer, $resFileHandle): ?FileInterface
    {
        if (!is_resource($resFileHandle)) {
            throw new Exception('No Resource!');
        }

        fseek($resFileHandle, 0);
        $strContent = fread($resFileHandle, 50);
        if (($strContent[0] == '*' && $strContent[1] == '2' && strpos($strContent, "MarPrime")) == false) {
            return null;
        }

        $objFile = new Mpd($objContainer, $resFileHandle);
        return $objFile;
    }

    /**
     * Verarbeitet das File
     */
    public function process()
    {
        rewind($this->resFileHandle);
        $this->readHeader();
        $arrTmp = [];
        if (!preg_match('/^(\d{2})\.(\d{4})\.(\d{5})$/', $this->arrHeader['SerialNo'], $arrTmp)) {

            $this->objLogger->warning("invalid serial number: " . $this->arrHeader['SerialNo'] . '; file ignored!');
            return;
        }
        $this->strMpdSerialNumber = $this->arrHeader['SerialNo'];

        /**
         * @var App\Repository\UsrWeb71\ShipTableRepository
         */
        $objShipRepository = $this->objDoctrineDefaultManager->getRepository(UsrWeb71ShipTable::class);
        $this->objShip = $objShipRepository->findByMarprimeSerialNo($this->strMpdSerialNumber);

        $this->objLogger->info("processing data for ship " . $this->objShip->getAktName());

        while (!feof($this->resFileHandle)) {
            $strContent = fread($this->resFileHandle, 25);
            if (!feof($this->resFileHandle)) {
                switch ($strContent) {
                    case (strpos($strContent, "_CPA_DATA_") !== false):
                        fseek($this->resFileHandle, strpos($strContent, "_CPA_DATA_") - 25, SEEK_CUR);
                        $this->ShiftToPosition(10);
                        // require_once 'mpd_measurementdata_100.inc';
                        $meas_data = $this->getMeasurementData($this->arrHeader);
                        if ($meas_data[0]['MeasurementTime'] == 0) {
                            // fehlerhafterweise keine Daten
                            throw new MscException(strtr('Keine Messdaten im MPD-File :filename enthalten!', array(
                                ':filename' => $this->strFilename,
                            )));
                        }

        $this->objLogger->info("  MeasurementTime=" . date('H:i:s d.M.Y', $meas_data[0]['MeasurementTime']));//processing data for ship " . $this->objShip->getAktName());
                        break;
                    case (strpos($strContent, "_CPA_Params_") !== false):
                        fseek($this->resFileHandle, strpos($strContent, "_CPA_Params_") - 25, SEEK_CUR);
                        $this->ShiftToPosition(12);
                        // require_once 'mpd_measparam_100.inc';
                        $meas_params = $this->getMeasurementParams($this->resFileHandle, $this->arrHeader);
                        break;
                    case (strpos($strContent, "_Engine_Params_") !== false):
                        fseek($this->resFileHandle, strpos($strContent, "_Engine_Params_") - 25, SEEK_CUR);
                        $this->ShiftToPosition(15); //, $this->resFileHandle);
                        // require_once 'mpd_engparam_100.inc';
                        $eng_params = $this->getEngineParams($this->resFileHandle, $this->arrHeader, $meas_data[0]['MeasurementTime']);
                        break;
                    case (strpos($strContent, "_Program_Params_") !== false):
                        fseek($this->resFileHandle, strpos($strContent, "_Program_Params_") - 25, SEEK_CUR);
                        $this->ShiftToPosition(16); //, $this->resFileHandle);
                        // require_once 'mpd_progparam_100.inc';
                        $prog_params = $this->getProgramParams($this->resFileHandle, $this->arrHeader);
                        break;
                    case (strpos($strContent, "_Report_Data_") !== false):
                        fseek($this->resFileHandle, strpos($strContent, "_Report_Data_") - 25, SEEK_CUR);
                        $this->ShiftToPosition(13, $this->resFileHandle);
                        // require_once 'mpd_reportparam_100.inc';
                        $report_params = $this->getReportData(
                            $this->resFileHandle,
                            $this->arrHeader,
                            $meas_data[0]['MeasurementTime'],
                            $eng_params['EngName'],
                            $eng_params['EngType'],
                            substr($this->resFileHandle, 0, -4)
                        );
                        break;
                    default:
                    // $this->objLogger->warning("Couldn't identify data part: " . $strContent);
                        break;
                }
            }
        }
        $this->setLastMeasurementData((int)$this->arrHeader['lastUpdateTimestamp'], NULL, NULL, $this->arrHeader['SerialNo']);
    }

    private function readHeader()
    {
        $this->arrHeader['Version'] = trim(fread($this->resFileHandle, 4));
        $this->arrHeader['Product'] = trim(fread($this->resFileHandle, 25));
        $this->arrHeader['Module'] = trim(fread($this->resFileHandle, 25));
        $this->arrHeader['FileVersion'] = trim(fread($this->resFileHandle, 5));
        $time = trim(fread($this->resFileHandle, 20));
        // Timestamp der Auswertung durch die Software
        $this->arrHeader['LastUpdate'] = substr($time, 6, 4) . "-" . substr($time, 3, 2) . "-" . substr($time, 0, 2) . " " . substr($time, 11, 5) . ":00";
        $this->arrHeader['lastUpdateTimestamp'] = strtotime($time); // 24.03.2015 02:36 -> unix_ts
        $this->arrHeader['SerialNo'] = trim(fread($this->resFileHandle, 25));
        $this->arrHeader['UserField'] = trim(fread($this->resFileHandle, 25));
        $this->arrHeader['Component'] = trim(fread($this->resFileHandle, 25));
        $this->arrHeader['Checksum'] = trim(fread($this->resFileHandle, 8));
    }

    /**
     * aus der alten Schnittstelle
     */
    public function getMeasurementData()
    {
        $history = [];
        // echo "Receiving Measurement Data...\n";
        $data[0] = "";
        $data += unpack("v1Status/d1IpaStartAngle/d1IpaAngleSteps/v1TdcCorrected/v1ScavMeasured/v1OscDone", fread($this->resFileHandle, 25));
        $text = trim(fread($this->resFileHandle, 5));
        $sql = "INSERT IGNORE INTO `mpd_measurement_data` ( `MarPrime_SerialNo` , `date` , `MeasurementTime` , `cyl_no` , `measurement_num` , `calc_fail` , `count_cycles` , `cycle_length_cpa` , `cycle_length_ipa` , `data_status` ,
	`ipa_start` , `ipa_schritt` , `ot_korrektur` , `spuelluft` , `pfeiffenschwingungskorrektur` , `revolution` , `ind_power` , `v_dp` , `pil` , `pmax` , `apmax` , `pim` , `pilw` , `p0` , `p36` ,
	`pemax` , `apemax` , `peb` , `apeb` , `apee` , `apfb` , `apfe` , `afd` , `azv` , `aev` , `scav_air` , `aed` , `pfb` , `avb` , `cappa`) VALUES ";
        while ("Cyl_" == $text) {
            $cyl_no = trim(fread($this->resFileHandle, 2));
            $data[$cyl_no] = unpack("a2xxx/v1CylStat" . $cyl_no . "/v1CalcFail/v1Cycles/v1IpaMode/v1CycleLengthIPA/v1CycleLengthCPA/V1MeasurementTime", fread($this->resFileHandle, 18));
            //        var_dump("<pre>", $data[$cyl_no], "</pre>");
            //        echo "Measurement Time: ".date("Y-m-d H:i:s", $data[$cyl_no]['MeasurementTime'])."\n";
            if ($data[$cyl_no]['CylStat' . $cyl_no] != 0) {
                $count = unpack("v1count", fread($this->resFileHandle, 2));
                $counter = $count['count'];
                if (0 != $count['count']) {
                    $data[$cyl_no] += unpack("d" . $count['count'] . "Revolution_", fread($this->resFileHandle, 8 * $count['count']));
                }

                $count = unpack("v1count", fread($this->resFileHandle, 2));
                if (0 != $count['count']) {
                    $data[$cyl_no] += unpack("d" . $count['count'] . "indPower_", fread($this->resFileHandle, 8 * $count['count']));
                }

                $count = unpack("v1count", fread($this->resFileHandle, 2));
                if (0 != $count['count']) {
                    $data[$cyl_no] += unpack("d" . $count['count'] . "Vdp_", fread($this->resFileHandle, 8 * $count['count']));
                }

                $count = unpack("v1count", fread($this->resFileHandle, 2));
                if (0 != $count['count']) {
                    $data[$cyl_no] += unpack("d" . $count['count'] . "Pil_", fread($this->resFileHandle, 8 * $count['count']));
                }

                $count = unpack("v1count", fread($this->resFileHandle, 2));
                if (0 != $count['count']) {
                    $data[$cyl_no] += unpack("d" . $count['count'] . "PMax_", fread($this->resFileHandle, 8 * $count['count']));
                }

                $count = unpack("v1count", fread($this->resFileHandle, 2));
                if (0 != $count['count']) {
                    $data[$cyl_no] += unpack("d" . $count['count'] . "APMax_", fread($this->resFileHandle, 8 * $count['count']));
                }

                $count = unpack("v1count", fread($this->resFileHandle, 2));
                if (0 != $count['count']) {
                    $data[$cyl_no] += unpack("d" . $count['count'] . "Pim_", fread($this->resFileHandle, 8 * $count['count']));
                }

                $count = unpack("v1count", fread($this->resFileHandle, 2));
                if (0 != $count['count']) {
                    $data[$cyl_no] += unpack("d" . $count['count'] . "Pilw_", fread($this->resFileHandle, 8 * $count['count']));
                }

                $count = unpack("v1count", fread($this->resFileHandle, 2));
                if (0 != $count['count']) {
                    $data[$cyl_no] += unpack("d" . $count['count'] . "P0_", fread($this->resFileHandle, 8 * $count['count']));
                }

                $count = unpack("v1count", fread($this->resFileHandle, 2));
                if (0 != $count['count']) {
                    $data[$cyl_no] += unpack("d" . $count['count'] . "P36_", fread($this->resFileHandle, 8 * $count['count']));
                }

                $count = unpack("v1count", fread($this->resFileHandle, 2));
                if (0 != $count['count']) {
                    $data[$cyl_no] += unpack("d" . $count['count'] . "PeMax_", fread($this->resFileHandle, 8 * $count['count']));
                }

                $count = unpack("v1count", fread($this->resFileHandle, 2));
                if (0 != $count['count']) {
                    $data[$cyl_no] += unpack("d" . $count['count'] . "APeMax_", fread($this->resFileHandle, 8 * $count['count']));
                }

                $count = unpack("v1count", fread($this->resFileHandle, 2));
                if (0 != $count['count']) {
                    $data[$cyl_no] += unpack("d" . $count['count'] . "Peb_", fread($this->resFileHandle, 8 * $count['count']));
                }

                $count = unpack("v1count", fread($this->resFileHandle, 2));
                if (0 != $count['count']) {
                    $data[$cyl_no] += unpack("d" . $count['count'] . "APeb_", fread($this->resFileHandle, 8 * $count['count']));
                }

                $count = unpack("v1count", fread($this->resFileHandle, 2));
                if (0 != $count['count']) {
                    $data[$cyl_no] += unpack("d" . $count['count'] . "APee_", fread($this->resFileHandle, 8 * $count['count']));
                }

                $count = unpack("v1count", fread($this->resFileHandle, 2));
                if (0 != $count['count']) {
                    $data[$cyl_no] += unpack("d" . $count['count'] . "APfb_", fread($this->resFileHandle, 8 * $count['count']));
                }

                $count = unpack("v1count", fread($this->resFileHandle, 2));
                if (0 != $count['count']) {
                    $data[$cyl_no] += unpack("d" . $count['count'] . "APfe_", fread($this->resFileHandle, 8 * $count['count']));
                }

                $count = unpack("v1count", fread($this->resFileHandle, 2));
                if (0 != $count['count']) {
                    $data[$cyl_no] += unpack("d" . $count['count'] . "Afd_", fread($this->resFileHandle, 8 * $count['count']));
                }

                $count = unpack("v1count", fread($this->resFileHandle, 2));
                if (0 != $count['count']) {
                    $data[$cyl_no] += unpack("d" . $count['count'] . "Azv_", fread($this->resFileHandle, 8 * $count['count']));
                }

                $count = unpack("v1count", fread($this->resFileHandle, 2));
                if (0 != $count['count']) {
                    $data[$cyl_no] += unpack("d" . $count['count'] . "Aev_", fread($this->resFileHandle, 8 * $count['count']));
                }

                $count = unpack("v1count", fread($this->resFileHandle, 2));
                if (0 != $count['count']) {
                    $data[$cyl_no] += unpack("d" . $count['count'] . "PScav_", fread($this->resFileHandle, 8 * $count['count']));
                }

                $count = unpack("v1count", fread($this->resFileHandle, 2));
                if (0 != $count['count']) {
                    $data[$cyl_no] += unpack("d" . $count['count'] . "Aed_", fread($this->resFileHandle, 8 * $count['count']));
                }

                $count = unpack("v1count", fread($this->resFileHandle, 2));
                if (0 != $count['count']) {
                    $data[$cyl_no] += unpack("d" . $count['count'] . "Pfb_", fread($this->resFileHandle, 8 * $count['count']));
                }

                $count = unpack("v1count", fread($this->resFileHandle, 2));
                if (0 != $count['count']) {
                    $data[$cyl_no] += unpack("d" . $count['count'] . "Avb_", fread($this->resFileHandle, 8 * $count['count']));
                }

                $count = unpack("v1count", fread($this->resFileHandle, 2));
                if (0 != $count['count']) {
                    $data[$cyl_no] += unpack("d" . $count['count'] . "CapCorr_", fread($this->resFileHandle, 8 * $count['count']));
                }

                $count = unpack("v1count", fread($this->resFileHandle, 2));
                if (0 != $count['count']) {
                    $data[$cyl_no] += unpack("s" . $count['count'] . "CPAValues_", fread($this->resFileHandle, 2 * $count['count']));
                }

                //            echo "Cyl. No.: ".$cyl_no."\n";
                $sql_pressure = "INSERT IGNORE INTO `mpd_pressure_curve_data` ( `MarPrime_SerialNo` , `date` , `MeasurementTime` , `cyl_no` , `x_val` , `y_val` , `revolution` )VALUES ";
                if ($count['count']) {
                    for ($i = 1; $i <= $count['count']; $i++) {
                        $sql_pressure .= "('" . $this->arrHeader['SerialNo'] . "', '" . $this->arrHeader['LastUpdate'] . "', '" . date("Y-m-d H:i:s", $data[$cyl_no]['MeasurementTime']) . "', '" . $cyl_no . "', '" . $i . "', '" . $data[$cyl_no]['CPAValues_' . $i] . "', '" . $data[$cyl_no]['Revolution_1'] * 60 . "'), ";
                    }

                    $sql_pressure = substr($sql_pressure, 0, strlen($sql_pressure) - 2);
                    $this->executeQuery($sql_pressure, 'marprime');
                    // $erg = mysql_query($sql_pressure, $_SESSION['DB_CONNECTION']['MarPrime']);
                    // @todo: Exception abfangen/loggen?
                    // Im Moment wird nächste Mail bearbeitet, ok?
                    /*
                if (!$erg)
                echo mysql_error() . "\n" . $sql_pressure . "\n";
                 */
                }
                //            echo $sql_pressure."\n";
                $count = unpack("v1count", fread($this->resFileHandle, 2));
                if (0 != $count['count']) {
                    $data[$cyl_no] += unpack("s" . $count['count'] . "IPAValues_", fread($this->resFileHandle, 2 * $count['count']));
                }

                $sql_acoustic = "INSERT IGNORE INTO `mpd_ae_curve_data` ( `MarPrime_SerialNo` , `date`, `MeasurementTime` , `cyl_no` , `x_val` , `y_val` , `revolution` )VALUES ";
                if ($count['count']) {
                    for ($i = 1; $i <= $count['count']; $i++) {
                        $sql_acoustic .= "('" . $this->arrHeader['SerialNo'] . "', '" . $this->arrHeader['LastUpdate'] . "', '" . date("Y-m-d H:i:s", $data[$cyl_no]['MeasurementTime']) . "', '" . $cyl_no . "', '" . $i . "', '" . $data[$cyl_no]['IPAValues_' . $i] . "', '" . $data[$cyl_no]['Revolution_1'] * 60 . "'), ";
                    }

                    $sql_acoustic = substr($sql_acoustic, 0, strlen($sql_acoustic) - 2);
                    // $erg = mysql_query($sql_acoustic, $_SESSION['DB_CONNECTION']['MarPrime']);
                    $this->executeQuery($sql_acoustic, 'marprime');

                    // @todo: Exception abfangen/loggen?
                    // if (!$erg)
                    //     echo mysql_error() . "\n" . $sql_acoustic . "\n";
                    //                else
                    //                    echo "Ultrasonic Values Cyl($cyl_no): ".mysql_info()."\n";
                }
                //            echo $sql_acoustic."\n";
                if ($count['count']) {
                    $history[$cyl_no]->revolution = 0;
                    $history[$cyl_no]->scav_air = 0;
                    $history[$cyl_no]->comp_pressure = 0;
                    $history[$cyl_no]->max_pressure = 0;
                    $history[$cyl_no]->mean_ind_pressure = 0;
                    $history[$cyl_no]->ind_power = 0;
                    $history[$cyl_no]->angle_pmax = 0;
                    $history[$cyl_no]->pcomp_rel_pscav = 0;
                    $history[$cyl_no]->leakage = 0;
                    $history[$cyl_no]->MeasurementTime = 0;
                    for ($i = 1; $i <= $counter; $i++) {
                        $history[$cyl_no]->revolution += $data[$cyl_no]['Revolution_' . $i] * 60;
                        $history[$cyl_no]->scav_air += $data[$cyl_no]['PScav_' . $i] * 10;
                        $history[$cyl_no]->comp_pressure += $data[$cyl_no]['P0_' . $i] * 10;
                        $history[$cyl_no]->max_pressure += $data[$cyl_no]['PMax_' . $i] * 10;
                        $history[$cyl_no]->mean_ind_pressure += $data[$cyl_no]['Pim_' . $i] * 10;
                        $history[$cyl_no]->ind_power += $data[$cyl_no]['indPower_' . $i];
                        $history[$cyl_no]->angle_pmax += $data[$cyl_no]['APMax_' . $i];
                        $history[$cyl_no]->pcomp_rel_pscav += $data[$cyl_no]['P0_' . $i] / $data[$cyl_no]['PScav_' . $i];
                        $history[$cyl_no]->leakage += $data[$cyl_no]['Aev_' . $i] * 100;
                    }
                    $history[$cyl_no]->revolution /= $counter;
                    $history[$cyl_no]->scav_air /= $counter;
                    $history[$cyl_no]->comp_pressure /= $counter;
                    $history[$cyl_no]->max_pressure /= $counter;
                    $history[$cyl_no]->mean_ind_pressure /= $counter;
                    $history[$cyl_no]->ind_power /= $counter;
                    $history[$cyl_no]->angle_pmax /= $counter;
                    $history[$cyl_no]->pcomp_rel_pscav /= $counter;
                    $history[$cyl_no]->leakage /= $counter;
                }
            }
            for ($i = 1; $i <= $counter; $i++) {
                $sql .= "('" . $this->arrHeader['SerialNo'] . "', '" . $this->arrHeader['LastUpdate'] . "', '" . date("Y-m-d H:i:s", $data[$cyl_no]['MeasurementTime']) . "', '" . $cyl_no . "', '" . $i . "', '" . $data[$cyl_no]['CalcFail'] . "', '" . $data[$cyl_no]['Cycles'] . "', '" . $data[$cyl_no]['CycleLengthCPA'] . "', '" . $data[$cyl_no]['CycleLengthIPA'] . "', '" . $data[$cyl_no]['CylStat'] . "', '" . $data[$cyl_no]['IpaStartAngle'] . "', '" . $data[$cyl_no]['IpaAngleSteps'] . "', '" . $data[$cyl_no]['TdcCorrected'] . "', '" . $data[$cyl_no]['ScavMeasured'] . "', '" . $data[$cyl_no]['OscDone'] . "', '" . $data[$cyl_no]['Revolution_' . $i] * 60 . "', '" . $data[$cyl_no]['indPower_' . $i] . "', '" . $data[$cyl_no]['Vdp_' . $i] . "', '" . $data[$cyl_no]['Pil_' . $i] . "', '" . $data[$cyl_no]['PMax_' . $i] . "', '" . $data[$cyl_no]['APMax_' . $i] . "', '" . $data[$cyl_no]['Pim_' . $i] . "', '" . $data[$cyl_no]['Pilw_' . $i] . "', '" . $data[$cyl_no]['P0_' . $i] . "', '" . $data[$cyl_no]['P36_' . $i] . "', '" . $data[$cyl_no]['PeMax_' . $i] . "', '" . $data[$cyl_no]['APeMax_' . $i] . "', '" . $data[$cyl_no]['Peb_' . $i] . "', '" . $data[$cyl_no]['APeb_' . $i] . "', '" . $data[$cyl_no]['APee_' . $i] . "', '" . $data[$cyl_no]['APfb_' . $i] . "', '" . $data[$cyl_no]['APfe_' . $i] . "', '" . $data[$cyl_no]['Afd_' . $i] . "', '" . $data[$cyl_no]['Azv_' . $i] . "', '" . $data[$cyl_no]['Aev_' . $i] . "', '" . $data[$cyl_no]['PScav_' . $i] . "', '" . $data[$cyl_no]['Aed_' . $i] . "', '" . $data[$cyl_no]['Pfb_' . $i] . "', '" . $data[$cyl_no]['Avb_' . $i] . "', '" . $data[$cyl_no]['CapCorr_' . $i] . "'), ";
            }

            //        else
            //            var_dump("<pre>", $data[$cyl_no], "</pre>");
            $text = fread($this->resFileHandle, 2);
            //        echo $text."\n";
            $text = trim(fread($this->resFileHandle, 5));
            //        echo $text."\n";
        }

        $meastime = 0;
        $no = 0;
        foreach ($data as $key => $value) {
            if (0 < $key && is_int($key)) {
                $meastime += $value['MeasurementTime'];
                if ($value['MeasurementTime'] > 100000) {
                    $no++;
                }

            }
        }
        $meastime = floor($meastime / $no);
        //    echo date("Y-m-d H:i:s", $meastime);
        foreach ($history as $key => $value) {
            $sql_history = "INSERT IGNORE INTO `mpd_history` (`MarPrime_SerialNo`, `date`, `MeasurementTime`, `revolution`, `cyl_no`, `scav_air`, `comp_pressure`, `max_pressure`, `mean_ind_pressure`, `ind_power`, `angle_pmax`, `pcomp_rel_pscav`, `leakage`) VALUES
					('" . $this->arrHeader['SerialNo'] . "', '" . $this->arrHeader['LastUpdate'] . "', '" . date("Y-m-d H:i:s", $meastime) . "', '" . $value->revolution . "', '" . $key . "', '" . $value->scav_air . "', '" . $value->comp_pressure . "', '" . $value->max_pressure . "', '" . $value->mean_ind_pressure . "', '" . $value->ind_power . "', '" . $value->angle_pmax . "', '" . $value->pcomp_rel_pscav . "', '" . $value->leakage . "')";
            //        echo "\n".$sql_history."<hr>";

            $this->executeQuery($sql_history, 'marprime');
            // @todo: Exception abfangen/loggen?
            // $erg = mysql_query($sql_history, $_SESSION['DB_CONNECTION']['MarPrime']);
            // if (!$erg)
            //     echo mysql_error() . "\n"; //.$sql_history."\n";
            //         else
            //             echo "History Values Cyl($key): ".mysql_affected_rows()."\n";
        }
        fseek($this->resFileHandle, -6, SEEK_CUR);
        // Last Measurement und load value in shiptable
        $sql = substr($sql, 0, strlen($sql) - 2);
        $this->executeQuery($sql, 'marprime');
        // $erg = mysql_query($sql, $_SESSION['DB_CONNECTION']['MarPrime']);
        // @todo: Exception abfangen/loggen?
        // if (!$erg) {
        //     echo mysql_error() . "\n" . $sql . "\n";
        // }

        $sum_ind = 0;
        $max_ind = 0;
        $min_ind = 100000;
        $Measured_cyl = 0;
        for ($i = 1; $i <= $cyl_no; $i++) {
            $sum_ind += $history[$i]->ind_power;
            if (0 < $history[$i]->ind_power) {
                $Measured_cyl++;
            }

            if ($history[$i]->ind_power < $min_ind) {
                $min_ind = $history[$i]->ind_power;
            }

            if ($history[$i]->ind_power > $max_ind) {
                $max_ind = $history[$i]->ind_power;
            }

        }
        if ($sum_ind > 0) {
            $m_ind = round((($max_ind - $min_ind) * 100) / ($sum_ind / $Measured_cyl), 2);
        }

        $sql = "UPDATE ship_table SET load_value = " . $m_ind . ", load_date = '" . date("Y-m-d", $meastime) . "' WHERE MarPrime_SerialNo = '" . $this->arrHeader['SerialNo'] . "'";
        $this->executeQuery($sql);
        // if (!mysql_query($sql, $_SESSION['DB_CONNECTION']['usr_web7_1'])) {
        //     echo "Fehler beim Update des 'load_balance' Wertes.\n";
        // }
        // @todo: Exception abfangen/loggen?

        return $data;
    }

    public function getEngineParams($datei, $fileheader, $MT)
    {
        // echo "Receiving Engine Parameters...\n";
        $count = unpack("v1count", fread($datei, 2));
        if (0 != $count['count']) {
            $params = unpack("a" . $count['count'] . "EngName", fread($datei, $count['count'] + 1));
        }

        $count = unpack("v1count", fread($datei, 2));
        if (0 != $count['count']) {
            $params += unpack("a" . $count['count'] . "EngType", fread($datei, $count['count'] + 1));
        }

        $cyl = unpack("v1cyl", fread($datei, 2));
        $params['cyl_count'] = $cyl['cyl'];
        $params += unpack("v" . $params['cyl_count'] . "FireAngle_/v1Strokes/d1Speed/d1Power/d1Stroke/d1Bore/d1ConnRatio/d1CompRatio/s1InletOpen/s1InletClose/s1OutletOpen/s1OutletClose/V1LastChange/d1KappaCorr", fread($datei, $params['cyl_count'] * 2 + 70));

//      echo "Time last changed: ".date("Y-m-d H:i:s", $params['LastChange'])."\n".$fileheader['SerialNo'];
        $sql = "INSERT IGNORE INTO `engine_params` ( `MarPrime_SerialNo` , `date` , `MeasurementTime` , `engine_name` , `engine_type` , `cyl_count` , `fire_angle` , `strokes` , `speed` , `power` , `stroke` , `bore` , `connection_ratio` , `compression_ratio` , `inlet_open` , `inlet_close` , `outlet_open` , `outlet_close` , `cappa_correction` , `last_change` )
			VALUES ('" . $fileheader['SerialNo'] . "', '" . $fileheader['LastUpdate'] . "', '" . date("Y-m-d H:i:s", $MT) . "', '" . $params['EngName'] . "', '" . $params['EngType'] . "', '" . $params['cyl_count'] . "', '";
        for ($i = 1; $i <= $params['cyl_count']; $i++) {
            $sql .= $params['FireAngle_' . $i] . "; ";
        }

        $sql .= "', '" . $params['Strokes'] . "', '" . $params['Speed'] . "', '" . $params['Power'] . "', '" . $params['Stroke'] . "', '" . $params['Bore'] . "', '" . $params['ConnRatio'] . "', '" . $params['CompRatio'] . "', '" . $params['InletOpen'] . "', '" . $params['InletClose'] . "', '" . $params['OutletOpen'] . "', '" . $params['OutletClose'] . "', '" . $params['KappaCorr'] . "', '" . date("Y-m-d H:i:s", $params['LastChange']) . "')";
        // mysql_select_db($_SESSION['DB3']);
        $this->executeQuery($sql, 'marprime');
        // @todo: Exception abfangen/loggen?
        // $erg = mysql_query($sql, $_SESSION['DB_CONNECTION']['MarPrime']);
        // if (!$erg) {
        //     echo mysql_error() . "\n";
        // }
        //.$sql."\n";
        //    else
        //        echo "Engine Parameters: ".mysql_affected_rows()." ".mysql_info()."\n";
        return $params;
    }

    public function getMeasurementParams($datei, $fileheader)
    {
        // echo "Receiving Measurement Parameters...\n";
        $params = unpack("v1Cycles/v1sets", fread($datei, 4));
        for ($i = 0; $i < $params['sets']; $i++) {
            $count = unpack("v1count", fread($datei, 2));
            if (0 != $count['count']) {
                $params['CPA'][$i] = unpack("a" . $count['count'] . "SensorNum", fread($datei, $count['count']));
            }

            $params['CPA'][$i] += unpack("v1Channel/d1Calibration/d1Offset/", fread($datei, 18));
        }
        $count = unpack("v1sets", fread($datei, 2));
        for ($i = 0; $i < $count['sets']; $i++) {
            $count = unpack("v1count", fread($datei, 2));
            if (0 != $count['count']) {
                $params['IPA'][$i] = unpack("a" . $count['count'] . "SensorNum", fread($datei, $count['count']));
            }

            $params['IPA'][$i] += unpack("v1Channel/d1Calibration/d1Offset/", fread($datei, 18));
        }
        $count = unpack("v1count", fread($datei, 2));
        if (0 != $count['count']) {
            $params['AE'][$i] = unpack("a" . $count['count'] . "SensorNum", fread($datei, $count['count']));
            $params['AE'][$i] += unpack("v1Channel/d1Calibration/d1Offset/", fread($datei, 18));
        }
        $params += unpack("d1AnglePComp/V1CPAAmpRange/d1IPAAmpValue/v1IPAMode/v1PscavFlag/v1TDCFlag/v1OSCFlag/d1Convergence/d1Alpha1Corr/d1Alpha2Corr/d1StartFeedMin/d1StartFeedMax/d1StartInjSoll/d1SearchFeedBegin/d1SearchInjBegin/d1SearchFeedEnd/v1MonitorTime/s1CalcRangeLeft1/s1CalcRangeLeft2/s1CalcRangeRight1/s1CalcRangeRight2/d1LimitNormalisation1/d1LimitNormalisation2/d1DiagRange1/d1DiagRange2/d1DiagWeight1/d1DiagWeight2/d1AEAmplification/d1LimitPMax/d1LimitPComp/d1LimitPower/", fread($datei, 190));
        $sql = "INSERT IGNORE INTO `measurement_params` ( `MarPrime_SerialNo` , `date` , `convergence` , `alpha1` , `alpha2` , `monitor` , `leakage_left_start` , `leakage_left_end` , `leakage_right_start` , `leakage_right_end` , `normalisation_limit_start` , `normalisation_limit_end` , `diagnostic_range_start` , `diagnostic_range_end` , `diagnostic_weight_start` , `diagnostic_weight_end` , `ae_amplification` , `limit_pmax` , `limit_pcomp` , `limit_power` )
    VALUES ('" . $fileheader['SerialNo'] . "', '" . $fileheader['LastUpdate'] . "', '" . $params['Convergence'] . "', '" . $params['Alpha1Corr'] . "', '" . $params['Alpha2Corr'] . "', '" . $params['MonitorTime'] . "', '" . $params['CalcRangeLeft1'] . "', '" . $params['CalcRangeLeft2'] . "', '" . $params['CalcRangeRight1'] . "', '" . $params['CalcRangeRight2'] . "', '" . $params['LimitNormalisation1'] . "', '" . $params['LimitNormalisation2'] . "', '" . $params['DiagRange1'] . "', '" . $params['DiagRange2'] . "', '" . $params['DiagWeight1'] . "', '" . $params['DiagWeight2'] . "', '" . $params['AEAmplification'] . "', '" . $params['LimitPMax'] . "', '" . $params['LimitPComp'] . "', '" . $params['LimitPower'] . "')";
        $this->executeQuery($sql, 'marprime');
        // @todo: Exception abfangen/loggen?
        // $erg = mysql_query($sql, $_SESSION['DB_CONNECTION']['MarPrime']);
        // if (!$erg) {
        // echo mysql_error() . "\n";
        // }
        return $params;
    }

    public function getProgramParams($datei, $fileheader)
    {
        // echo "Receiving Program Parameters...\n";
        $name = array("Verzeichnis",
            "Laufwerk",
            "Firma",
            "Produkt",
            "SoftwareVersion",
            "Release",
            "Build",
            "Comment",
            "RegRootKey",
            "DatenVerzeichnis",
            "RohdatenVerzeichnis",
            "KonfigurationenVerzeichnis",
            "ReportVerzeichnis",
            "StatistikenVerzeichnis",
            "Seriennummer",
            "Lizenzschlüssel");
        $params = array();
        for ($i = 0; $i <= 15; $i++) {
            $count = unpack("v1count", fread($datei, 2));
            if (0 != $count['count']) {
                $params += unpack("a" . $count['count'] . $name[$i], fread($datei, $count['count'] + 1));
            } else {
                fread($datei, 1);
            }

        }
        $params += unpack("V1reg_mod/V1auto_modules/V1inst_modules/V1disp_modules/V1active_modul/V1elapse_time/v1current_eng/v1licence_type/v1debug_mode/v1press_unit/v1status/", fread($datei, 34));

        foreach (array_keys($params) as $keys) {
            if (substr($params[$keys], -1) == "\\") {
                $params[$keys] = substr($params[$keys], 0, -1);
            }
        }

        //var_dump("<pre>", $params, "</pre>");
        $sql = "INSERT IGNORE INTO `program_params` ( `MarPrime_SerialNo` , `date` , `installpath` , `company` , `product` , `sw_version` , `comment` , `reg_root_key` , `datadir` , `rawdatadir` , `configdir` , `reportdir` , `statisticsdir` , `serial` , `key` , `reg_mod` , `auto_modules` , `inst_modules` , `disp_modules` , `active_modules` , `elapse_time` , `current_eng` , `licence_type` , `debug_mode` , `press_unit` , `status`)
        VALUES ('" . $fileheader['SerialNo'] . "', '" . $fileheader['LastUpdate'] . "', '" . $params[$name[1]] . $params[$name[0]] . "', '" . $params[$name[2]] . "', '" . $params[$name[3]] . "', '" . $params[$name[4]] . "." . $params[$name[5]] . "." . $params[$name[6]] . "', '" . $params[$name[7]] . "', '" . $params[$name[8]] . "'
                , '" . $params[$name[9]] . "', '" . $params[$name[10]] . "', '" . $params[$name[11]] . "', '" . $params[$name[12]] . "', '" . $params[$name[13]] . "', '" . $params[$name[14]] . "', '" . $params[$name[15]] . "'
                , '" . $params['reg_mod'] . "', '" . $params['auto_modules'] . "', '" . $params['inst_modules'] . "', '" . $params['disp_modules'] . "', '" . $params['active_modul'] . "', '" . $params['elapse_time'] . "', '" . $params['current_eng'] . "', '" . $params['licence_type'] . "', '" . $params['debug_mode'] . "', '" . $params['press_unit'] . "', '" . $params['status'] . "')";
        $this->executeQuery($sql, 'marprime');
        // @todo: Exception abfangen/loggen?
        // $erg = mysql_query($sql, $_SESSION['DB_CONNECTION']['MarPrime']);
        // if (!$erg) {
        //     echo mysql_error();
        // }
        return $params;
    }

    public function getReportData($datafile, $fileheader, $measurement_time, $EngName, $EngType, $MPDFileName)
    {
        // echo "Receiving Report Data...\n";
        $global = unpack("V1MaskVersion/V1countRepName", fread($datafile, 8));
        $global += unpack("a" . $global['countRepName'] . "RepName/V1countTabs/V1countRepVersion", fread($datafile, $global['countRepName'] + 8));
        $global += unpack("a" . $global['countRepVersion'] . "RepVersion/V1Strokes/V1countEngName", fread($datafile, $global['countRepVersion'] + 8));
        $global += unpack("a" . $global['countEngName'] . "EngName/V1countEngType", fread($datafile, $global['countEngName'] + 4));
        $global += unpack("a" . $global['countEngType'] . "EngType", fread($datafile, $global['countEngType']));
        $Report['global'] = $global;
        $sql = "INSERT IGNORE INTO `report` (`MarPrimeSerial`, `Date`, `MPDFileName`, `EngineName`, `EngineType`, `ReportName`, `ReportVersion`, `XMLVersion`, `Strokes`, `TabCount`) VALUES
	('" . $fileheader['SerialNo'] . "', '" . date("Y-m-d H:i:s", $measurement_time) . "', '" . $MPDFileName . "', '" . $EngName . "', '" . $EngType . "', '" . $global['RepName'] . "', '" . $global['RepVersion'] . "', '" . $global['MaskVersion'] . "', '" . $global['Strokes'] . "', '" . $global['countTabs'] . "');";
        // SELECT ReportID FROM `report` ORDER BY ReportID DESC LIMIT 1";
        $ReportID = (int) $this->executeInsert($sql, 'marprime');
        if (!$ReportID) {
            // Insert IGNORE --> gibt es schon, könnte raus, aber muss die Bytes im File abarbeiten
            // return $Report;
        }
        // @todo: Exception abfangen/loggen?

        // $mysqli = new mysqli("localhost", "dbo00066364", "S2uhunvB2rnV", "db00066364");

        /* check connection */
        // if (mysqli_connect_errno()) {
        //     printf("Connect failed: %s\n", mysqli_connect_error());
        //     exit();
        // }
        /* execute multi query */
        /*
        if ($mysqli->multi_query($sql)) {
        do {
        if ($result = $mysqli->use_result()) {
        while ($row = $result->fetch_row()) {
        $ReportID = $row[0];
        }
        $result->close();
        }
        if ($mysqli->more_results()) {
        }
        } while ($mysqli->next_result());
        } else {
        die("Fehler in Query: " . $mysqli->error);
        }
         */
        /* close connection */
        // $mysqli->close();

        // echo "\n" . $global['RepName'] . ": '" . $global['RepVersion'] . "'\n";
        $sql_tabs = "INSERT IGNORE INTO `report_tabs` (`ReportID` ,`Tab` ,`Name` ,`BoxCount`) VALUES ";
        for ($a = 0; $a < $global['countTabs']; $a++) {
            $tab = unpack("a6Tab/V1countTabName", fread($datafile, 10));
            $tabs[$tab['Tab']] = unpack("a" . $tab['countTabName'] . "TabName/V1countBoxes", fread($datafile, $tab['countTabName'] + 4));
            $sql_tabs .= "('" . $ReportID . "', '" . $a . "', '" . $tabs[$tab['Tab']]['TabName'] . "', '" . $tabs[$tab['Tab']]['countBoxes'] . "'), ";
            $sql_boxes = "INSERT IGNORE INTO `report_groupboxes` (`ReportID` ,`Box` ,`Tab` ,`Name` ,`FieldCount` ,`Top` ,`Height`) VALUES ";
            for ($b = 0; $b < $tabs[$tab['Tab']]['countBoxes']; $b++) {
                $box = unpack("a6Box/V1countBoxName", fread($datafile, 10));
                $boxes[$box['Box']] = unpack("a" . $box['countBoxName'] . "BoxName/V1Top/V1Height/V1countFields", fread($datafile, $box['countBoxName'] + 12));
                $sql_boxes .= "('" . $ReportID . "', '" . $b . "', '" . $a . "', '" . $boxes[$box['Box']]['BoxName'] . "', '" . $boxes[$box['Box']]['countFields'] . "', '" . $boxes[$box['Box']]['Top'] . "', '" . $boxes[$box['Box']]['Height'] . "'), ";
                $sql_fields = "INSERT IGNORE INTO report_fields ( ReportID, FieldID, Box, Tab, Top, LeftPoint, Height, Width, Type, Name, BoldFont, Value, Hint, TabOrder, Minval, Maxval, Maxlength, ValuePrecision, ItemIndex) VALUES ";
                for ($c = 0; $c < $boxes[$box['Box']]['countFields']; $c++) {
                    $field = unpack("a6Field/V1countFieldName", fread($datafile, 10));
                    $fields[$field['Field']] = unpack("a" . $field['countFieldName'] . "FieldName/V1countFieldType", fread($datafile, $field['countFieldName'] + 4));
                    $fields[$field['Field']] += unpack("a" . $fields[$field['Field']]['countFieldType'] . "FieldType/V1countFieldHint", fread($datafile, $fields[$field['Field']]['countFieldType'] + 4));
                    $fields[$field['Field']] += unpack("a" . $fields[$field['Field']]['countFieldHint'] . "FieldHint/V1Top/V1Left/V1Height/V1Width/V1numValues", fread($datafile, $fields[$field['Field']]['countFieldHint'] + 20));
                    for ($d = 0; $d < $fields[$field['Field']]['numValues']; $d++) {
                        $count = unpack("V1countValueLength", fread($datafile, 4));
                        $tmp = array_values(unpack("a" . $count['countValueLength'], fread($datafile, $count['countValueLength'])));
                        $fields[$field['Field']]['Values'] .= $tmp[0] . "\n";
                    }
                    switch ($fields[$field['Field']]['FieldType']) {
                        case 'valedit':$fields[$field['Field']]['Special'] = unpack("d1MinVal/d1MaxVal/d1MaxLength/d1Precision", fread($datafile, 32));
                            break;
                        case 'combobox':$fields[$field['Field']]['Special'] = unpack("V1ItemIndex", fread($datafile, 4));
                            break;
                        case 'stringgrid':$tmp = unpack("V1ColCount", fread($datafile, 4));
                            $sql_stringgrids = "INSERT IGNORE INTO `report_stringgrids` (`ReportID`, `Tab`, `Box`, `Field`, `Colnum`, `Values`) VALUES ";
                            for ($i = 0; $i < $tmp['ColCount']; $i++) {
                                $countColNames = unpack("V1countColName", fread($datafile, 4));
                                $ColNames = array_values(unpack("a" . $countColNames['countColName'] . "/V1countCells", fread($datafile, $countColNames['countColName'] + 4)));
                                $fields[$field['Field']]['Special'][$i]['Name'] = $ColNames[0];
                                $values = "";
                                for ($j = 0; $j < $ColNames[1]; $j++) {
                                    $countCells = unpack("V1countCells", fread($datafile, 4));
                                    $Cells = array_values(unpack("a" . $countCells['countCells'], fread($datafile, $countCells['countCells'])));
                                    $fields[$field['Field']]['Special'][$i]['Cells'][$j] = $Cells[0];
                                    $values .= $Cells[0] . "##";

                                }
                                $sql_stringgrids .= "('" . $ReportID . "', '" . $c . "', '" . $b . "', '" . $a . "', '" . $i . "', '" . $values . "'), ";
                            }
                            // nur wenn der Report neu ist
                            if ($ReportID) {
                                $this->executeQuery(substr($sql_stringgrids, 0, strlen($sql_stringgrids) - 2), 'marprime');
                            }
                            // @todo: Exception abfangen/loggen?
                            // $erg = mysql_query(substr($sql_stringgrids, 0, strlen($sql_stringgrids) - 2), $_SESSION['DB_CONNECTION']['MarPrime']);
                            // if (!$erg) {
                            //     echo mysql_error() . "\n";
                            // }
                            break;
                    }
                    $fields[$field['Field']] += unpack("a1Bold/V1TabOrder", fread($datafile, 5));
                    $fields[$field['Field']]['Values'] = str_replace("'", "\'", $fields[$field['Field']]['Values']);
                    $fields[$field['Field']]['FieldHint'] = str_replace("'", "\'", $fields[$field['Field']]['FieldHint']);
                    $sql_fields .= " ( '" . $ReportID . "', '" . $c . "', '" . $b . "', '" . $a . "', '" . $fields[$field['Field']]['Top'] . "', '" . $fields[$field['Field']]['Left'] . "', '" . $fields[$field['Field']]['Height'] . "',
				'" . $fields[$field['Field']]['Width'] . "', '" . $fields[$field['Field']]['FieldType'] . "', '" . $fields[$field['Field']]['FieldName'] . "', '" . $fields[$field['Field']]['Bold'] . "',
				'" . $fields[$field['Field']]['Values'] . "', '" . $fields[$field['Field']]['FieldHint'] . "', '" . $fields[$field['Field']]['TabOrder'] . "', '" . $fields[$field['Field']]['Special']['MinVal'] . "',
				'" . $fields[$field['Field']]['Special']['MaxVal'] . "', '" . $fields[$field['Field']]['Special']['MaxLength'] . "', '" . $fields[$field['Field']]['Special']['Precision'] . "', '" . $fields[$field['Field']]['Special']['ItemIndex'] . "'), ";
                    if ("[_FLD]" != ($tmp = fread($datafile, 6))) {
                        //     echo "Tab: " . $a . "|Box: " . $b . "|Field: " . $c . "|Text: " . $tmp . "\n";
                        //     var_dump("<pre>", $fields, "</pre>");
                    }
                }
/**/
// nur wenn der Report neu ist
                if ($ReportID) {
                    $this->executeQuery(substr($sql_fields, 0, strlen($sql_fields) - 2), 'marprime');
                }
                // @todo: Exception abfangen/loggen?
                // $erg = mysql_query(substr($sql_fields, 0, strlen($sql_fields) - 2), $_SESSION['DB_CONNECTION']['MarPrime']);
                // if (!$erg) {
                //     echo mysql_errno() . ": " . mysql_error() . "\n" . substr($sql_fields, 0, strlen($sql_fields) - 2) . "\n";
                // }

//             else
                //                 echo "Report Fields: ".mysql_info()." (".mysql_affected_rows().")\n";

                $boxes[$box['Box']]['fields'] = $fields;
                if ("[_BOX]" != ($tmp = fread($datafile, 6))) {
                    //     echo "Tab: " . $a . "|Box: " . $b . "|Text: " . $tmp . "\n";
                }

            }
            // nur wenn der Report neu ist
            if ($ReportID) {
                $this->executeQuery(substr($sql_boxes, 0, strlen($sql_boxes) - 2), 'marprime');
            }
            // @todo: Exception abfangen/loggen?
            // $erg = mysql_query(substr($sql_boxes, 0, strlen($sql_boxes) - 2), $_SESSION['DB_CONNECTION']['MarPrime']);
            // if (!$erg) {
            //     echo mysql_error() . "\n";
            // }
            //.$sql_boxes."\n";
            //         else
            //             echo "Report Groupboxes: ".mysql_affected_rows()."\n";
            $tabs[$tab['Tab']]['boxes'] = $boxes;
            if ("[_TAB]" != ($tmp = fread($datafile, 6))) {
                //     echo "Tab: " . $a . "|Box: " . $b . "|Field: " . $c . " Text: " . $tmp . "\n";
            }

        }
        // nur wenn der Report neu ist
        if ($ReportID) {
            $this->executeQuery(substr($sql_tabs, 0, strlen($sql_tabs) - 2), 'marprime');
        }
        // @todo: Exception abfangen/loggen?
        // $erg = mysql_query(substr($sql_tabs, 0, strlen($sql_tabs) - 2), $_SESSION['DB_CONNECTION']['MarPrime']);
        // if (!$erg) {
        //     echo mysql_error() . "\n";
        // }
        //.$sql_tabs."\n";
        //     else
        //         echo "Report Tabs: ".mysql_affected_rows()."\n";
        $Report['tabs'] = $tabs;
        //var_dump("<pre>", $Report, "</pre>");
        return $Report;
    }
}
