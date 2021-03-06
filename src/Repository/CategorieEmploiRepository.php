<?php

namespace App\Repository;

use App\Entity\CategorieEmploi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CategorieEmploi|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategorieEmploi|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategorieEmploi[]    findAll()
 * @method CategorieEmploi[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategorieEmploiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategorieEmploi::class);
    }

    // /**
    //  * @return CategorieEmploi[] Returns an array of CategorieEmploi objects
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
    public function findOneBySomeField($value): ?CategorieEmploi
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    function SearchByName($categoryName){
        return $this->createQueryBuilder('s')
            ->where('s.categoryName like :categoryName')
            ->setParameter('categoryName','%'.$categoryName.'%')
            ->getQuery()
            ->getResult();
    }
    public function findEmploisOfCategory()
    {
        return $this->createQueryBuilder('c')
            ->select('c, e')
            ->leftJoin('c.emplois','e')
            ->getQuery()
            ->getResult()
        ;
    }
}
