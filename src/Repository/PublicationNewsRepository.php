<?php

namespace App\Repository;

use App\Entity\PublicationNews;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PublicationNews|null find($id, $lockMode = null, $lockVersion = null)
 * @method PublicationNews|null findOneBy(array $criteria, array $orderBy = null)
 * @method PublicationNews[]    findAll()
 * @method PublicationNews[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PublicationNewsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PublicationNews::class);
    }

    // /**
    //  * @return PublicationNews[] Returns an array of PublicationNews objects
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
    public function findOneBySomeField($value): ?PublicationNews
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
