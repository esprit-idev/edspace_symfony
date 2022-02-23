<?php

namespace App\Repository;

use App\Entity\ThreadType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ThreadType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ThreadType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ThreadType[]    findAll()
 * @method ThreadType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ThreadTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ThreadType::class);
    }

    // /**
    //  * @return ThreadType[] Returns an array of ThreadType objects
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
    public function findOneBySomeField($value): ?ThreadType
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findDisplay(){
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT t
            FROM App\Entity\ThreadType t
            WHERE t.display = :t
            '
        )->setParameter('t', '0');

        // returns an array of Product objects
        return $query->getResult();
    }
}
