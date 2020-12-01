<?php
namespace App\Service\Model;

use App\Entity\UsrWeb71\Users;
use App\Kohana\Arr;
use App\Repository\UsrWeb71\UserRepository;

/**
 * Basis-Service für User
 */
class User
{

    /**
     * $objUserRepository
     *
     * @var App\Repository\UsrWeb71\UserRepository;
     */
    protected $objUserRepository;

    /**
     * act User
     *
     * @var App\Entity\UsrWEb71\Users
     */
    protected $objUser;
    /**
     *
     * @var App\Entity\UsrWEb71\Users[]
     */
    private $arrUsers = []; // speichere alle User, die ich im Laufe des Scripts erhalten habe
    /**
     * speichert an der user-id das Rechte-array
     *
     * @var array
     */
    private $arrPermissions = [];

    public function __construct(UserRepository $objUserRepository)
    {
        $this->objUserRepository = $objUserRepository;
    }

    /**
     * liefert das Repo
     *
     * @return App\Repository\UsrWeb71\UserRepository
     */
    public function getRepository()
    {
        return $this->objUserRepository;
    }
    
    public function setUser(Users $objUser)
    {
        if ($this->objUser == $objUser) {
            return;
        }
        $this->objUser = $objUser;
    }

    public function getUser()
    {
        return $this->objUser;
    }

    public function getPermissions()
    {
        $intUserId = $this->objUser->getId();
        if (!Arr::get($this->arrPermissions, $intUserId)) {
            $arrPermissions = $this->objUserRepository->retrievePermissions($this->objUser);
            $arrPermissions = $this->compilePermissions($arrPermissions);
            $this->arrPermissions[$intUserId] = $arrPermissions;

        }
        return $this->arrPermissions[$intUserId];
    }

    /**
     * Diese Methode compiliert alle Rechte in ein Array, welches zurück gegeben wird.
     * Folgende Struktur wird aufgebaut:
     *' = TRUE)
     * Bsp. 'document', array('edit', 'read'), array('oe1', 'oe3') produziert
     * array(
     * 'document' => array(
     *'),
     *')
     * ),
     * 'read' => array(
     *'),
     *')
     * )
     * )
     * )
     *'), array('*'), array('*') mit übergeben worden, wär das compilierte Ergebnis:
     * array(
     * 'document' => array(
     *' => array(
     *' => array('*')
     * )
     * )
     * )
     * geworden, weil die zweite Regel allgemeiner ist und somit die erste ersetzt.
     * @param ArrayIterator $mixedIterator (array von Permissions, //array->Unittest)
     * @return array
     */
    protected function compilePermissions($mixedIterator)
    {
        $arrCompiledPermissions = array();
        foreach ($mixedIterator as $objPermission) {
            $boolFound = false;
            foreach (array_unique(array(
                '*',
                $objPermission->getResource(),
            )) as $strResource) {
                foreach (array_unique(Arr::merge(array(
                    '*',
                ), $objPermission->getPrivilege())) as $strPrivilege) {
                    $strPath = "$strResource.$strPrivilege";
                    if (Arr::path($arrCompiledPermissions, $strPath)) {
                        // raus, weil schon höheres Recht gefunden
                        break 2;
                    }
                    // sonst check, ob es genau dieses Recht ist, dass eingebaut werden soll
                    elseif ($strResource == $objPermission->getResource() && in_array($strPrivilege, $objPermission->getPrivilege())) {
                        // schaue erst noch, ob ich ein unterarray löschen kann, weil dieses evtl.
                        // am ende ein paar sterne hat:
                        // document.*.*.* löscht bspw. document.edit.*.* und document.read.*.*
                        // nehme zuerst alle *.*.* hinten weg
                        $strRmPath = rtrim($strPath, '.*');
                        if ($strRmPath != $strPath) {
                            if (!strlen($strRmPath)) {
                                // check, ob ich von ganz oben an löschen muss
                                $arrCompiledPermissions = array();
                            } else {
                                // sonst nur das Unterarray
                                Arr::set_path($arrCompiledPermissions, $strRmPath, null);
                            }
                        }

                        // ok, Recht wird mit TRUE in das Array eingefügt
                        Arr::set_path($arrCompiledPermissions, "$strResource.$strPrivilege", true);
                    }
                }
            }
        }
        return $arrCompiledPermissions;
    }

}
