<?php

namespace App\Repository;

use App\Entity\DocumentFavoris;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DocumentFavoris|null find($id, $lockMode = null, $lockVersion = null)
 * @method DocumentFavoris|null findOneBy(array $criteria, array $orderBy = null)
 * @method DocumentFavoris[]    findAll()
 * @method DocumentFavoris[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentFavorisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DocumentFavoris::class);
    }

    // /**
    //  * @return DocumentFavoris[] Returns an array of DocumentFavoris objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DocumentFavoris
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

}
