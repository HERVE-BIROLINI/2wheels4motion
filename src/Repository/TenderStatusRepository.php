<?php

namespace App\Repository;

use App\Entity\TenderStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TenderStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method TenderStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method TenderStatus[]    findAll()
 * @method TenderStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TenderStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TenderStatus::class);
    }

    // /**
    //  * @return TenderStatus[] Returns an array of TenderStatus objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TenderStatus
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
