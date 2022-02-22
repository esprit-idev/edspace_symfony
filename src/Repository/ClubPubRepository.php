<?php

namespace App\Repository;

use App\Entity\ClubPub;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ClubPub|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClubPub|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClubPub[]    findAll()
 * @method ClubPub[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClubPubRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClubPub::class);
    }


    // /**
    //  * @return ClubPub[] Returns an array of ClubPub objects
    //  */
/*
    public function findAllOrdredByDate($value)
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }*/


    /*
    public function findOneBySomeField($value): ?ClubPub
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
