<?php

namespace App\Repository;

use App\Entity\ClaimStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ClaimStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClaimStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClaimStatus[]    findAll()
 * @method ClaimStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClaimStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClaimStatus::class);
    }

    // /**
    //  * @return ClaimStatus[] Returns an array of ClaimStatus objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ClaimStatus
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
