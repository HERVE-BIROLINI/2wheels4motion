<?php

namespace App\Repository;

use App\Entity\Socialreason;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Socialreason|null find($id, $lockMode = null, $lockVersion = null)
 * @method Socialreason|null findOneBy(array $criteria, array $orderBy = null)
 * @method Socialreason[]    findAll()
 * @method Socialreason[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SocialreasonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Socialreason::class);
    }

    // /**
    //  * @return Socialreason[] Returns an array of Socialreason objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Socialreason
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
