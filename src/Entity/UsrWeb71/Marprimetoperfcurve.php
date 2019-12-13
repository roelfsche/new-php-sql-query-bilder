<?php

namespace App\Entity\UsrWeb71;

use Doctrine\ORM\Mapping as ORM;

/**
 * Marprimetoperfcurve
 *
 * @ORM\Table(name="MarPrimeToPerfCurve")
 * @ORM\Entity
 */
class Marprimetoperfcurve
{
    /**
     * @var string
     *
     * @ORM\Column(name="EngineType", type="string", length=20, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $enginetype;

    /**
     * @var string
     *
     * @ORM\Column(name="RefEngine", type="string", length=20, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $refengine;

    public function getEnginetype(): ?string
    {
        return $this->enginetype;
    }

    public function getRefengine(): ?string
    {
        return $this->refengine;
    }


}
