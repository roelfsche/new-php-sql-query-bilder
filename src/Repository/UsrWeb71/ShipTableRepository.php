<?php

namespace App\Repository\UsrWeb71;

use App\Entity\UsrWeb71\ShipTable;
use App\Exception\MscException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ShipTable|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShipTable|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShipTable[]    findAll()
 * @method ShipTable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShipTableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
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
