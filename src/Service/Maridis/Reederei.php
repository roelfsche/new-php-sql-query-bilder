<?php

namespace App\Service\Maridis;

use App\Entity\UsrWeb71\Reederei as UsrWeb71Reederei;
use App\Entity\UsrWeb71\ShipTable;
use App\Exception\MscException;
use App\Kohana\Arr;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class Reederei
{
    private $objReederei = null;
    private $objParameterInterface = null;
    private $objContainer = null;
    private $objReedereiRepository = null;
    private $objImageFilterService = null;

    public function __construct(ContainerInterface $objContainer, ContainerBagInterface $objParameterInterface/*, LiipFilterService $objImageFilterService*/)
    {
        $this->objParameterInterface = $objParameterInterface;
        $this->objContainer = $objContainer;
        // $this->objImageFilterService = $objImageFilterService;
        $this->objImageFilterService = $objContainer->get('liip_imagine.service.filter');

        $objDoctrineManager = $this->objContainer->get('doctrine');

        $this->objReedereiRepository = $objDoctrineManager
            ->getManager('default')
            ->getRepository(UsrWeb71Reederei::class);
    }

    public function setShip(ShipTable $objShip)
    {
        $this->objShip = $objShip;
        if (!$this->objReederei) {
            $this->objReederei = $this->objReedereiRepository->findOneByShip($objShip);
        }
    }

    public function setReederei(UsrWeb71Reederei $objReederei)
    {
        $this->objReederei = $objReederei;
    }

    public function getReederei()
    {
        return $this->objReederei;
    }
    /**
     * Diese methode resized das Logo zu x oder y (oder beidem) und speichert dann einen tmp-version ab.
     *
     * zurück gegeben wird der komlette pfad zur datei (einschl. filename)
     *
     * @param null $intWidth
     * @param null $intHeight
     * @param int  $intImageType
     * @return bool|string
     */
    public function getLogoFilePath($intWidth = null, $intHeight = null)
    {

        $strFilename = $this->getFlagFilename(true);
        if (!$strFilename) {
            return false;
        }
        if ($intWidth == 120) {
            return $strFilename;
        }
        // $strTmpFilename = tempnam(sys_get_temp_dir(), 'msc_logo_');

        // resize

        // $runtimeConfig = [
        //     'thumbnail' => [
        //         'size' => [200, 200],
        //     ],
        // ];
        $resourcePath = $this->objImageFilterService->getUrlOfFilteredImage($strFilename, 'pdf_flags');
        return $resourcePath;
        // return str_replace('http://localhost', $this->objContainer->get('kernel')->getProjectDir() . '/web', $resourcePath);

        // $objImage = Image::factory($strFilename);
        // if ($intWidth || $intHeight) {
        //     $objImage->resize($intWidth, $intHeight);
        //     $objImage->save($strTmpFilename);
        //     return $strTmpFilename;
        // }
        return false;
    }

    /**
     * Diese Methode gibt den vollständigen Pfad zurück
     *
     * @return bool|string - FALSE, wenn keine Datei
     * @throws Kohana_Exception
     */
    public function getFlagFilename($boolRelative = false)
    {
        if (!$this->objReederei) {
            throw new MscException('shipping company not set');
        }
        // wenn leerer Upload, dann ist das Feld das Upload-Array
        if (!is_string($this->objReederei->getFlagge())) {
            return false;
        }
        if ($boolRelative) {
            return $this->objReederei->getFlagge();
        }
        $strPath = Arr::path($this->objParameterInterface->get('reports'), 'paths.flag.upload');
        if ($strPath) {
            return $strPath . $this->objReederei->getFlagge();
        }
        return false;
    }

}
