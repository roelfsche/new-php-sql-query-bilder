<?php
namespace App\Exception;

use Exception;

class MscException extends Exception
{
    public function __construct($strMessage, $arrParameters = null)
    {
        if ($arrParameters) {
            parent::__construct(strtr($strMessage, $arrParameters));
        }
    }
}
