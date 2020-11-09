<?php

namespace App\Repository\Marprime;

use App\Entity\Marprime\Report;
use App\Exception\MscException;
use App\Kohana\Arr;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Report|null find($id, $lockMode = null, $lockVersion = null)
 * @method Report|null findOneBy(array $criteria, array $orderBy = null)
 * @method Report[]    findAll()
 * @method Report[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReportRepository extends ServiceEntityRepository
{
    private $arrParameterCache = [];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Report::class);
    }

    public function retrieveReportCount($strSerialNumber, $strDate)
    {
        return (int)$this->retrieveDbValue('SELECT COUNT(*) AS count FROM report WHERE MarPrimeSerial = :mp_number AND date = :date', $strSerialNumber, $strDate)->fetchColumn(0);
    }

    public function retrieveDbValue($strSQL, $strSerialNumber = null, $strDate = null)
    {
        extract($this->findByCheckCacheParameter([
            'strSerialNumber' => $strSerialNumber,
            'strDate' => $strDate,
        ]));

        $objMpConn = $this->getEntityManager()
            ->getConnection();

        $objStatement = $objMpConn->prepare($strSQL);
        $objStatement->execute([
            ':mp_number' => $strSerialNumber,
            ':date' => $strDate,
        ]);
        return $objStatement; //->fetchColumn(0);
    }

    /**
     * speichere funktionsparameter zwischen, um sie nicht immer übergeben zu müssen
     */
    protected function findByCheckCacheParameter($arrParameter)
    {
        foreach ($arrParameter as $strKey => $mixedValue) {
            if ($mixedValue === null) {
                if (!Arr::get($this->arrParameterCache, $strKey)) {
                    throw new MscException("Parameter nicht gesetzt $strKey");
                }
                $arrParameter[$strKey] = Arr::get($this->arrParameterCache, $strKey);
            } else {
                $this->arrParameterCache[$strKey] = $mixedValue;
            }
        }
        return $arrParameter;
    }
}
