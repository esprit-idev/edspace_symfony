<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */

    public function findEmails($id) {
        $qb = $this->createQueryBuilder('u');
        return $qb
            // find all users where 'role' is NOT '['ROLE_RESPONSABLE']'
            ->where('u.roles NOT LIKE :roles')
            ->setParameter('roles','%"'.'ROLE_RESPONSABLE'.'"%')
            ->orwhere('u.club = :id')
            ->setParameter('id',$id)

            ;
    }

    public function findByRole($role) {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('u')
            ->from($this->_entityName, 'u')
            ->where('u.roles LIKE :roles')
            ->setParameter('roles', '%"' . $role . '"%');
        return $qb->getQuery()->getResult();
    }

   /* public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }*/

    public function CountUsers(){
        $em = $this->getEntityManager();
        $qb= $em
        ->createQuery('SELECT count(n) FROM APP\ENTITY\User n');
        return $qb->getSingleScalarResult();
    }
}
