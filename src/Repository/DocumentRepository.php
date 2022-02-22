<?php

namespace App\Repository;

use App\Entity\Document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Document|null find($id, $lockMode = null, $lockVersion = null)
 * @method Document|null findOneBy(array $criteria, array $orderBy = null)
 * @method Document[]    findAll()
 * @method Document[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    // /**
    //  * @return Document[] Returns an array of Document objects
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
    public function findOneBySomeField($value): ?Document
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    /*function TriByNiveauMatiere($niveau,$matiere){
        return $this->createQueryBuilder('d')
            ->where('d.matiere like ?1')
            ->andWhere('d.niveau like ?2')
            ->setParameter('1',$matiere)
            ->setParameter('2',$niveau)
            ->getQuery()
            ->getResult();
    }*/

    function TriByNiveauMatiere($matiere){
        return $this->createQueryBuilder('d')
            ->join('d.matiere','m')
            ->addSelect('m')
            ->where('m.id =:idM')
            ->setParameter('idM',$matiere)
            ->getQuery()
            ->getResult();
    }

    function FindNiveaux(){
        $niveaux=array();
        $docIds= $this->createQueryBuilder('d')
            ->getQuery()
            ->getResult();
        foreach ($docIds as $item){
            array_push($niveaux,$item->getNiveau());
        }
        $niveaux=array_unique($niveaux);
        return $niveaux;
    }

    function FindMatieres($niveau){
        $matieres=array();
        $docIds= $this->createQueryBuilder('d')
            ->where('d.niveau =:idN')
            ->setParameter('idN',$niveau)
            ->distinct()
            ->getQuery()
            ->getResult();
        foreach ($docIds as $item){
            array_push($matieres,$item->getMatiere());
        }
        $matieres=array_unique($matieres);
        return $matieres;
    }
}
