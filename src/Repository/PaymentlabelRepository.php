<?php

namespace App\Repository;

use App\Entity\Paymentlabel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Paymentlabel|null find($id, $lockMode = null, $lockVersion = null)
 * @method Paymentlabel|null findOneBy(array $criteria, array $orderBy = null)
 * @method Paymentlabel[]    findAll()
 * @method Paymentlabel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentlabelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Paymentlabel::class);
    }

    // /**
    //  * @return Paymentlabel[] Returns an array of Paymentlabel objects
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
    public function findOneBySomeField($value): ?Paymentlabel
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
