<?php

namespace App\Repository;

use App\Entity\Emploi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Emploi|null find($id, $lockMode = null, $lockVersion = null)
 * @method Emploi|null findOneBy(array $criteria, array $orderBy = null)
 * @method Emploi[]    findAll()
 * @method Emploi[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmploiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Emploi::class);
    }

    // /**
    //  * @return Emploi[] Returns an array of Emploi objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Emploi
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    function SearchByTitle($title){
        return $this->createQueryBuilder('s')
            ->where('s.title like :title')
            ->setParameter('title','%'.$title.'%')
            ->getQuery()
            ->getResult();
    }
    
    public function findNewsByCategory($categoryName){
        return $this->createQueryBuilder('p')
        ->join('p.categoryName', 'c')
        ->addSelect('c')
        ->where('c.categoryName=:categoryName')
        ->setParameter('categoryName',$categoryName)
        ->getQuery()
        ->getResult();
    }

    public function CountEmploi(){
        $em = $this->getEntityManager();
        $qb= $em
        ->createQuery('SELECT count(p) FROM APP\ENTITY\Emploi p');
        return $qb->getSingleScalarResult();
    }
    public function ListEmploiByCategory($id)
    {
    $em = $this->getEntityManager();
    $query=$em
    ->createQuery("SELECT e FROM APP\ENTITY\Emploi e JOIN e.categoryName c where c.id=:id")
    ->setParameter('id',$id);
    return $query->getResult();
    }

}
