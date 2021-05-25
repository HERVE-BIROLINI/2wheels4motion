<?php

namespace App\Repository;

use App\Entity\Picturelabel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Picturelabel|null find($id, $lockMode = null, $lockVersion = null)
 * @method Picturelabel|null findOneBy(array $criteria, array $orderBy = null)
 * @method Picturelabel[]    findAll()
 * @method Picturelabel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PicturelabelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Picturelabel::class);
    }

    // /**
    //  * @return Picturelabel[] Returns an array of Picturelabel objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Picturelabel
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
