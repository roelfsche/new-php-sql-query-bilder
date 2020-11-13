<?php

namespace App\Repository\Marprime;

use App\Entity\Marprime\MpdPressureCurveData;
use App\Exception\MscException;
use App\Kohana\Arr;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MpdPressureCurveData|null find($id, $lockMode = null, $lockVersion = null)
 * @method MpdPressureCurveData|null findOneBy(array $criteria, array $orderBy = null)
 * @method MpdPressureCurveData[]    findAll()
 * @method MpdPressureCurveData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MpdPressureCurveDataRepository extends ServiceEntityRepository
{
    private $arrParameterCache = [];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MpdPressureCurveData::class);
    }

    /**
     * Errechnet die Werte für die Leakage-Wahrscheinlichkeit
     *
     * return array
     */
    public function retrieveMaxPressureValues($strSerialNumber = null, $strDate = null)
    {

        // errechne den Wert wie in marprime_barcharts_view Z205 ff.
        return (int) $this->retrieveDbValue('SELECT MAX(y_val / 100) FROM mpd_pressure_curve_data WHERE MarPrime_SerialNo = :mp_number AND date = :date LIMIT 1', $strSerialNumber, $strDate)->fetchColumn(0);

    }

    public function retrievePressureCurveData($strSerialNumber = null, $strDate = null, $intCylNo = 1)
    {
        // habe für 2 Takter X-Bereiche von 1-720 gefunden
        // für 4 Takter 1 - 1440
        // mappe das auf: -180 - 180 für 2 Takter
        //                -180 - 540 für 4 Takter
        $strSQL = 'SELECT ROUND(x_val / 2 - 180) AS x_val, (y_val / 100) AS y_val, cyl_no FROM mpd_pressure_curve_data WHERE MarPrime_SerialNo = :mp_number AND date = :date AND cyl_no = ' . $intCylNo;
        return $this->retrieveDbValue($strSQL, $strSerialNumber, $strDate);
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
