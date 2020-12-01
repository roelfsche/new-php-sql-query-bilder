<?php
namespace App\Service\Maridis\Model;

use App\Entity\UsrWeb71\PermissionsShips;
use App\Entity\UsrWeb71\ShipTable;
use App\Entity\UsrWeb71\Users;
use App\Kohana\Arr;
use App\Service\Model\User as ServiceUser;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use NilPortugues\Sql\QueryBuilder\Builder\GenericBuilder;
use NilPortugues\Sql\QueryBuilder\Manipulation\Select;

/**
 * User service; übernimmt sachen, die früher das User-Jelly-Objekt erledigt hat
 */
class User extends ServiceUser
{

    /**
     * doctrine
     *
     * @var Doctrine\Common\Persistence\ManagerRegistry
     */
    private $objDoctrineManagerRegistry;

    public function __construct(ManagerRegistry $objDoctrineManagerRegistry)
    {
        parent::__construct($objDoctrineManagerRegistry->getManager()->getRepository(Users::class));
        $this->objDoctrineManagerRegistry = $objDoctrineManagerRegistry;
    }

    /**
     * Diese Methode holt alle Schiffe, für die der User das Recht zum Privileg hat
     *
     * @param Pagination    $objPagination     - Pagination-Objekt
     * @param boolean       $boolJust          Count - ...
     * @param string        $strSearch         - wird im Namen nach gesucht
     * @param string        $strPrivilege      - 'list', 'details', ...
     * @param boolean       $boolOnlyFavorites - wenn TRUE, dann nur die Favoriten
     * @param NilPortugues\Sql\QueryBuilder\Manipulation\Select $objQuery          - wenn gesetzt, wird das als Ausgangsquery genommen
     *                                              - alias 'ship_table'
     * @param boolean $boolReturnObjects - wenn true, dann return ShipTable[], sonst array von arrays
     *
     * @return ShipTable[]
     */
    public function getAllowedShips($objPagination = null, $boolJustCount = false, $strSearch = null, $strPrivilege = 'list', $boolOnlyFavorites = false, Select $objQuery = null, $boolReturnObjects = true)
    {

        $objBuilder = new GenericBuilder();
        // $objBuilder = new MySqlBuilder();
        // die schiffsquery
        if (!$objQuery) {

            $objQuery = $objBuilder->select('ship_table', ['*']);
            // $objQuery = $this->objUserRepository
            //     ->getEntityManager()
            //     ->getRepository(ShipTable::class)
            //     ->createQueryBuilder('s');

        }

        if ($boolOnlyFavorites) {
            // $objQuery->join('s.ship_favorites', 'sf')
            //     ->andWhere('sf.userId = :user_id')
            //     ->setParameter('user_id', $this->objUser->getId());
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
                // $objQuery->addSelect('fc.id AS is_favorite');
                // Spalte aus der 2. Tabelle
                // siehe: http://dba.stackexchange.com/questions/56840/can-i-provide-a-default-for-a-left-outer-join
                // wenn ich die id selektiere, dann zieht ein evt. 2. sort-kriterium nicht mehr bei den favorites
                // weil immer nach is_favorite zuerst selektiert wird und das ist aufsteigend
                // also ersetze ich die Id zur einen default-wert
                // $arrFields[] = array(
                // 'ship_favorites.id',
                // 'is_favorite'
                // );
            } else {
                // Spalte aus der 2. Tabelle
                // siehe: http://dba.stackexchange.com/questions/56840/can-i-provide-a-default-for-a-left-outer-join
                // wenn ich die id selektiere, dann zieht ein evt. 2. sort-kriterium nicht mehr bei den favorites
                // weil immer nach is_favorite zuerst selektiert wird und das ist aufsteigend
                // also ersetze ich die Id zur einen default-wert
                // $arrFields[] = array(
                // DB::expr('CASE WHEN `ship_favorites`.`id` IS NOT NULL THEN 1 ELSE 0 END'),
                // 'is_favorite'
                // );
                // $objQuery->addSelect('sf.id');
                // $objQuery->leftjoin('s.ship_favorites', 'sf')//, Join::ON, 'sf.userId = :user_id')
                //  ->andWhere('sf.userId = :user_id OR sf.userId IS NULL')
                // ->setParameter('user_id', $this->objUser->getId());
                $objQuery->leftJoin('ship_favorites', 'id', 'ship_id') //, ['is_favorite' => 'CASE WHEN `ship_favorites`.`id` IS NOT NULL THEN 1 ELSE 0 END'])
                    ->on()
                    ->equals('user_id', $this->objUser->getId());
                $objQuery->setFunctionAsColumn('CASE WHEN `ship_favorites`.`id` IS NOT NULL THEN 1 ELSE 0 END', [], 'is_favorite');
                // $objQuery->join('ship_favorites', 'LEFT')
                //     ->on('ship_table.id', '=', 'ship_favorites.ship_id')
                //     ->on('ship_favorites.user_id', '=', DB::expr($this->id));
            }
            // $objQuery->select_array($arrFields);
        }

