<?php

namespace App\Repository\UsrWeb71;

use App\Entity\Marnoon\Voyagereport;
use App\Entity\Marprime\EngineParams as EngineParams;
use App\Entity\UsrWeb71\ShipTable;
use App\Exception\MscException;
use App\Kohana\Arr;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection as DBALConnection;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ShipTable|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShipTable|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShipTable[]    findAll()
 * @method ShipTable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShipTableRepository extends ServiceEntityRepository
{
    /**
     * @param Doctrine\Common\Persistence\ManagerRegistry
     */
    protected $objManagerRegistry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->objManagerRegistry = $registry;
        parent::__construct($registry, ShipTable::class);
    }

    public function findByMarprimeSerialNo($strMarprimeSerialNo)
    {
        return $this->createQueryBuilder('s')
        // ->andWhere('s.MarPrime_SerialNo = ?1')
        // ->andWhere('s.MarPrime_SerialNo = :val')
            ->andWhere('s.marprimeSerialno = :val')
            ->setParameter(':val', $strMarprimeSerialNo)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $strImoNumber
     * @return ShipTable
     * @throws MscException
     */
    public function findByImoNumber($strImoNumber)
    {
        if (!strlen($strImoNumber)) {
            throw new MscException('Keine IMO-Nummer übergeben');
        }

        return $this->createQueryBuilder('s')
            ->andWhere('s.imoNo = :val')
            ->setParameter(':val', $strImoNumber)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

    }

    /**
     * @param string $strCdsSerialNumber
     * @return ShipTable
     * @throws MscException
     */
    public function findByCdsSerialNumber($strCdsSerialNumber)
    {
        if (!strlen($strCdsSerialNumber)) {
            throw new MscException('Keine CDS-Serien-Nummer übergeben');
        }

        return $this->createQueryBuilder('s')
            ->andWhere('s.cdsSerialno = :val')
            ->setParameter(':val', $strCdsSerialNumber)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

    }

    public function findAllForVoyageReport($intFromTs)
    {
        $objMnConn = $this->objManagerRegistry->getManager('marnoon')
            ->getRepository(Voyagereport::class)
            ->getEntityManager()
            ->getConnection();
        $strSql = 'SELECT DISTINCT(IMO) AS IMO FROM voyagereport WHERE IMO > 0 AND FROM_UNIXTIME(date) >= ' . $intFromTs;
        $objStmt = $objMnConn->prepare($strSql);
        $objStmt->execute([]);
        $arrResult = $objStmt->fetchAll();
        if (!$arrResult || !count($arrResult)) {
            return [];
        }
        $arrImoNumbers = array_map('current', $arrResult); // Arr::pluck('IMO', $arrResult);
        // nun die Schiffe aus der anderen DB dazu
        $objQuery = $this->createQueryBuilder('a')
            ->andWhere('a.imoNo IN (:string)')
            ->setParameter('string', $arrImoNumbers, DBALConnection::PARAM_STR_ARRAY); //->andWhere('a.marprimeSerialno IN (:ids)')
        return $objQuery->getQuery()
            ->getResult();
    }

    /**
     * @return array von Objekten
     */
    public function findByDateOrShippingCompany($intFromTs, $strShippingCompany = '')
    {
        $strSql = 'SELECT DISTINCT(MarPrime_SerialNo) AS marprime_serial_number from engine_params WHERE create_ts >= FROM_UNIXTIME(:from_ts) AND create_ts < FROM_UNIXTIME(:to_ts);';

        $objMpConn = $this->objManagerRegistry->getManager('marprime')
            ->getRepository(EngineParams::class)
            ->getEntityManager()
            ->getConnection();

        $objStmt = $objMpConn->prepare($strSql); //getConnection('marprime')->prepare($strSql);
        $objStmt->execute([
            ':from_ts' => $intFromTs,
            ':to_ts' => $intFromTs + 86400,
        ]);
        $arrResult = $objStmt->fetchAll();
        if (!$arrResult || !count($arrResult)) {
            return [];
        }
        // array(array('column' => val1), array('column' => val2)) => array(val1, val2)
        // https://stackoverflow.com/a/13969241
        $arrMpSerialNumbers = array_map('current', $arrResult);

        // nun die Schiffe aus der anderen DB dazu
        $objQuery = $this->createQueryBuilder('a')
            ->andWhere('a.marprimeSerialno IN (:string)')
            ->setParameter('string', $arrMpSerialNumbers, DBALConnection::PARAM_STR_ARRAY); //->andWhere('a.marprimeSerialno IN (:ids)')

        if (strlen($strShippingCompany)) {
            $objQuery->andWhere('a.reederei = :reederei')
                ->setParameter('reederei', $strShippingCompany);
        }

        return $objQuery->getQuery()
            ->getResult();
    }

    /**
     * führt eine native SQL aus und liefert das Ergebnis als Array von Arrays oder ShipTable zurück
     *
     * @param string $strSql - SQL
     * @param array $arrParams - [':name' => value, ...]
     * @param boolean $boolAsObject - wenn true, dann ShipTable[], sonst [[], [], ...]
     * @param string $strTableAlias - braucht Doctrine, um die Spalten zu finden: Select a.*  --> a
     * @return ShipTable[] | []
     */
    public function findByNativeSql($strSql, $arrParams, $boolAsObject = true, $strTableAlias = 'ship_table')
    {
        $objEntityManager = $this->getEntityManager();
        if ($boolAsObject) {
            $objResultSetMappingBuilder = new ResultSetMappingBuilder($objEntityManager);
            $objResultSetMappingBuilder->addRootEntityFromClassMetadata(ShipTable::class, $strTableAlias);
            $objQuery = $objEntityManager->createNativeQuery($strSql, $objResultSetMappingBuilder);
            $objQuery->setParameters($arrParams);
            $arrShips = $objQuery->getResult();
        } else {
            $objStatement = $objEntityManager
                ->getConnection()
                ->prepare($strSql);
            $objStatement->execute($arrParams);
            $arrShips = $objStatement->fetchAll();
        }
        return $arrShips;
    }
    // /**
    //  * @return ShipTable[] Returns an array of ShipTable objects
    //  */
    /*
    public function findByExampleField($value)
    {
    return $this->createQueryBuilder('s')
    ->andWhere('s.exampleField = :val')
    ->setParameter('val', $value)
    ->orderBy('s.id', 'ASC')
    ->setMaxResults(10)
    ->getQuery()
    ->getResult()
    ;
    }
     */

    /*
public function findOneBySomeField($value): ?ShipTable
{
return $this->createQueryBuilder('s')
->andWhere('s.exampleField = :val')
->setParameter('val', $value)
->getQuery()
->getOneOrNullResult()
;
}
 */
}
