<?php

namespace App\Service\Maridis\Model;

use App\Entity\UsrWeb71\Reederei;
use App\Exception\MscException;
use App\Kohana\Arr;
use App\Kohana\Valid;
use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Container\ContainerInterface;

class Report
{
    protected $objDoctrineRegistry = null;

    /**
     * @var ShipTable
     */
    protected $objShip = null;

    protected $objReedereiRepository = null;

    protected $objReedereiService = null;

    /**
     * enthält die min./max-Intervall-Werte für einzelne DB-Werte
     * siehe service.yml
     */
    protected $arrConstraints = null;

    public function __construct(ContainerInterface $objContainer, ManagerRegistry $objDoctrineRegistry)
    {
        $this->objContainer = $objContainer;
        $this->objDoctrineRegistry = $objDoctrineRegistry;
        $arrParameter = $objContainer->getParameter('reports');
        $this->arrConstraints = Arr::get($arrParameter, 'constraints', []);

        $this->objReedereiRepository = $objDoctrineRegistry
            ->getManager('default')
            ->getRepository(Reederei::class);

    }

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
    public function resetInfValues($array, $strParentArrayKey = '')
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

        /**
     * Diese Methode checkt, ob ein Wert innerhalb eines Intervalls ist. Wenn nicht, wird der default-Wert zurück gegeben.
     *
     * public static, weil auch von Model_Row_Voyage_Report aufgerufen
     *
     * @param     $mixedValue
     * @param     $strConfig
     * @param int $mixedDefault
     * @return int
     * @throws Msc_Exception
     */
    public function sanitizeValue($mixedValue, $strConfig, $mixedDefault = 0)
    {
        if (self::isValid($mixedValue, $strConfig))
        {
            return $mixedValue;
        }
        return $mixedDefault;
    }

        /**
     * Diese Methode checkt, ob ein Wert innerhalb eines Intervalls ist.
     * Die Intervallgrenzen sind in der config definiert: report.validation
     * Sie müssen als Array-Indizes 'min', 'max' heissen.
     *
     * @param        $mixedValue
     * @param string $strConfig - Suffix zum finden der Config (bspw. 'time_at_sea' => report.validation.time_at_sea)
     * @return bool
     * @throws Msc_Exception
     */
    public function isValid($mixedValue, $strConfig)
    {
        $arrConfig = Arr::get($this->arrConstraints, $strConfig);
        // $arrConfig = Kohana::$config->load('report.constraints.' . $strConfig);
        if (!is_array($arrConfig) || !isset($arrConfig['min']) || !isset($arrConfig['max']))
        {
            throw new MscException('Min/Max-Validation-Werte nicht in Config gefunden');
        }

        return Valid::range($mixedValue, $arrConfig['min'], $arrConfig['max']);
        // return $mixedValue >= $arrConfig['min'] &&  $mixedValue <= $arrConfig['max'];
    }
}
