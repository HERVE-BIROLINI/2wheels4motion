<?php

namespace App\Repository;

use App\Entity\Flatrate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Flatrate|null find($id, $lockMode = null, $lockVersion = null)
 * @method Flatrate|null findOneBy(array $criteria, array $orderBy = null)
 * @method Flatrate[]    findAll()
 * @method Flatrate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FlatrateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Flatrate::class);
    }

    // /**
    //  * @return Flatrate[] Returns an array of Flatrate objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Flatrate
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
