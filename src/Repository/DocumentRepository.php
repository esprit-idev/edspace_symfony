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

    public function IncrementCountSignal(Document $document): void
    {
        $this
            ->createQueryBuilder('s')
            ->update()
            ->set('s.signalements', 's.signalements + 1')
            ->where('s.id = :id')
            ->setParameter('id', $document->getId())
            ->getQuery()
            ->execute();
    }

    public function DecrementCountSignal(Document $document): void
    {
        $this
            ->createQueryBuilder('s')
            ->update()
            ->set('s.signalements', 0)
            ->where('s.id =:id')
            ->setParameter('id', $document->getId())
            ->getQuery()
            ->execute();
    }

    function FindDocSignales(){
        $documents=array();
        $document= $this->createQueryBuilder('d')
            ->where('d.signalements>0')
            ->getQuery()
            ->getResult();
        foreach ($document as $item){
            array_push($documents,$item);
        }
        return $documents;
    }

    function FindDocByType($prop,$docType){
        $documents=array();
        if($docType=="tous"){
            $document= $this->createQueryBuilder('d')
                ->where('d.proprietaire =:prop')
                ->setParameter('prop',$prop)
                ->getQuery()
                ->getResult();
        }
        elseif($docType=="url"){
            $document= $this->createQueryBuilder('d')
                ->where('d.proprietaire =:prop')
                ->setParameter('prop',$prop)
                ->andwhere('d.url IS NOT NULL')
                ->getQuery()
                ->getResult();
        }else{
            $document= $this->createQueryBuilder('d')
                ->where('d.proprietaire =:prop')
                ->setParameter('prop',$prop)
                ->andwhere('d.type like :type')
                ->setParameter('type','%'.$docType.'%')
                ->getQuery()
                ->getResult();
        }
        foreach ($document as $item){
            array_push($documents,$item);
        }
        return $documents;
    }
    public function CountDocuments(){
        $em = $this->getEntityManager();
        $qb= $em
        ->createQuery('SELECT count(n) FROM APP\ENTITY\Document n');
        return $qb->getSingleScalarResult();
    }
}
