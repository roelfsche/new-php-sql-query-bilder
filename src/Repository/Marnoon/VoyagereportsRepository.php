<?php

namespace App\Repository\Marnoon;

use App\Entity\Marnoon\Voyagereport;
use App\Entity\UsrWeb71\ShipTable;
use App\Entity\UsrWeb71\Users;
use App\Exception\MscException;
use App\Kohana\Arr;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;
use NilPortugues\Sql\QueryBuilder\Builder\GenericBuilder;
use NilPortugues\Sql\QueryBuilder\Manipulation\Select;
use Psr\Container\ContainerInterface;

/**
 * @method Voyagereport|null find($id, $lockMode = null, $lockVersion = null)
 * @method Voyagereport|null findOneBy(array $criteria, array $orderBy = null)
 * @method Voyagereport[]    findAll()
 * @method Voyagereport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VoyagereportsRepository extends ServiceEntityRepository
{
    private $objShip = null;
    private $strFromTs = '';
    private $arrConstraints = null;
    private $objUserRepository = null;

    public function __construct(ManagerRegistry $registry, ContainerInterface $objContainer)
    {
        parent::__construct($registry, Voyagereport::class);
        $this->objContainer = $objContainer;

        $arrConfig = $objContainer->getParameter('reports');
        $this->arrConstraints = Arr::get($arrConfig, 'constraints');

        $o = $objContainer->get('doctrine');
        $this->objUserRepository = $registry

        // $this->objUserRepository = $objContainer->get('doctrine')
            ->getManager('default')
            ->getRepository(Users::class);
    }

    public function init(ShipTable $objShip, $intFromTs, $intToTs)
    {

        $this->objShip = $objShip;
        $this->intFromTs = $intFromTs;
        $this->strFromTs = date('Y-m-d', $intFromTs);
        $this->intToTs = $intToTs;
        $this->strToTs = date('Y-m-d', $intToTs);
    }

    /**
     * Liefert alles für ein Schiff innerhalb des Zeitraums
     *
     * @return Voyagereport[]
     */
    public function retrieveAllForShip()
    {
        // $objQuery = $this->getVoyageReportQuery();
        $objQuery = $this->createQueryBuilder('a')
            ->andWhere('a.imo = :imo')
            ->setParameter('imo', $this->objShip->getImoNo())
            ->andWhere('a.date >= :min_date')
            ->setParameter('min_date', $this->strFromTs)
            ->andWhere('a.date <= :max_date')
            ->setParameter('max_date', $this->strToTs)
            ->OrderBy('a.lastentry');

        return $objQuery->getQuery()->getResult();

    }

    /**
     * Diese Methode liefert alle Schiffe aus der voyage-Report-Tabelle, die dem User zugeordnet sind
     *
     * @param  App\Entity\UsrWeb71\User $objUser
     * @param boolean    $boolOnlyFavorites
     * @param array      $arrFilterImoNumbers - wenn gesetzt, dann werden nur diese Schiffe geliefert (falls sie allen anderen Kriterien entsprechend)
     * @return User[]
     */
    // public function getAllShipsForUser(Users $objUser, $boolOnlyFavorites = false, $arrFilterImoNumbers = array())
    // {
    //     if (!count($arrFilterImoNumbers)) {
    //         $arrShips = $this->objUserRepository->getAllowedShips($objUser);
    //         // $objShipCollection = $objUser->getAllowedShips();
    //         $arrImoNumbers = $objShipCollection->as_array('id', 'imo_number');
    //         // schmeisse '', '4tgw...', 'IMO1234' raus
    //         $arrFilterImoNumbers = array_filter($arrImoNumbers, function ($mixedValue) {
    //             return is_numeric($mixedValue);
    //         });
    //     }

    //     $objQuery = DB::select(array(
    //         DB::expr('DISTINCT(IMO)'),
    //         'IMO',
    //     ))->where('IMO', 'IN', DB::expr(strtr('(:imo_numbers)', array(
    //         ':imo_numbers' => implode(', ', $arrFilterImoNumbers),
    //     )
    //     )));

    //     $objShipQuery = Jelly::query('Row_Ship')->order_by('id');

    //     if ($boolOnlyFavorites) {
    //         $objShipQuery->join('ship_favorites')
    //             ->on('ship_table.id', '=', 'ship_favorites.ship_id')
    //             ->on('ship_favorites.user_id', '=', DB::expr($objUser->user_id));
    //     }

    //     return self::getAllShips($objQuery, $objShipQuery);
    // }

    /**
     * Diese Methode liest einen Wert aus der DB und liefert ihn zurück.
     *
     * Verwende sie intensiv in {@link Model_Report_Fleet_Performance_Row}.
     * Bekommt eine Config für die Query übergeben, und führt die Query aus.
     * Config:
     *
     * array(
     *     'field_expression' => array(
     *         array(DB::expr('SUM(TimeAtSea * MESFOC) / SUM(TimeAtSea)'), 'sfoc')      <-- das wird selektiert als DB::expr(...) as 'sfoc'
     *                                                                                  <-- können mehrere übergeben werden
     *     ),
     *     'constraints' => array(
     *        'MESFOC' => 'me_specific_fuel_oil_consumption',                           <-- Dieses DB-Feld wird gegen die Grenzen in report.constraints.me_specific_fuel_oil_consumption gecheckt
     *        'TimeAtSea' => 'time_at_sea'                                              <-- Dieses DB-Feld wird gegen die Grenzen in report.constraints.time_at_sea gechekct
     *     ),
     *     'defaults' => array('sfoc' => PHP_INT_MAX)                                   <-- Wenn NULL/FALSE/0.0/..., dann den DefaultWert für dieses Feld
     * )
     *
     * @param                    $arrQueryConfig
     * @param Jelly_Builder|NULL $objQuery -zur Übergabe einer eigenen Query
     * @return bool|array
     * @throws Msc_Exception
     * @deprecated
     */
    public function retrieveVoyageValues($arrQueryConfig, $strQuery = null)
    {
        if (!$strQuery) {
            // $objQuery = $this->getVoyageReportQuery();
            $objQuery = $this->createQueryBuilder('a')
                ->andWhere('a.imo = :imo')
                ->setParameter('imo', $this->objShip->getImoNo())
                ->andWhere('a.date >= :min_date')
                ->setParameter('min_date', $this->strFromTs)
                ->andWhere('a.date <= :max_date')
                ->setParameter('max_date', $this->strToTs);
        }

        $arrFields = [];
        foreach ($arrQueryConfig['field_expression'] as $arrField) {
            $arrFields[] = $arrField[0] . ' as ' . $arrField[1];
        }
        $objQuery->select($arrFields);

        $objQuery = $this->addConstraintsToQuery($objQuery, $arrQueryConfig['constraints']);
        $arrResult = $objQuery->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);
        if (!count($arrResult)) {
            return false;
        }

        $arrRow = $arrResult[0];

        // defaults bei Bedarf setzen
        $arrDefaults = Arr::get($arrQueryConfig, 'defaults');
        if ($arrDefaults) {
            throw new MscException("noch nicht getestet!!!!");
            foreach ($arrResult as $strIndex => $mixedValue) {
                if (!(float) $mixedValue && Arr::get($arrDefaults, $strIndex)) {
                    $arrResult[$strIndex] = Arr::get($arrDefaults, $strIndex);
                }
            }
        }
        return $arrRow;
    }

    /**
     * Diese Methode liest einen Wert aus der DB und liefert ihn zurück.
     * Sie verwendet für die Bildung des SQL-STatements https://github.com/nilportugues/php-sql-query-builder
     *
     * Verwende sie intensiv in {@link Fleet_Performance_Row}.
     * Bekommt eine Config für die Query übergeben, und führt die Query aus.
     * Config:
     *
     * array(
     *     'field_expression' => array(
     *          [Feld-Alias => SQL-Spaltenname],
     *            oder
     *          ['func' => 'SUM', 'arguments' => ['spalte1', ...], Feld-Alias],
     *            ...                                                                   <-- können mehrere übergeben werden
     *     ),
     *     'constraints' => array(
     *        'MESFOC' => 'me_specific_fuel_oil_consumption',                           <-- Dieses DB-Feld wird gegen die Grenzen in report.constraints.me_specific_fuel_oil_consumption gecheckt
     *        'TimeAtSea' => 'time_at_sea'                                              <-- Dieses DB-Feld wird gegen die Grenzen in report.constraints.time_at_sea gechekct
     *     ),
     *     'defaults' => array('sfoc' => PHP_INT_MAX)                                   <-- Wenn NULL/FALSE/0.0/..., dann den DefaultWert für dieses Feld
     * )
     *
     * @param                    $arrQueryConfig
     * @param Jelly_Builder|NULL $objQuery -zur Übergabe einer eigenen Query
     * @return bool|array
     * @throws Msc_Exception
     */
    public function retrieveVoyageValuesBySql($arrQueryConfig, $strQuery = null)
    {
        if (!$strQuery) {
            $objBuilder = new GenericBuilder();
            $objQuery = $objBuilder->select('voyagereport')
            // ->setColumns([])
                ->where()
                ->equals('IMO', $this->objShip->getImoNo())
                ->greaterThanOrEqual('date', $this->strFromTs)
                ->lessThanOrEqual('date', $this->strToTs)
                ->end();
        }
        // Spalten
        if (is_array($arrQueryConfig['field_expression'])) {
            $arrColumns = [];
            $objQuery->setColumns($arrColumns);
            foreach ($arrQueryConfig['field_expression'] as $arrField) {
                if (isset($arrField['func'])) {
                    call_user_func_array([$objQuery, 'setFunctionAsColumn'], $arrField);
                    // $objQuery->setFunctionAsColumn($arrField);
                } else {
                    $arrColumns = $arrField;
                }
            }
            if (count($arrColumns)) {
                $objQuery->setColumns($arrColumns);
            }

        }
        //Where
        if (isset($arrQueryConfig['constraints'])) {
            $this->addConstraintsToSqlQuery($objQuery, $arrQueryConfig['constraints']);
        }
        //Having
        if (isset($arrQueryConfig['having'])) {
            $this->addConstraintsToSqlQuery($objQuery, $arrQueryConfig['having'], true);
        }

        $arrResult = $this->findByNativeSql($objBuilder->write($objQuery), $objBuilder->getValues($objQuery), false);
        $arrRow = $arrResult[0];

        // defaults bei Bedarf setzen
        $arrDefaults = Arr::get($arrQueryConfig, 'defaults');
        if ($arrDefaults) {
            foreach ($arrRow as $strIndex => $mixedValue) {
                if (!(float) $mixedValue && Arr::get($arrDefaults, $strIndex)) {
                    $arrRow[$strIndex] = Arr::get($arrDefaults, $strIndex);
                }
            }
        }
        return $arrRow;
    }

    /**
     * Diese Methode fügt der Query min/max-Bedingungen für Felder hinzu
     *
     * @param QueryBuilder $objQuery
     * @param string|array           $arrConstraintsSuffix - suffix für Config-Pfad zu den constraints (reports.constraints.....)
     *                                                     - Struktur: Db-Feldname => Suffix
     * @return QueryBuilder
     * @throws MscException
     */
    protected function addConstraintsToQuery(QueryBuilder $objQuery, $arrConstraintsSuffix)
    {
        if (!is_array($arrConstraintsSuffix)) {
            throw new MscException('ConstraintsArray falsch!!');
        }
        foreach ($arrConstraintsSuffix as $strFieldName => $strConstraintSuffix) {
            $arrConfig = Arr::get($this->arrConstraints, $strConstraintSuffix);
            if (!is_array($arrConfig) || !isset($arrConfig['min']) || !isset($arrConfig['max'])) {
                throw new MscException('Min/Max-Validation-Werte nicht in Config gefunden');
            }

            $objQuery->andWhere("$strFieldName >= :min_$strConstraintSuffix")
                ->setParameter("min_$strConstraintSuffix", $arrConfig['min'])
                ->andWhere("$strFieldName <= :max_$strConstraintSuffix")
                ->setParameter("max_$strConstraintSuffix", $arrConfig['max']);

        }
        return $objQuery;
    }

    /**
     * Selbe wie oben nur mit dem neuen Query-Objekt
     *
     * @param NilPortugues\Sql\QueryBuilder\Manipulation\Select $objQuery
     * @param array $arrConstraintsSuffix
     * @param bool $boolAsHaving - wenn true, dann wird als HAVING gesetzt
     * @return NilPortugues\Sql\QueryBuilder\Manipulation\Select
     */
    public function addConstraintsToSqlQuery(Select $objQuery, $arrConstraintsSuffix, $boolAsHaving = false)
    {
        if (!is_array($arrConstraintsSuffix)) {
            throw new MscException('ConstraintsArray falsch!!');
        }
        foreach ($arrConstraintsSuffix as $strFieldName => $strConstraintSuffix) {
            $arrConfig = Arr::get($this->arrConstraints, $strConstraintSuffix);
            if (!is_array($arrConfig) || !isset($arrConfig['min']) || !isset($arrConfig['max'])) {
                throw new MscException('Min/Max-Validation-Werte für ' . $strConstraintSuffix . ' nicht in Config gefunden');
            }

            if ($boolAsHaving) {
                $objQuery->having()
                    ->greaterThanOrEqual($strFieldName, $arrConfig['min'])
                    ->lessThanOrEqual($strFieldName, $arrConfig['max']);
            } else {
                $objQuery->where()
                    ->greaterThanOrEqual($strFieldName, $arrConfig['min'])
                    ->lessThanOrEqual($strFieldName, $arrConfig['max']);
            }
        }
        return $objQuery;
    }

    /**
     * schaut, ob es einen Eintrag zu der Imo-Nummer und dem Date gibt
     * @param string $strImoNumber
     * @param integer $intTs - UnixTimestamp
     * @return boolean
     */
    public function doesExist($strImoNumber, $intTs)
    {
        $strSql = 'SELECT COUNT(*) FROM  voyagereport WHERE IMO = :imo AND date >= :date';
        $objConn = $this->getEntityManager()
            ->getConnection();

        $objStmt = $objConn->prepare($strSql); //getConnection('marprime')->prepare($strSql);
        $objStmt->execute([
            'imo' => $strImoNumber,
            'date' => date('Y-m-d', $intTs),
        ]);
        return $objStmt->fetchColumn(0) > 0;
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
    public function findByNativeSql($strSql, $arrParams, $boolAsObject = true, $strTableAlias = 'voyagereport')
    {
        $objEntityManager = $this->getEntityManager();
        if ($boolAsObject) {
            $objResultSetMappingBuilder = new ResultSetMappingBuilder($objEntityManager);
            $objResultSetMappingBuilder->addRootEntityFromClassMetadata(Voyagereport::class, $strTableAlias);
            $objQuery = $objEntityManager->createNativeQuery($strSql, $objResultSetMappingBuilder);
            $objQuery->setParameters($arrParams);
            $arrVoyagereport = $objQuery->getResult();
        } else {
            $objStatement = $objEntityManager
                ->getConnection()
                ->prepare($strSql);
            $objStatement->execute($arrParams);
            $arrVoyagereport = $objStatement->fetchAll();
        }
        return $arrVoyagereport;
    }
    // /**
    //  * @return Voyagereport[] Returns an array of Voyagereport objects
    //  */
    /*
    public function findByExampleField($value)
    {
    return $this->createQueryBuilder('v')
    ->andWhere('v.exampleField = :val')
    ->setParameter('val', $value)
    ->orderBy('v.id', 'ASC')
    ->setMaxResults(10)
    ->getQuery()
    ->getResult()
    ;
    }
     */

    /*
public function findOneBySomeField($value): ?Voyagereport
{
return $this->createQueryBuilder('v')
->andWhere('v.exampleField = :val')
->setParameter('val', $value)
->getQuery()
->getOneOrNullResult()
;
}
 */

}
