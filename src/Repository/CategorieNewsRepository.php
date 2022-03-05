<?php

namespace App\Repository;

use App\Entity\CategorieNews;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CategorieNews|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategorieNews|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategorieNews[]    findAll()
 * @method CategorieNews[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategorieNewsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategorieNews::class);
    }

    // /**
    //  * @return CategorieNews[] Returns an array of CategorieNews objects
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
    public function findOneBySomeField($value): ?CategorieNews
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findAllCategoryNames()
    {
        return $this->createQueryBuilder('c')
            ->select('c.categoryName')
            ->getQuery()
            ->getResult()
        ;
    }
    function SearchByName($categoryName){
        return $this->createQueryBuilder('s')
            ->where('s.categoryName like :categoryName')
            ->setParameter('categoryName','%'.$categoryName.'%')
            ->getQuery()
            ->getResult();
    }
    public function CountEmploi(){
        $em = $this->getEntityManager();
        $qb= $em
        ->createQuery('SELECT count(p) FROM APP\ENTITY\CategorieNews p');
        return $qb->getSingleScalarResult();
    }
}
