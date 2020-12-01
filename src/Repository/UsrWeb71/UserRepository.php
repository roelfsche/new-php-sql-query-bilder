<?php

namespace App\Repository\UsrWeb71;

use App\Entity\UsrWeb71\Permissions;
use App\Entity\UsrWeb71\Users;
use App\Service\Maridis\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Users|null find($id, $lockMode = null, $lockVersion = null)
 * @method Users|null findOneBy(array $criteria, array $orderBy = null)
 * @method Users[]    findAll()
 * @method Users[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    // private ManagerRegistry $objRegistry;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Users::class);
    }

    public function findByResourcePrivilege($strResource, $strPrivilege)
    {

        $strSQL = 'SELECT u.* FROM users u JOIN groups_users gu ON (u.user_id = gu.user_user_id)
        JOIN role_targets rt ON (gu.group_id = rt.target_id AND rt.target_type = :target_type)
        JOIN role_permissions rp ON (rt.role_id = rp.role_id)
        JOIN permissions p ON (rp.permission_id = p.id)
        WHERE ((p.resource = :resource) AND (p.privilege LIKE :privilege OR p.privilege LIKE \'%*%\'))
        OR (p.resource = \'*\' AND p.privilege LIKE \'%*%\')
        UNION
        SELECT u.* FROM users u join role_targets rt ON (u.user_id = rt.target_id AND rt.target_type = :target2_type)
        JOIN role_permissions rp ON (rt.role_id = rp.role_id)
        JOIN permissions p on (rp.permission_id = p.id)
        WHERE ((p.resource = :resource) AND (p.resource LIKE :privilege OR p.privilege LIKE \'%*%\'))
        OR (p.resource = \'*\' AND p.privilege LIKE \'%*%\'); ';

        $arrParams = [
            'target_type' => 'group',
            'resource' => $strResource,
            'privilege' => "%$strPrivilege%",
            'target2_type' => 'user',
        ];

        $objEntityManager = $this->getEntityManager();
        $objResultSetMappingBuilder = new ResultSetMappingBuilder($objEntityManager);
        $objResultSetMappingBuilder->addRootEntityFromClassMetadata(Users::class, 'u');
        $objQuery = $objEntityManager->createNativeQuery($strSQL, $objResultSetMappingBuilder);
        $objQuery->setParameters($arrParams);
        $arrUsers = $objQuery->getResult();
        return $arrUsers;
    }

    /**
     * Diese Methode holt alle Schiffe, für die der User das Recht zum Privileg hat
     *
     * @param App\Service\Maridis\User $objUserService
     * @param Pagination    $objPagination     - Pagination-Objekt
     * @param boolean       $boolJust          Count - ...
     * @param string        $strSearch         - wird im Namen nach gesucht
     * @param string        $strPrivilege      - 'list', 'details', ...
     * @param boolean       $boolOnlyFavorites - wenn TRUE, dann nur die Favoriten
     * @param Doctrine\ORM\Query $objQuery          - wenn gesetzt, wird das als Ausgangsquery genommen
     *                                              - alias 's'
     *
     * @return Jelly_Collection von Model_Row_Ship
     */
    public function getAllowedShips(User $objUserService, $objPagination = null, $boolJustCount = false, $strSearch = null, $strPrivilege = 'list', $boolOnlyFavorites = false, Query $objQuery = null)
    {
        $objUser = $objUserService->getUser();
        // die schiffsquery
        if (!$objQuery) {
            $objQuery = $this->createQueryBuilder('s');
        }

        if ($boolOnlyFavorites) {
            $objQuery->join('s.ship_favorites', 'sf')
                ->andWhere('sf.userId = :user_id')
                ->setParameter('user_id', $objUser->getId());
        }

        // $test = $objQuery->getQuery()->getResult();

        if ($boolJustCount) {
            // $objQuery->select_array(array(
            //     array(
            //         DB::expr('count(*)'),
            //         'anzahl'
            //     )
            // ));
        } else {
            // Doctrine selektiert alle Felder erstmal out-of-the-box
            // Hack!!!
            // muss die Spalten einzeln benennen..., da ich aus 2Tabellen joine.
            //Da ich mit Aliasen arbeite, ziehe ich mir hier alle Spalten + Alias
            // aus dem Meta-Object
            // $arrFields = array();
            // foreach (Jelly::factory('Row_Ship')->meta()->fields() as $objField)
            // {
            //     $arrFields[] = array('ship_table.' . $objField->column, $objField->name);
            // }

            if ($boolOnlyFavorites) {
                $objQuery->addSelect('fc.id AS is_favorite');
                // Spalte aus der 2. Tabelle
                // siehe: http://dba.stackexchange.com/questions/56840/can-i-provide-a-default-for-a-left-outer-join
                // wenn ich die id selektiere, dann zieht ein evt. 2. sort-kriterium nicht mehr bei den favorites
                // weil immer nach is_favorite zuerst selektiert wird und das ist aufsteigend
                // also ersetze ich die Id zur einen default-wert
                // $arrFields[] = array(
                //     'ship_favorites.id',
                //     'is_favorite'
                // );
            } else {
                // Spalte aus der 2. Tabelle
                // siehe: http://dba.stackexchange.com/questions/56840/can-i-provide-a-default-for-a-left-outer-join
                // wenn ich die id selektiere, dann zieht ein evt. 2. sort-kriterium nicht mehr bei den favorites
                // weil immer nach is_favorite zuerst selektiert wird und das ist aufsteigend
                // also ersetze ich die Id zur einen default-wert
                // $arrFields[] = array(
                //     DB::expr('CASE WHEN `ship_favorites`.`id` IS NOT NULL THEN 1 ELSE 0 END'),
                //     'is_favorite'
                // );
                $objQuery->addSelect('sf.id');
                $objQuery->leftjoin('s.ship_favorites', 'sf')
                    ->andWhere('sf.userId = :user_id')
                    ->setParameter('user_id', $objUser->getId());

                // $objQuery->join('ship_favorites', 'LEFT')
                //     ->on('ship_table.id', '=', 'ship_favorites.ship_id')
                //     ->on('ship_favorites.user_id', '=', DB::expr($this->id));
            }

            // $objQuery->select_array($arrFields);
        }
// $objQuery->getQuery()->getResult();

        $arrShipIds = $this->getAllowedShipIds();

        // wenn keine schiffe erlaubt...
        if (!count($arrShipIds)) {
            $objQuery->where('id', '=', -1);
        } elseif (!in_array('*', $arrShipIds)) {
            // Einschränkung auf die erlaubten
            $objQuery->where('id', 'IN', DB::expr('(' . implode(', ', $arrShipIds) . ')'));
        }

        if ($objPagination) {
            $objQuery->limit($objPagination->items_per_page)
                ->offset($objPagination->offset)
                ->order_by('is_favorite', 'DESC');

            if ($objPagination->sort && $objPagination->direction) {
                $objQuery->order_by($objPagination->sort, $objPagination->direction);
            }
        }

        if ($strSearch) {
            $objQuery->where_open()
                ->or_where('baptism_name', 'LIKE', '%' . $strSearch . '%')
                ->or_where('actual_name', 'LIKE', '%' . $strSearch . '%')
                ->or_where('vessel_name', 'LIKE', '%' . $strSearch . '%')
                ->or_where('zusatz', 'LIKE', '%' . $strSearch . '%')
                ->where_close();
        }

        $objResult = $objQuery->execute();
        if ($boolJustCount) {
            return $objResult->offsetGet(0)
                ->get('anzahl');
        } else {
            return $objResult;
        }
    }

    public function retrievePermissions(Users $objUser)
    {
        $strSQL = 'SELECT p.* FROM users u JOIN groups_users gu ON (u.user_id = gu.user_user_id)
JOIN role_targets rt ON (gu.group_id = rt.target_id AND rt.target_type = :target_type)
JOIN role_permissions rp ON (rt.role_id = rp.role_id)
JOIN permissions p ON (rp.permission_id = p.id)
WHERE (u.user_id = :user_id)
UNION
SELECT p.* FROM users u JOIN role_targets rt ON (u.user_id = rt.target_id AND rt.target_type = :target2_type)
JOIN role_permissions rp ON (rt.role_id = rp.role_id)
JOIN permissions p ON (rp.permission_id = p.id)
WHERE (u.user_id = :user_id)
';
        $arrParams = [
            'target_type' => 'group',
            'user_id' => $objUser->getId(),
            'target2_type' => 'user',
        ];
        // $objConnection = $this->getEntityManager()
        //     ->getConnection();
        // $objStatement = $objConnection->prepare($strSQL);
        // $objStatement->execute($arrParams);
        // $arrPermissions = $objStatement->fetchAll();
        // return $arrPermissions;


        $objEntityManager = $this->getEntityManager();
        $objResultSetMappingBuilder = new ResultSetMappingBuilder($objEntityManager);
        $objResultSetMappingBuilder->addRootEntityFromClassMetadata(Permissions::class, 'p');
        $objQuery = $objEntityManager->createNativeQuery($strSQL, $objResultSetMappingBuilder);
        $objQuery->setParameters($arrParams);
        $arrPermissions = $objQuery->getResult();
        return $arrPermissions;
    }
    // /**
    //  * @return Users[] Returns an array of Users objects
    //  */
    /*
    public function findByExampleField($value)
    {
    return $this->createQueryBuilder('u')
    ->andWhere('u.exampleField = :val')
    ->setParameter('val', $value)
    ->orderBy('u.id', 'ASC')
    ->setMaxResults(10)
    ->getQuery()
    ->getResult()
    ;
    }
     */

    /*
public function findOneBySomeField($value): ?Users
{
return $this->createQueryBuilder('u')
->andWhere('u.exampleField = :val')
->setParameter('val', $value)
->getQuery()
->getOneOrNullResult()
;
}
 */
/**
 * public...
 *
 * @return void
 */
public function getEntityManager() {
    return parent::getEntityManager();
}
}
