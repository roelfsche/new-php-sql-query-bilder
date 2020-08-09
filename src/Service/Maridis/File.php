<?php

namespace App\Service\Maridis;

use App\Maridis\FileInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Exception;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

// /**
//  * Basisklassen aller .mpi, .noon, .pdf, .mpd usw. Files
//  */
class File// implements FileInterface
{
//     protected $strFilename = NULL;
//     protected $resFileHandle = NULL;
//     protected $arrHeader = [];
//     protected $objLogger = NULL;
//     protected $objShip = NULL;
//     protected $objDoctrineManagerRegistry = NULL;
//     protected $objContainer = NULL;


//     public function __construct(ContainerInterface $objContainer, LoggerInterface $objLogger, ManagerRegistry $objDoctrineManagerRegistry)
//     {
//         $this->objContainer = $objContainer;
//         $this->objLogger = $objLogger;
//         $this->objDoctrineManagerRegistry = $objDoctrineManagerRegistry;
//     }
//     /**
//      * 
//      */
//     // public function setLogger(LoggerInterface $objLogger)
//     // {
//     //     $this->objLogger = $objLogger;
//     // }
//     /***
//      * Setzt den Filename
//      * @param string $strFilename - komplett: Pfad + Filename
//      */
//     public function setFilename(string $strFilename)
//     {
//         $this->strFilename = $strFilename;
//     }

//     /**
//      * Setzt das Filehandle (von fopen)
//      */
//     public function setFileHandle($resFileHandle)
//     {
//         if (!is_resource($resFileHandle)) {
//             throw new Exception('No Resource!');
//         }
//         $this->resFileHandle = $resFileHandle;
//     }

//     /**
//      * checkt, ob das File eines von Maridis-Files ist.
//      * 
//      * Wenn ja, wird ein entsprechendes Interface zurück gegeben.
//      * 
//      * @return FileInterface
//      */
//     public function getMaridisFile($strFilename)
//     {
//         // if (Mpi::is($strFilename)) {
//         //     return new Mpi($strFilename);
//         // }
//         // if (VoyageReport::is($strFilename)) {
//         //     return new VoyageReport($strFilename);
//         // }

//         // // für alle anderen Filetypen muss in die Datei geschaut werden
//         if (!$resFileHandle = fopen($strFilename, "rb")) {
//             throw new FileNotFoundException("Could not open file: ", $strFilename);
//         }

//         if (Mpd::is($resFileHandle)) {
//             $obFile = $this->objContainer->get()
//             return new Mpd($strFilename);
//         }
//     }

//     /**
//      * checkt, ob dieses File vom Typ ist
//      * muss in den subklassen überschrieben werden
//      */
//     public function is($mixedFileHandleOrName): bool
//     {
//         throw new Exception('Overwrite this!!!');
//         return FALSE;
//     }

//     public function process()
//     {
//         throw new Exception('Overwrite this!!!');
//     }
}
