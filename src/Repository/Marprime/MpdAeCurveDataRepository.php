<?php

namespace App\Repository\Marprime;

use App\Entity\Marprime\MpdAeCurveData;
use App\Exception\MscException;
use App\Kohana\Arr;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MpdAeCurveData|null find($id, $lockMode = null, $lockVersion = null)
 * @method MpdAeCurveData|null findOneBy(array $criteria, array $orderBy = null)
 * @method MpdAeCurveData[]    findAll()
 * @method MpdAeCurveData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MpdAeCurveDataRepository extends ServiceEntityRepository
{
    private $arrParameterCache = [];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MpdAeCurveData::class);
    }

    public function retrieveRevolutionAvg($strSerialNumber = null, $strDate = null)
    {
        $strSQL = 'SELECT DISTINCT cyl_no FROM mpd_ae_curve_data WHERE cyl_no != 0 AND y_val > 0  AND  MarPrime_SerialNo = :mp_number AND date = :date;';
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
        return $objStatement; 
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
