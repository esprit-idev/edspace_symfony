<?php

namespace App\Controller;

use App\Entity\Classe;
use App\Entity\Niveau;
use App\Entity\User;
use App\Form\ClasseType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Repository\ClasseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClasseController extends AbstractController
{
    /**
     * @Route("/classe", name="Classe")
     * @param Request $request
     */
    public function index(Request $request): Response
    {

            
        $em1=$this->getDoctrine()->getRepository(Niveau::class);
        $niveau=$em1->findAll(Niveau::class);

        if($request->request->count() > 0){
            
            $em2=$this->getDoctrine()->getManager();
            $classe=$em2->getRepository(Classe::class)->find($request->request->get('id'));
            $classe->setClasse($request->request->get('classe'))
                ->setNiveau($em1->findOneBy(['id'=> $request->request->get('niveau')]));
                $em2->flush($classe);
                

        }

        
       

        $em=$this->getDoctrine()->getRepository(Classe::class);
        $classes=$em->findAll(Classe::class);



        return $this->render('classe/classes.html.twig', [
            'classes' => $classes,
            'niveau'=> $niveau,
        ]);
    }




    /**
     * @Route ("/suppclaase/{id}",name="suppClasse")
     */

    public function suppClasse($id): Response
    {
        $em2=$this->getDoctrine()->getManager();
        $classe=$em2->getRepository(Classe::class)->find($id);
        $em=$this->getDoctrine()->getManager();
        $user=$em->getRepository(User::class)->findBy(['classe'=> $id]);
        foreach($user as $i){
        $i->setClasse(NULL);
        $em->flush($i);
        }
        
        $em2->remove($classe);
        $em2->flush();

        return $this->redirectToRoute('Classe');
    }


     /**
     * @Route ("/addclaase",name="addClasse")
     * @param Request $request
     */

    public function addClasse(Request $request): Response
    {
        if($request->request->count() > 0){
            $em1=$this->getDoctrine()->getRepository(Niveau::class);

            $em2=$this->getDoctrine()->getManager();
            $classe=new Classe();
            $classe->setClasse($request->request->get('classe'))
            ->setNiveau($em1->findOneBy(['id'=> $request->request->get('niveau')]));
            $em2->persist($classe);
            $em2->flush();
                

        }
        return $this->redirectToRoute('Classe');
    }



    //l'ajout de l'etudiant
     /**
     * @Route ("/classe/{id}",name="classtoetudiant")
     * 
     * @param Request $request
     */

    public function addEt($id,Request $request): Response
    {

        $em2=$this->getDoctrine()->getRepository(Classe::class);
        $classe=$em2->find($id);
        
        if($request->request->count() > 0){
            
            $em=$this->getDoctrine()->getManager();
            $user=$em->getRepository(User::class)->find($request->request->get('Etudiant'));
            $user->setClasse($em2->findOneBy(['id'=> $id]));
                $em->flush($user);
                
        }

        $em1=$this->getDoctrine()->getRepository(User::class);
        $etudiant=$em1->findBy(['classe'=> $id]);



        $etudiants=$em1->findAll();
         
        $et=[];
        if(count($etudiants)>0){
        foreach($etudiants as $i){
            if(is_null($i->getClasse())){
                $et[]=$i;
            }
            else{
            if($i->getClasse()->getId() != $id){
                $et[]=$i;
            }}
        }}



        return $this->render('classe/classe.html.twig', [
            'classe'=>$classe,
            'etudiant' => $etudiant,
            'etudiants' => $et,
         
           
        ]);
    }




}
