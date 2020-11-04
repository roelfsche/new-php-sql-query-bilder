<?php

namespace App\Service\Maridis\Model;

use Doctrine\Common\Persistence\ManagerRegistry;

class Report
{
    protected $objDoctrineRegistry = null;

    
    public function __construct(ManagerRegistry $objDoctrineRegistry)
    {
        $this->objDoctrineRegistry = $objDoctrineRegistry;
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
