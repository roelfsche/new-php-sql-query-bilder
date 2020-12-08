<?php
namespace App\Service\Maridis\Model;

use App\Repository\Marnoon\VoyagereportsRepository;
use App\Repository\UsrWeb71\ShipTableRepository;
use App\Service\Maridis\Model\User;
use NilPortugues\Sql\QueryBuilder\Builder\GenericBuilder;
use NilPortugues\Sql\QueryBuilder\Manipulation\Select;

class Voyage
{
    /**
     * Undocumented variable
     *
     * @var App\Repository\Marnoon\VoyagereportsRepository
     */
    private $objVoyageRepo;

    /**
     *
     * @var App\Repository\UsrWeb71\ShipTableRepository
     */
    private $objShipTableRepository;
    private $objUserService;

    /**
     * Zur korrekten Verwendung, muss im UserService vorher der User gesetzt werden!!
     *
     * @param VoyagereportsRepository $objVoyageRepo
     * @param User $objUserService
     * @param ShipTableRepository $objShipTableRepository
     */
    public function __construct(VoyagereportsRepository $objVoyageRepo, User $objUserService, ShipTableRepository $objShipTableRepository)
    {
        $this->objVoyageRepo = $objVoyageRepo;
        $this->objUserService = $objUserService;
        $this->objShipTableRepository = $objShipTableRepository;
    }

    public function getAllShipsForUser($boolOnlyFavorites = false, $arrFilterImoNumbers = null)
    {
        $objBuilder = new GenericBuilder();
        if (!$arrFilterImoNumbers) {
            $objQuery = $objBuilder->select('ship_table', ['IMO_No']);
            $arrShips = $this->objUserService->getAllowedShips(null, false, null, 'list', false, $objQuery, false);
            $arrShipIds = array_map('current', $arrShips);
        }

        $objQuery = $objBuilder->select('voyagereport', ['IMO']);
        $objQuery->distinct()
            ->where()
            ->in('IMO', $arrShipIds);
        // ->equals('IMO', 9692698);

        // var_dump($objBuilder->writeFormatted($objQuery));exit();

        $objShipQuery = $objBuilder->select('ship_table')
            ->orderBy('id');

        if ($boolOnlyFavorites) {
            $objShipQuery->join('ship_favorites', 'id', 'ship_id')
                ->on()
                ->equals('user_id', $this->objUserService->getUser());
        }

        // var_dump($objBuilder->writeFormatted($objShipQuery));exit();
        return $this->getAllShips($objQuery, $objShipQuery);
    }

    /**
     * Diese Methode liefert alle Schiffe als Objekte, die in der voyage_data-Tabelle einen Eintrag haben
     *
     * @param \NilPortugues\Sql\QueryBuilder\Manipulation\Select $objQuery     - Query-Objekt für den Zugriff auf Tabelle 'voyagereport'
     * @param \NilPortugues\Sql\QueryBuilder\Manipulation\Select $objShipQuery - Query-Objekt für den Zugriff auf die Tabelle 'ships'
     *
     * @return Jelly_Collection von Model_Row_Ship's
     */
    public function getAllShips(Select $objQuery = null, Select $objShipQuery = null)
    {
        $objBuilder = new GenericBuilder();
        if (!$objQuery) {
            $objQuery = $objBuilder->select('voyagereport', ['IMO'])
                ->distinct();
        }

        $objQuery->where()
            ->greaterThan('IMO', 0);

        $arrImoNumbers = $this->objVoyageRepo->findByNativeSql($objBuilder->write($objQuery), $objBuilder->getValues(), false);
        $arrImoNumbers = array_map('current', $arrImoNumbers);

        if (!count($arrImoNumbers)) {
            // Dummy, falls die Selektion oben nichts liefert
            $arrImoNumbers = [-1];
        }

        if (!$objShipQuery) {
            $objShipQuery = $objBuilder->select('ship_table', ['*']);
        }

        $objShipQuery->where()
            ->in('IMO_No', $arrImoNumbers);
        return $this->objShipTableRepository->findByNativeSql($objBuilder->write($objShipQuery), $objBuilder->getValues());
    }

    /**
     * Diese Methoded generiert einen eindeutigen Hash über die Ids der Zeilen in der Collection
     *
     * @param GeneratedShip[]
     * @return string
     */
    public function generateHash($arrShips)
    {
        $arrIds = [];
        foreach ($arrShips as $objShip) {
            $arrIds[] = $objShip->getId();
        }
        $strIds = implode('-', $arrIds);
        $strHash = md5($strIds);
        return $strHash;
    }

}
