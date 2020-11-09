<?php

namespace App\Repository\Marprime;

use App\Entity\Marprime\EngineParams;
use App\Exception\MscException;
use App\Kohana\Arr;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EngineParams|null find($id, $lockMode = null, $lockVersion = null)
 * @method EngineParams|null findOneBy(array $criteria, array $orderBy = null)
 * @method EngineParams[]    findAll()
 * @method EngineParams[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EngineParamsRepository extends ServiceEntityRepository
{
    private $arrParameterCache = [];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EngineParams::class);
    }

    /**
     * Diese Methode liefert eine Collection für alle Maschinen-Typen des Schiffs als stdObject's.
     * Hydriere erstmal default-mässig nach array, da es wohl einen Bug in Doctrine gibt:
     * https://github.com/doctrine/orm/issues/8323
     *
     *
     * @param $strMarprimeNumber
     * @return array - Result mit array's
     */
    public function findByMarprimeNumber($strMarprimeNumber, $strHydrationMode = AbstractQuery::HYDRATE_OBJECT)
    {
        return $this->createQueryBuilder('a')
            ->select('a')
            ->andWhere('a.marprimeSerialno = :mp_number')
            ->setParameter(':mp_number', $strMarprimeNumber)
            ->groupBy('a.engineName')
            ->getQuery()
            ->getResult($strHydrationMode);
    }

    /**
     * Diese Methode liefert den max. Messzeitpunkt, der grösser als $intLowerTs ist, zurück.
     *
     * Wenn $intLowerTs nicht übergeben wird, dann den letzten TS.
     * Hintergrund ist, dass bei der autom. Generierung immer als ts {Tag 00:00:00} übergeben wird und ich die genaue Uhrzeit benötige.
     *
     * @param string    $strMarprimeNumber
     * @param stdObject $objEngineParams - Zeile aus der engine_params Tabelle {@see Model_Engine_Params::getByMarprimeNumber}- wenn leer, dann wird nicht nach engine_name selektiert
     * @param int       $intLowerTs      - unix_ts - wenn NULL, dann time()
     * @return bool|int
     */
    public function getLastMeasurementTs($strMarprimeNumber, $objEngineParams = null, $intLowerTs = null)
    {
        $strDate = null;
        $strSql = 'SELECT MAX(date) AS date from engine_params WHERE MarPrime_SerialNo = :mp_number';
        $arrParams = [':mp_number' => $strMarprimeNumber];
        if ($intLowerTs) {
            $strSql .= ' AND create_ts >= FROM_UNIXTIME(:min_ts) AND create_ts < FROM_UNIXTIME(:max_ts)';
            $arrParams[':min_ts'] = $intLowerTs;
            $arrParams[':max_ts'] = $intLowerTs + 86400;
        }
        if ($objEngineParams) {
            $strSql .= ' AND engine_name = :name';
            $arrParams[':name'] = $objEngineParams->engineName;
        }

        $objMpConn = $this->getEntityManager()
            ->getConnection();

        $objStmt = $objMpConn->prepare($strSql); //getConnection('marprime')->prepare($strSql);
        $objStmt->execute($arrParams);
        $arrResult = $objStmt->fetchAll();
        if (is_array($arrResult) && count($arrResult)) {
            $strDate = $arrResult[0]['date'];
        }
        return ($strDate) ? strtotime($strDate) : false;
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
