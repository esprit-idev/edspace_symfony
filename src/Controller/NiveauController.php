<?php

namespace App\Controller;

use App\Entity\Niveau;
use App\Entity\Classe;
use App\Entity\User;
use App\Form\NiveauType;
use App\Repository\NiveauRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NiveauController extends AbstractController
{
    /**
     * @Route("/niveau", name="niveau")
     */
    public function index(): Response
    {
        return $this->render('niveau/index.html.twig', [
            'controller_name' => 'NiveauController',
        ]);
    }

    /**
     * @param $id
     * @param NiveauRepository $repository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route ("/niveau/suppNiveau/{id}",name="suppNiveau")
     */
    function SuppNiveau($id, NiveauRepository $repository){

        $em2=$this->getDoctrine()->getManager();
        $classes=$em2->getRepository(Classe::class)->findBy(['niveau'=> $id]);
        foreach($classes as $i){
            $em3=$this->getDoctrine()->getManager();
        $users=$em3->getRepository(User::class)->findBy(['classe'=> $i->getId()]);
        foreach($users as $user){
            $user->setClasse(NULL);
            $em3->flush($user);
            }
            
        }

        $niveau=$repository->find($id);
        $em=$this->getDoctrine()->getManager();
        $em->remove($niveau);
        $em->flush();
        return $this->redirectToRoute('ajoutNiveau');
    }

    /**
     * @param Request $request
     * @param NiveauRepository $repository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Route ("/niveau/ajoutNiveau",name="ajoutNiveau")
     */
    function AjoutNiveau(Request $request,NiveauRepository $repository){
        $niveaux=$repository->findAll();
        $niveau= new Niveau();
        $form=$this->createForm(NiveauType::class,$niveau);
        $form->add('Ajouter',SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em=$this->getDoctrine()->getManager();
            $em->persist($niveau);
            $em->flush();
            return $this->redirectToRoute('ajoutNiveau');
        }
        return $this->render("niveau/ajoutNiveau.html.twig",['f'=>$form->createView(),'niveaux'=>$niveaux]);
    }

    /**
     * @param NiveauRepository $repository
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Route ("/niveau/modifNiveau/{id}",name="modifNiveau")
     */
    function ModifNiveau(NiveauRepository $repository,$id, Request $request){
        $niveau=$repository->find($id);
        $form=$this->createForm(NiveauType::class,$niveau);
        $form->add("Enregistrer",SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em=$this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute('ajoutNiveau');
        }
        return $this->render("niveau/modifNiveau.html.twig",['f'=>$form->createView()]);
    }
}
