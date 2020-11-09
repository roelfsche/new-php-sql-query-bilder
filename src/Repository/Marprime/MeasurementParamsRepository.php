<?php

namespace App\Repository\Marprime;

use App\Entity\Marprime\MeasurementParams;
use App\Exception\MscException;
use App\Kohana\Arr;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @method MeasurementParams|null find($id, $lockMode = null, $lockVersion = null)
 * @method MeasurementParams|null findOneBy(array $criteria, array $orderBy = null)
 * @method MeasurementParams[]    findAll()
 * @method MeasurementParams[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MeasurementParamsRepository extends ServiceEntityRepository
{
    private $objParameterBag = null;
    private $arrParameterCache = [];

    public function __construct(ManagerRegistry $registry, ParameterBagInterface $parameterBag)
    {
        parent::__construct($registry, MeasurementParams::class);
        $this->objParameterBag = $parameterBag;
    }

    /**
     * Liefert die maximal mögliche Abweichung des Kompressionsdrucks vom Mittelwertdruck zurück
     *
     * @param string  $strMarpimeNumber
     * @param integer $intTs
     * @return float|FALSE
     */
    public function retrievePressureLimit($strSerialNumber = null, $strDate = null)
    {
        // $strCacheKey = md5(__METHOD__ . $strSerialNumber . $strDate);
        // if ($this->objParameterBag->has($strCacheKey)) {
        //     return $this->objParameterBag->get($strCacheKey);
        // }
        $strSQL = 'SELECT limit_pcomp FROM measurement_params WHERE MarPrime_SerialNo = :mp_number AND date = :date limit 1;';

        $objStatement = $this->retrieveDbValue($strSQL, $strSerialNumber, $strDate);
        if (!$objStatement->RowCount()) {
            return false;
        }
        $floatLimit = (float) $objStatement->fetchColumn(0);

        // $this->objParameterBag->set($strCacheKey, $floatLimit);
        return $floatLimit;
    }

    public function retrieveLimitPressure($strSerialNumber = null, $strDate = null, $intStrokes)
    {
        // limit der Abweichung (0,05 -> 5%)
        $strSQL = 'SELECT limit_power * 0.5 * ' . $intStrokes . ' AS limit_pressure FROM measurement_params WHERE MarPrime_SerialNo = :mp_number AND date = :date LIMIT 1;';
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
