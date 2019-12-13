<?php

namespace App\Repository\UsrWeb71;

use App\Entity\UsrWeb71\InterfaceErrorMails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method InterfaceErrorMails|null find($id, $lockMode = null, $lockVersion = null)
 * @method InterfaceErrorMails|null findOneBy(array $criteria, array $orderBy = null)
 * @method InterfaceErrorMails[]    findAll()
 * @method InterfaceErrorMails[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InterfaceErrorMailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InterfaceErrorMails::class);
    }

    /**
     * fügt eine Mail ein
     * 
     * @param stdClass (siehe @Mailbox->getMailsInfo()@)
     * @return App\Entity\UsrWeb71\InterfaceErrorMail
     */
    public function insertMail(object $objMailInfo)
    {
        $objEntityManager = $this->getEntityManager();
        $objInterfaceErrorMail = new InterfaceErrorMails();
        $objInterfaceErrorMail->setMessageId($objMailInfo->message_id)
            ->setHeader(json_encode($objMailInfo))
            ->setCreateTs(time());
        $objEntityManager->persist($objInterfaceErrorMail);
        $objEntityManager->flush();

        return $objInterfaceErrorMail;
    }

    // /**
    //  * @return InterfaceErrorMails[] Returns an array of InterfaceErrorMails objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?InterfaceErrorMails
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
