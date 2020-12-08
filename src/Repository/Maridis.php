<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use NilPortugues\Sql\QueryBuilder\Builder\GenericBuilder;
use NilPortugues\Sql\QueryBuilder\Manipulation\Select;

/**
 * Basisklasse meiner  Repos
 * 
 * Musste abstrakt sein, damit ich string $entityClass autowiren kann
 */
abstract class Maridis extends ServiceEntityRepository
{

    /**
     * Entity Klasse
     *
     * @var string
     */
    private $strEntityClass;

    /**
     * Builder
     *
     * @var NilPortugues\Sql\QueryBuilder\Builder\GenericBuilder
     */
    private $objBuilder;
    /**
     * @param string $entityClass The class name of the entity this repository manages
     */
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
        $this->strEntityClass = $entityClass;
        $this->objBuilder = new GenericBuilder();
    }

    /**
     * Liefert die Eintitätenklasse
     *
     * @return string
     */
    public function getEntityClass(): string {
        return $this->strEntityClass;
    }
    /**
     * führt eine native SQL aus und liefert das Ergebnis als Array von Arrays oder ShipTable zurück
     *
     * @param string $strSql - SQL
     * @param array $arrParams - [':name' => value, ...]
     * @param boolean $boolAsObject - wenn true, dann ShipTable[], sonst [[], [], ...]
     * @param string $strTableAlias - braucht Doctrine, um die Spalten zu finden: Select a.*  --> a
     * @return array asso | oder von Entitäten
     */
    public function findByNativeSql($strSql, $arrParams, $boolAsObject = true, $strTableAlias = 'ship_table')
    {
        $objEntityManager = $this->getEntityManager();
        if ($boolAsObject) {
            $objResultSetMappingBuilder = new ResultSetMappingBuilder($objEntityManager);
            $objResultSetMappingBuilder->addRootEntityFromClassMetadata($this->strEntityClass, $strTableAlias);
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

    /**
     * DB-Abfrage mittels Select-Objekte
     *
     * @param Select $objQuery
     * @param boolean $boolAsObject
     * @return void
     */
    public function findBySelect(Select $objQuery, $boolAsObject = true) {
        
        return $this->findByNativeSql($this->objBuilder->write($objQuery), $this->objBuilder->getValues($objQuery), $boolAsObject, $objQuery->getTable()->getName(), $this->strEntityClass);
    }
}
