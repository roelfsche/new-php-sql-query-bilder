<?php

namespace App\Imagine\Cache\Resolver;

use Doctrine\Common\Cache\Cache;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Symfony\Component\Filesystem\Filesystem;

class TmpResolver implements ResolverInterface
{
    private $strRootPath = '';
    private $objFilesystem = null;
    /**
     * Constructor.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->objFilesystem = $filesystem;

        $this->strRootPath = sys_get_temp_dir();
    }

    /**
     * @param string $path
     * @param string $filter
     *
     * @return bool
     */
    public function isStored($path, $filter)
    {
        return file_exists($this->getFullPath($path, $filter));
    }

    /**
     * @param string $path
     * @param string $filter
     *
     * @return string
     */
    public function resolve($path, $filter)
    {
        return $this->getFullPath($path, $filter);
    }

    /**
     * @param BinaryInterface $binary
     * @param string          $path
     * @param string          $filter
     */
    public function store(BinaryInterface $binary, $path, $filter)
    {
        $strFilename = $this->getFullPath($path, $filter);
        $this->objFilesystem->dumpFile($strFilename, $binary->getContent());
        // file_put_contents($strFilename, $binary);
    }

    /**
     * @param string[] $paths
     * @param string[] $filters
     */
    public function remove(array $paths, array $filters)
    {
        foreach ($paths as $path) {
            foreach ($filters as $filter) {
                if ($this->isStored($path, $filter)) {
                    $strFilename = $this->getFullPath($path, $filter);
                    unlink($strFilename);
                }
            }
        }
    }

    private function getFullPath($path, $filter)
    {
        return $this->strRootPath . DIRECTORY_SEPARATOR . $filter . DIRECTORY_SEPARATOR . $path;
    }
}
