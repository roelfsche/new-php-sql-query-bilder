<?php

namespace App\Maridis;

/**
 * Interface, dass alle Maridis-Files implementieren müssen
 */
interface FileInterface
{
    public function process();
    public function getShip();
}
