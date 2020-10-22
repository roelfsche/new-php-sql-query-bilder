<?php

namespace App\Repository\Marprime;

use App\Entity\Marprime\EngineParams;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EngineParams|null find($id, $lockMode = null, $lockVersion = null)
 * @method EngineParams|null findOneBy(array $criteria, array $orderBy = null)
 * @method EngineParams[]    findAll()
 * @method EngineParams[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EngineParamsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EngineParams::class);
    }

    // /**
    //  * @return EngineParams[] Returns an array of EngineParams objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EngineParams
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
