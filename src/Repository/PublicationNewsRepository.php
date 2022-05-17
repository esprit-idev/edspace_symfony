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

    public function findNewsByCategory($categoryName){
        return $this->createQueryBuilder('p')
        ->join('p.categoryName', 'c')
        ->addSelect('c')
        ->where('c.categoryName=:categoryName')
        ->setParameter('categoryName',$categoryName)
        ->getQuery()
        ->getResult();
    }

    function SearchByTitle($title){
        return $this->createQueryBuilder('s')
            ->where('s.title like :title')
            ->setParameter('title','%'.$title.'%')
            ->getQuery()
            ->getResult();
    }

    public function SortByDateASC()
    {
        return $this
            ->createQueryBuilder('e')
            ->OrderBy('e.date', 'ASC')
            ->andWhere('e.date > :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult()
        ;
    }
    public function CountPublications(){
        $em = $this->getEntityManager();
        $qb= $em
        ->createQuery('SELECT count(p) FROM APP\ENTITY\PublicationNews p');
        return $qb->getSingleScalarResult();
    }

    public function ListPublicationByCategory($id){
        $em = $this->getEntityManager();
        return $em
        ->createQuery("SELECT count(p) FROM APP\ENTITY\PublicationNews p JOIN p.categoryName c where c.id:=id")
        ->setParameter('id',$id);
    }

    public function getPreviousUser($title)
{
    return $this->createQueryBuilder('p')
        ->select('p')
        ->where('p.title < :title')
        ->setParameter(':title', $title)
        // Order by id.
        ->orderBy('p.id', 'ASC')
        // Get the first record.
        ->setFirstResult(0)
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult()
    ;
}
public function findProductsOfCategory($id)
{
    $em = $this->getEntityManager();
    $query=$em
    ->createQuery("SELECT p FROM APP\ENTITY\PublicationNews p JOIN p.categoryName c where c.id=:id")
    ->setParameter('id',$id);
    return $query->getResult();
}
public function findAllLikesByPublication($id){
    return $this->createQueryBuilder('p')
            ->select('p.likes')
            ->where('p.id =:id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleScalarResult();
}
public function limitPublications(){
    return $this->createQueryBuilder('p')
            ->select('p')
            ->setMaxResults(4)
            ->getQuery()
            ->getResult();
}
public function updateLike($likes, $id){
    return $this->createQueryBuilder('u')
        ->update()
        ->set('u.likes', $likes)
        ->where('u.id = :id')
        ->setParameter('id', $id)
        ->getQuery()
        ->execute();
}
}
