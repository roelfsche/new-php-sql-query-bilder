<?php

namespace App\Service\Maridis\Model;

use App\Entity\UsrWeb71\Reederei;
use Doctrine\Common\Persistence\ManagerRegistry;

class Report
{
    protected $objDoctrineRegistry = null;

    /**
     * @var ShipTable
     */
    protected $objShip = null;

    protected $objReedereiRepository = null;

    protected $objReedereiService = null;

    public function __construct(ManagerRegistry $objDoctrineRegistry)
    {
        $this->objDoctrineRegistry = $objDoctrineRegistry;

        $this->objReedereiRepository = $objDoctrineRegistry
            ->getManager('default')
            ->getRepository(Reederei::class);

    }

    // public function getReederei()
    // {
    //     if (!$this->objShip) {
    //         throw new MscException("Ship not set");
    //     }
    //     return $this->objReedereiRepository->findOneByShip($this->objShip);
    // }

    /**
     * Werte als Array zurück
     *
     * liefert die Werte dann indiziert zurück (brauch ich für Plot-Lib)
     *
     * @param @array $arrEntities -  Array von Entitäten
     * @param @array $arrKeys : Keys
     */
    public function getEntityValues($arrEntities, $arrKeys)
    {
        // $arrRet = [0];
        foreach ($arrEntities as $objEntity) {
            $arrRet[$objEntity->{$arrKeys[0]}] = $objEntity->{$arrKeys[1]};
        }
        return $arrRet;
    }

    /**
     * Diese Methode extrahiert aus dem Array von Arrays jeweils 2 Werte und setzt den einen als Key für den anderen isn return - array
     * @return array
     */
    public function as_array($arrValues, $strKeyKey, $strKeyValue) {
        $arrRet = [];
        foreach($arrValues as $arrArr) {
            $arrRet[$arrArr[$strKeyKey]] = $arrArr[$strKeyValue];
        }
        return $arrRet;
    }

    /**
     * setze die min-Values nach der Berechnung auf 0, wenn noch INF, weil mit INF starteten.
     * @param $array
     * @return mixed
     */
    protected function resetInfValues($array, $strParentArrayKey = '')
    {
        foreach ($array as $strKey => $strValue) {
            if (is_array($strValue)) {
                $array[$strKey] = $this->resetInfValues($strValue, $strKey);
            }
            if ($strParentArrayKey == 'min' && $strValue == PHP_INT_MAX) {
                $array[$strKey] = 0;
            }
        }
        return $array;
    }
}
