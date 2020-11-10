<?php

namespace App\Repository\Marprime;

use App\Entity\Marprime\MpdMeasurementData;
use App\Exception\MscException;
use App\Kohana\Arr;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @method MpdMeasurementData|null find($id, $lockMode = null, $lockVersion = null)
 * @method MpdMeasurementData|null findOneBy(array $criteria, array $orderBy = null)
 * @method MpdMeasurementData[]    findAll()
 * @method MpdMeasurementData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MpdMeasurementDataRepository extends ServiceEntityRepository
{
    private $objParameterBag = null;
    private $arrParameterCache = [];

    public function __construct(ManagerRegistry $registry, ParameterBagInterface $parameterBag)
    {
        parent::__construct($registry, MpdMeasurementData::class);
        $this->objParameterBag = $parameterBag;
    }

    public function getBySerialNumberAndDate($strSerialNumber = null, $strDate = null, $intCountStrokes = 4)
    {
        extract($this->findByCheckCacheParameter([
            'strSerialNumber' => $strSerialNumber,
            'strDate' => $strDate,
            'intCountStrokes' => $intCountStrokes,
        ]));

        $arrResult = $this->createQueryBuilder('a')
            ->andWhere('a.marprimeSerialno = :mp_number')
            ->setParameter('mp_number', $strSerialNumber)
            ->andWhere('a.cylNo = :cylNo')
            ->setParameter('cylNo', 0)
            ->andWhere('a.date = :date')
            ->setParameter('date', $strDate)
            ->getQuery()
            ->getResult();
        // $arrRet = $objResult->fetchAll();
        foreach ($arrResult as $objRow) {
            $objRow->setStrokes($intCountStrokes);
        }
        return $arrResult;
    }

    /**
     * Liefert den Durchschnitt der indizierten Power.
     * Benötige ich die Berechnung der load-balance.
     *
     * @return float
     */
    public function retrieveLoadAvg($strSerialNumber = null, $strDate = null)
    {
        $objResult = $this->retrieveDbValue('SELECT AVG(ind_power) as avg_power FROM mpd_measurement_data WHERE MarPrime_SerialNo = :mp_number AND cyl_no = 0 AND date = :date', $strSerialNumber, $strDate);
        if (!$objResult->rowCount()) {
            return 0.0;
        }
        return (float) $objResult->fetchColumn(0);

    }

    public function retrieveRevolutionAvg($strSerialNumber = null, $strDate = null)
    {
        return (float) $this->retrieveDbValue('SELECT AVG(revolution) FROM mpd_measurement_data WHERE MarPrime_SerialNo = :mp_number AND cyl_no = 0 AND date = :date', $strSerialNumber, $strDate)->fetchColumn(0);
    }

    /**
     * liefert für jeden Zylinder min/max
     */
    public function retrieveMinMaxRevolution($strSerialNumber = null, $strDate = null, $intMax = 3, $floatAvgRevolution)
    {
        $strSQL = 'SELECT MIN(revolution) as min_revolution, MAX(revolution) as max_revolution, cyl_no FROM mpd_measurement_data WHERE cyl_no != 0 AND  MarPrime_SerialNo = :mp_number AND date = :date GROUP BY cyl_no HAVING min_revolution < ' . ($floatAvgRevolution - $intMax) . ' OR max_revolution > ' . ($floatAvgRevolution + $intMax);
        return $this->retrieveDbValue($strSQL, $strSerialNumber, $strDate);
    }

    public function retrieveOtCorrection($strSerialNumber = null, $strDate = null)
    {
        // habe in der DB gesehen, dass das Bitfeld bei cyl_no = 0 nicht gesetzt ist, jedoch bei cyl_no > 0
        // nehme hier dann die einzelnen messungen (cyl_no > 0)
        $strSQL = 'SELECT (calc_fail & b\'100\') as calc_fail, cyl_no FROM mpd_measurement_data WHERE cyl_no != 0  AND  MarPrime_SerialNo = :mp_number AND date = :date HAVING calc_fail = 8';
        return $this->retrieveDbValue($strSQL, $strSerialNumber, $strDate);
    }

    public function retrieveMeasurementCount($strSerialNumber = null, $strDate = null)
    {
        return (int) $this->retrieveDbValue('SELECT COUNT(measurement_num) as count FROM mpd_measurement_data WHERE MarPrime_SerialNo = :mp_number AND cyl_no = 0 AND date = :date', $strSerialNumber, $strDate)->fetchColumn(0);
    }

    public function retrieveDistinctMeasurementNums($strSerialNumber = null, $strDate = null)
    {
        return $this->retrieveDbValue('SELECT DISTINCT measurement_num as cyl_no FROM mpd_measurement_data WHERE MarPrime_SerialNo = :mp_number AND date = :date', $strSerialNumber, $strDate);
    }
    public function retrieveLimitPower($strSerialNumber = null, $strDate = null)
    {
        // limit der Abweichung (0,05 -> 5%)
        $strSQL = 'SELECT limit_power FROM measurement_params WHERE MarPrime_SerialNo = :mp_number AND date = :date LIMIT 1';
        return $this->retrieveDbValue($strSQL, $strSerialNumber, $strDate);
    }
    public function retrieveMeasurementNumFromAvgLoad($strSerialNumber = null, $strDate = null, $floatAvgMinLoad, $floatAvgMaxLoad)
    {
        // hole alle cyl_no, die eine Messung ausserhalb des Ranges haben
        // siehe email Hans 30.09.
        // checke gegen den cylinder avg (cyl_no = 0)
        $strSQL = 'SELECT measurement_num FROM mpd_measurement_data WHERE cyl_no = 0 AND MarPrime_SerialNo = :mp_number AND date = :date AND (ind_power < ' . $floatAvgMaxLoad . ' OR ind_power > ' . $floatAvgMinLoad . ');';
        return $this->retrieveDbValue($strSQL, $strSerialNumber, $strDate);
    }

    public function retrievePressureAvg($strSerialNumber = null, $strDate = null)
    {
        extract($this->findByCheckCacheParameter([
            'strSerialNumber' => $strSerialNumber,
            'strDate' => $strDate,
        ]));

        // $strCacheKey = md5(__METHOD__ . $strSerialNumber . $strDate);
        // $mixedValue = $this->objParameterBag->get($strCacheKey);
        // if ($mixedValue) {
        //     return $mixedValue;
        // }

        $strSQL = 'SELECT AVG(p0) as avg_p0 FROM mpd_measurement_data WHERE cyl_no = 0 AND  MarPrime_SerialNo = :mp_number AND date = :date;';
        $floatAvgLoad = (float) $this->retrieveDbValue($strSQL, $strSerialNumber, $strDate)->fetchColumn(0);
        // $this->objParameterBag->set($strCacheKey, $floatAvgLoad);
        return $floatAvgLoad;
    }

    public function retrieveP0Deviation($strSerialNumber = null, $strDate = null, $floatAvgMin, $floatAvgMax)
    {
        $strSQL = 'SELECT measurement_num as cyl_no FROM mpd_measurement_data WHERE cyl_no = 0  AND  MarPrime_SerialNo = :mp_number AND date = :date AND (p0 < ' . $floatAvgMin . ' OR p0 > ' . $floatAvgMax . ')';
        return $this->retrieveDbValue($strSQL, $strSerialNumber, $strDate);
    }

    public function retrieveMaxPressureAvg($strSerialNumber = null, $strDate = null)
    {

        $strSQL = 'SELECT AVG(pmax) as avg_pmax FROM mpd_measurement_data WHERE cyl_no = 0  AND  MarPrime_SerialNo = :mp_number AND date = :date';
        return (float) $this->retrieveDbValue($strSQL, $strSerialNumber, $strDate)->fetchColumn(0);
    }

    public function retrieveErrorMaxPressureAvg($strSerialNumber = null, $strDate = null, $floatLimitPressure)
    {
        // Durchschnitts-Druckwert der Zylinder
        $floatMaxPressure = $this->retrieveMaxPressureAvg($strSerialNumber, $strDate);

        $strSQL = 'SELECT measurement_num FROM mpd_measurement_data WHERE cyl_no = 0 AND  MarPrime_SerialNo = :mp_number AND date = :date AND (pmax < ' . ($floatMaxPressure - $floatMaxPressure) . ' OR pmax > ' . ($floatMaxPressure + $floatLimitPressure) . ')';

        return $this->retrieveDbValue($strSQL, $strSerialNumber, $strDate);
    }

    public function retrieveMeanIndicatedPressureValues($strSerialNumber = null, $strDate = null, $floatInductedPressure)
    {
        // Durchschnitts-Druckwert der Zylinder
        $floatMaxPressure = (float) $this->retrieveDbValue('SELECT AVG(pim) as avg_pim FROM mpd_measurement_data WHERE cyl_no = 0  AND  MarPrime_SerialNo = :mp_number AND date = :date', $strSerialNumber, $strDate)->fetchColumn(0);

        // hole alle zylinderdurchschnitte, die eine Messung ausserhalb des Ranges haben
        return $this->retrieveDbValue('SELECT measurement_num FROM mpd_measurement_data WHERE cyl_no = 0  AND  MarPrime_SerialNo = :mp_number AND date = :date AND (pim < ' . ($floatMaxPressure * (1 - $floatInductedPressure)) . 'OR pim >  ' . ($floatMaxPressure * (1 + $floatInductedPressure)) . ');');
    }

    public function retrieveLeakageValues($strSerialNumber = null, $strDate = null) {
        return $this->retrieveDbValue('SELECT measurement_num, aev FROM mpd_measurement_data WHERE cyl_no = 0  AND  MarPrime_SerialNo = :mp_number AND date = :date AND aev > 0.4', $strSerialNumber, $strDate);
    }

    /**
     * Errechnet die Werte für die Leakage-Wahrscheinlichkeit
     * 
     * return array
     */
    public function calculateLeakageData($strSerialNumber = null, $strDate = null) {
        
        // errechne den Wert wie in marprime_barcharts_view Z205 ff.
        return $this->retrieveDbValue('SELECT CASE WHEN aev*100 < 5 THEN 5.0 ELSE aev*100 END AS value, measurement_num as cyl_no FROM mpd_measurement_data WHERE MarPrime_SerialNo = :mp_number AND date = :date AND cyl_no = 0', $strSerialNumber, $strDate);
    }

    public function retrieveDbValue($strSQL, $strSerialNumber = null, $strDate = null)
    {
        extract($this->findByCheckCacheParameter([
            'strSerialNumber' => $strSerialNumber,
            'strDate' => $strDate,
        ]));

        $objMpConn = $this->getEntityManager()
            ->getConnection();

        $objStatement = $objMpConn->prepare($strSQL);
        $objStatement->execute([
            ':mp_number' => $strSerialNumber,
            ':date' => $strDate,
        ]);
        return $objStatement; //->fetchColumn(0);
    }

    /**
     * speichere funktionsparameter zwischen, um sie nicht immer übergeben zu müssen
     */
    protected function findByCheckCacheParameter($arrParameter)
    {
        foreach ($arrParameter as $strKey => $mixedValue) {
            if ($mixedValue === null) {
                if (!Arr::get($this->arrParameterCache, $strKey)) {
                    throw new MscException("Parameter nicht gesetzt $strKey");
                }
                $arrParameter[$strKey] = Arr::get($this->arrParameterCache, $strKey);
            } else {
                $this->arrParameterCache[$strKey] = $mixedValue;
            }
        }
        return $arrParameter;
    }
}
