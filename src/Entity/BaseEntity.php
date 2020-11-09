<?php
namespace App\Entity;

use App\Exception\MscException;
use App\Kohana\Arr;

class BaseEntity
{
    // Format für die date-Spalte in der DB
    const strDateFormat = 'Y-m-d H:i:s';

    private $arrAdditionalParameter = [];

    /**
     * Überbleibsel von Jelly
     */
    public function set($strName, $mixedValue)
    {
        $this->{$strName} = $mixedValue;
    }
    public function get($strName)
    {
        return $this->{$strName};
    }
    public function __set($strName, $mixedValue)
    {
        $this->arrAdditionalParameter[$strName] = $mixedValue;
    }
    /**
     * Magic-Getter, der $this->date nimmt und $this->getDate() aufruft
     *
     */
    public function __get($strField)
    {
        if (Arr::get($this->arrAdditionalParameter, $strField)) {
            return Arr::get($this->arrAdditionalParameter, $strField);
        }

        switch ($strField) {
            case 'marprime_serial_number':
                return $this->getMarprimeSerialno();
            default:
                $strGetter = 'get' . $this->toCamelCase($strField, true);
                if (method_exists($this, $strGetter)) {
                    return $this->{$strGetter}();
                }
        }
        throw new MscException("Feld $strField nicht am Objekt vorhanden");
    }

    private static function toCamelCase($str, $capitaliseFirstChar = false)
    {
        if ($capitaliseFirstChar) {
            $str[0] = strtoupper($str[0]);
        }
        return str_replace('_', '', ucwords($str, '_'));
        // return preg_replace('/_([a-z])/e', "strtoupper('\\1')", $str);
    }
}
