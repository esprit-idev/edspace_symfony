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

    public function find_all_approved_pub_ordredByDate($idclub)
    {
        return $this->createQueryBuilder('c')
            ->where('c.club = :id')
            ->setParameter('id', $idclub)
            ->andwhere('c.isPosted = 1')
            ->orderBy('c.pubDate','DESC')
            ->getQuery()
            ->getResult()
        ;
    }
    public function find_all_hanging_pub_ordredByDate($idclub)
    {
        return $this->createQueryBuilder('c')
            ->where('c.club = :id')
            ->setParameter('id', $idclub)
            ->andwhere('c.isPosted = 0')
            ->orderBy('c.pubDate','DESC')
            ->getQuery()
            ->getResult()
            ;
    }
    public function find_all_refused_pub_ordredByDate($idclub)
    {
        return $this->createQueryBuilder('c')
            ->where('c.club = :id')
            ->setParameter('id', $idclub)
            ->andwhere('c.isPosted = -1')
            ->orderBy('c.pubDate','DESC')
            ->getQuery()
            ->getResult()
            ;
    }



    public function find_all_pub_between_dates($minDate,$maxDate,$idclub)
    {

        return $this->createQueryBuilder('c')
            ->where('c.club = :id')
            ->setParameter('id', $idclub)
            ->andwhere('c.pubDate >= :date')
            ->setParameter('date', $minDate)
            ->andWhere('c.pubDate <= :date2')
            ->setParameter('date2', $maxDate)
            ->andwhere('c.isPosted = 1')
            ->orderBy('c.pubDate','DESC')
            ->getQuery()
            ->getResult()
        ;
    }

}