        $arrShipIds = $this->getAllowedShipIds();

        // wenn keine schiffe erlaubt...
        if (!count($arrShipIds)) {
            $objQuery->where()
                ->equals('id', 1);
            // $objQuery->where('id', '=', -1);
        } elseif (!in_array('*', $arrShipIds)) {
            // Einschränkung auf die erlaubten
            $objQuery->where()
                ->in('id', $arrShipIds);
            // $objQuery->andWhere('id IN (:ids)')
            //     ->setParameter('ids', implode($arrShipIds));
            // $objQuery->where('id', 'IN', DB::expr('(' . implode(', ', $arrShipIds) . ')'));
        }

        // if ($objPagination) {
        //     $objQuery->limit($objPagination->items_per_page)
        //         ->offset($objPagination->offset)
        //         ->order_by('is_favorite', 'DESC');

        //     if ($objPagination->sort && $objPagination->direction) {
        //         $objQuery->order_by($objPagination->sort, $objPagination->direction);
        //     }
        // }

        if ($strSearch) {
            // $objQuery->where_open()
            //     ->or_where('baptism_name', 'LIKE', '%' . $strSearch . '%')
            //     ->or_where('actual_name', 'LIKE', '%' . $strSearch . '%')
            //     ->or_where('vessel_name', 'LIKE', '%' . $strSearch . '%')
            //     ->or_where('zusatz', 'LIKE', '%' . $strSearch . '%')
            //     ->where_close();
        }
        // $objResult = $objQuery->execute();
        // if ($boolJustCount) {
        //     return $objResult->offsetGet(0)
        //         ->get('anzahl');
        // } else {
        // return $objResult;
        // }
        // $objDebugQuery = $objQuery->getQuery();
        // var_dump($objDebugQuery->getDQL());
        // echo $objBuilder->writeFormatted($objQuery);
        // var_dump($objBuilder->getValues());
        // var_dump($objBuilder->writeValues()); exit();
        // $objEntityManager = $this->objDoctrineManagerRegistry->getEntityManager();
        $strSQL = $objBuilder->write($objQuery);
        $arrParams = $objBuilder->getValues();
        $objEntityManager = $this->objUserRepository->getEntityManager();

        if ($boolReturnObjects) {
            $objResultSetMappingBuilder = new ResultSetMappingBuilder($objEntityManager);
            $objResultSetMappingBuilder->addRootEntityFromClassMetadata(ShipTable::class, 'ship_table');
            $objQuery = $objEntityManager->createNativeQuery($strSQL, $objResultSetMappingBuilder);
            $objQuery->setParameters($arrParams);
            $arrShips = $objQuery->getResult();
        } else {
            $objStatement = $objEntityManager->getConnection()
                ->prepare($strSQL);
            $objStatement->execute($arrParams);
            $arrShips = $objStatement->fetchAll();
        }

