<?php

namespace App\Repository;

use App\Entity\Typeplace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Typeplace|null find($id, $lockMode = null, $lockVersion = null)
 * @method Typeplace|null findOneBy(array $criteria, array $orderBy = null)
 * @method Typeplace[]    findAll()
 * @method Typeplace[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeplaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Typeplace::class);
    }

    // /**
    //  * @return Typeplace[] Returns an array of Typeplace objects
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
    public function findOneBySomeField($value): ?Typeplace
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
