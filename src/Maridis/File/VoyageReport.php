<?php

namespace App\Maridis\File;

use App\Maridis\File;
use App\Maridis\FileInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class VoyageReport extends File implements FileInterface
{
    public static function getIf(ContainerInterface $objContainer, $resFileHandle): ?FileInterface 
    {

        $strFilename = self::getFileNameFromResource($resFileHandle);
        $arrTmp = array();
        if (!preg_match('/VoyageReport_IMO(\d)+_\d{4}-(\d{2})-(\d{2})_(\d{1,2})_(\d{1,2})_(\d{1,2})/', $strFilename, $arrTmp)) {
            return NULL;
        }

        return new VoyageReport($objContainer, $resFileHandle);
    }

    public function process()
    { }
}
