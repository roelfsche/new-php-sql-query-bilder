<?php

namespace App\Repository\UsrWeb71;

use App\Entity\Marprime\EngineParams;
use App\Entity\UsrWeb71\GeneratedReports;
use App\Entity\UsrWeb71\ShipTable;
use App\Kohana\Arr;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;
use NilPortugues\Sql\QueryBuilder\Builder\GenericBuilder;

/**
 * @method GeneratedReports|null find($id, $lockMode = null, $lockVersion = null)
 * @method GeneratedReports|null findOneBy(array $criteria, array $orderBy = null)
 * @method GeneratedReports[]    findAll()
 * @method GeneratedReports[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GeneratedReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GeneratedReports::class);
    }

    /**
     * liefert einen Report zu einer Fleet zurück
     *
     * @param string $strFleetHash
     * @param string $strPeriod
     * @return GeneratedReport|null
     */
    public function findFleetReport($strFleetHash, $strType, $strPeriod)
    {
        $objBuilder = new GenericBuilder();
        $objQuery = $objBuilder->select('generated_reports', ['*'])
            ->where()
            ->equals('type', $strType)
            ->equals('period', $strPeriod)
            ->equals('fleet_hash', $strFleetHash)
            ->end()
            ->limit(1);

        $strSQL = $objBuilder->write($objQuery);
        $arrParams = $objBuilder->getValues();
        $objEntityManager = $this->getEntityManager();
        $objResultSetMappingBuilder = new ResultSetMappingBuilder($objEntityManager);
        $objResultSetMappingBuilder->addRootEntityFromClassMetadata(GeneratedReports::class, 'generated_reports');
        $objQuery = $objEntityManager->createNativeQuery($strSQL, $objResultSetMappingBuilder);
        $objQuery->setParameters($arrParams);
        $arrReports = $objQuery->getResult();
        return Arr::get($arrReports, 1);
    }

    public function findByShip(ShipTable $objShip, string $type, string $period = null, $intFromTs = 0, $intToTs = 0)
    {
        $strSql = 'SELECT * FROM generated_reports WHERE type= :type AND ship_id = :ship_id';
        $arrParams = ['type' => $type, 'ship_id' => $objShip->getId()];
        if ($period) {
            $strSql .= ' AND period = :period';
            $arrParams['period'] = $period;
        }
        if ($intFromTs && $intToTs) {
            $strSql .= ' AND from_ts = :from_ts AND to_ts = :to_ts';
            $arrParams['from_ts'] = $intFromTs;
            $arrParams['to_ts'] = $intToTs;
        }
        $strSql .= ' LIMIT 1;';

        $objConn = $this->getEntityManager()
            ->getConnection();
        $objStmt = $objConn->prepare($strSql);
        $objStmt->execute($arrParams);
        if (!$objStmt->RowCount()) {
            return null;
        }
        $arrResult = $objStmt->fetchAll(Query::HYDRATE_SIMPLEOBJECT);
        return $arrResult[0];
    }
    /**
     * @return GeneratedReports[] Returns an array von Std-Objekten
     */
    public function findByShipAndEngineParams(ShipTable $objShip, EngineParams $objEngineParams, $intFromCreateTs)
    {
        $strSql = 'SELECT * FROM generated_reports WHERE type = :type AND ship_id = :ship_id AND period = :period LIMIT 1';
        $objConn = $this->getEntityManager()
            ->getConnection();

        $objStmt = $objConn->prepare($strSql); //getConnection('marprime')->prepare($strSql);
        $objStmt->execute([
            'type' => 'engine-' . $objEngineParams->getEngineName() . '_' . $objEngineParams->getEngineType(),
            'ship_id' => $objShip->getId(),
            'period' => date('Y-m-d', $intFromCreateTs),
        ]);
        if (!$objStmt->RowCount()) {
            return null;
        }
        $arrResult = $objStmt->fetchAll(Query::HYDRATE_SIMPLEOBJECT);
        return $arrResult[0];
    }

    /*
public function findOneBySomeField($value): ?GeneratedReports
{
return $this->createQueryBuilder('g')
->andWhere('g.exampleField = :val')
->setParameter('val', $value)
->getQuery()
->getOneOrNullResult()
;
}
 */
}
