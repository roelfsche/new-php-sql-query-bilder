<?php

namespace App\Repository\UsrWeb71;

use App\Entity\UsrWeb71\Reederei;
use App\Entity\UsrWeb71\ShipTable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Reederei|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reederei|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reederei[]    findAll()
 * @method Reederei[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReedereiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reederei::class);
    }

    public function findOneByShip(ShipTable $objShip): ?Reederei
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.name = :val')
            ->setParameter('val', $objShip->getReederei())
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    // /**
    //  * @return Reederei[] Returns an array of Reederei objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Reederei
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