        return $arrShips;

    }

    /**
     * Diese Methode extrahiert alle erlaubten ship_id's aus den Permissions.
     * Wenn '*' gefunden wird, so wird nur array('*') zurück gegeben
     * Sonst ind. Array mit den ship_id's.
     *
     * @param string $strPrivilege
     * @return array
     *
     */
    public function getAllowedShipIds($strPrivilege = 'list')
    {
        $arrPermissions = $this->getPermissions();

        $arrShipIds = array();

        foreach (['*', 'ship'] as $strNeededResource) {
            foreach (['*', $strPrivilege] as $strNeededPrivilege) {
                // hole jetzt die werte
                $strPath = "$strNeededResource.$strNeededPrivilege";
                $arrAllowedShipIds = array_keys(Arr::path($arrPermissions, $strPath, []));
                // wenn alle...
                if (in_array('*', $arrAllowedShipIds)) {
                    // ... dann raus
                    return array('*');
                }

                $arrShipIds = array_unique(Arr::merge($arrShipIds, $arrAllowedShipIds));
            }
        }

        return $arrShipIds;
    }

    /**
     * Diese Methode compiliert alle Rechte in ein Array, welches zurück gegeben wird.
     * Folgende Struktur wird aufgebaut:
     *' = TRUE)
     * Bsp. 'document', array('edit', 'read'), array('oe1', 'oe3') produziert
     * array(
     * 'document' => array(
     *'),
     *')
     * ),
     * 'read' => array(
     *'),
     *')
     * )
     * )
     * )
     *'), array('*'), array('*') mit übergeben worden, wär das compilierte Ergebnis:
     * array(
     * 'document' => array(
     *' => array(
     *' => array('*')
     * )
     * )
     * )
     * geworden, weil die zweite Regel allgemeiner ist und somit die erste ersetzt.
     * @param ArrayIterator $mixedIterator (Jelly_Collection, array->Unittest)
     * @return array
     */
    protected function compilePermissions($mixedIterator)
    {
        $objPermissionsShipsRepository = $this->objUserRepository
            ->getEntityManager()
            ->getRepository(PermissionsShips::class);
        $arrCompiledPermissions = array();
        foreach ($mixedIterator as $objPermissions) {
            foreach (array_unique(array(
                '*',
                $objPermissions->getResource(),
            )) as $strResource) {
                foreach (array_unique(Arr::merge(array(
                    '*',
                ), $objPermissions->getPrivilege())) as $strPrivilege) {
                    $arrShipIds = $objPermissionsShipsRepository->findShipIdsByPermission($objPermissions);
                    // $arrShipIds = $objPermissions->getShipIds();
                    if (in_array('*', $arrShipIds)) {
                        $arrShipIds = array('*');
                    }

                    foreach (array_unique(Arr::merge(array(
                        '*',
                    ), $arrShipIds)) as $intShipId) {
                        $strPath = "$strResource.$strPrivilege.$intShipId";
                        if (Arr::path($arrCompiledPermissions, $strPath)) {
                            // raus, weil schon höheres Recht gefunden
                            break 3;
//                        break 4;
                        }
                        // sonst check, ob es genau dieses Recht ist, dass eingebaut werden soll
                        elseif ($strResource == $objPermissions->getResource() && in_array($strPrivilege, $objPermissions->getPrivilege()) && (!count($arrShipIds) || in_array($intShipId, $arrShipIds))) {
                            // schaue erst noch, ob ich ein unterarray löschen kann, weil dieses evtl.
                            // am ende ein paar sterne hat:
                            // document.*.*.* löscht bspw. document.edit.*.* und document.read.*.*
                            // nehme zuerst alle *.*.* hinten weg
                            $strRmPath = rtrim($strPath, '.*');
                            if ($strRmPath != $strPath) {
                                if (!strlen($strRmPath)) {
                                    // check, ob ich von ganz oben an löschen muss
                                    $arrCompiledPermissions = [];
                                } else {
                                    // sonst nur das Unterarray
                                    Arr::set_path($arrCompiledPermissions, $strRmPath, null);
                                }
                            }

                            // ok, Recht wird mit TRUE in das Array eingefügt
                            Arr::set_path($arrCompiledPermissions, "$strResource.$strPrivilege.$intShipId", true);
                        }
                    }
                }
            }
        }
        return $arrCompiledPermissions;
    }

}
