<?php

namespace App\Repository\UsrWeb71;

use App\Entity\UsrWeb71\Permissions;
use App\Entity\UsrWeb71\PermissionsShips;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PermissionsShips|null find($id, $lockMode = null, $lockVersion = null)
 * @method PermissionsShips|null findOneBy(array $criteria, array $orderBy = null)
 * @method PermissionsShips[]    findAll()
 * @method PermissionsShips[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PermissionsShipsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PermissionsShips::class);
    }

    /**
     * liefert ein Array von PermissionsShips-Objekt
     *
     * @param Permissions $objPermission
     * @return void
     */
    public function findByPermission(Permissions $objPermission)
    {
        return $this->createQueryBuilder('ps')
            ->andWhere('ps.permissionId = :id')
            ->setParameter('id', $$objPermission->getId())
            ->orderBy('ps.shipId', 'ASC')
            ->getQuery()
            ->getResult()
        ;

    }

    /**
     * liefert ein Array mit ship-id's zurück
     *
     * @param Permissions $objPermission
     * @return []
     */
    public function findShipIdsByPermission(Permissions $objPermission) {
        $strSQL = 'SELECT ship_id FROM permissions_ships WHERE permission_id = :id ORDER BY ship_id ASC;';
        $objStatement = $this->getEntityManager()
        ->getConnection()
        ->prepare($strSQL);
        $objStatement->execute(['id' => $objPermission->getId()]);
        $arrResult = $objStatement->fetchAll();
        return array_map('current', $arrResult);
    }
    // /**
    //  * @return PermissionsShips[] Returns an array of PermissionsShips objects
    //  */
    /*
    public function findByExampleField($value)
    {
    return $this->createQueryBuilder('p')
    ->andWhere('p.exampleField = :val')
    ->setParameter('val', $value)
    ->orderBy('p.id', 'ASC')
    ->setMaxResults(10)
    ->getQuery()
    ->getResult()
    ;
    }
     */

    /*
public function findOneBySomeField($value): ?PermissionsShips
{
return $this->createQueryBuilder('p')
->andWhere('p.exampleField = :val')
->setParameter('val', $value)
->getQuery()
->getOneOrNullResult()
;
}
 */
}
