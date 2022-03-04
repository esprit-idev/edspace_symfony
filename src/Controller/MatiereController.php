<?php

namespace App\Controller;

use App\Entity\Matiere;
use App\Form\MatiereType;
use App\Repository\MatiereRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MatiereController extends AbstractController
{
    /**
     * @Route("/matiere", name="matiere")
     */
    public function index(): Response
    {
        return $this->render('matiere/index.html.twig', [
            'controller_name' => 'MatiereController',
        ]);
    }

    /**
     * @param $id
     * @param MatiereRepository $repository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route ("/matiere/suppMatiere/{id}",name="suppMatiere")
     */
    function SuppMatiere($id,MatiereRepository $repository){
        $matiere=$repository->find($id);
        $em=$this->getDoctrine()->getManager();
        $em->remove($matiere);
        $em->flush();
        return $this->redirectToRoute('ajoutMatiere');
    }

    /**
     * @param Request $request
     * @return Response
     * @Route ("/matiere/ajoutMatiere",name="ajoutMatiere")
     */
    function AjoutMatiere(Request $request,MatiereRepository $repository){
        $matieres=$repository->findAll();

        $matiere=new Matiere();
        $form=$this->createForm(MatiereType::class,$matiere);
        $form->add("Ajouter",SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em=$this->getDoctrine()->getManager();
            $em->persist($matiere);
            $em->flush();
            return $this->redirectToRoute('ajoutMatiere');
        }
        return $this->render("matiere/ajoutMatiere.html.twig",["f"=>$form->createView(),'matieres'=>$matieres]);
    }

    /**
     * @Route ("/matiere/modifMatiere/{id}",name="modifMatiere")
     */
    function ModifMatiere($id,MatiereRepository $repository,Request $request){
        $matiere=$repository->find($id);
        $form=$this->createForm(MatiereType::class,$matiere);
        $form->add("Modifier",SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em=$this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute('ajoutMatiere');
        }
        return $this->render("matiere/modifMatiere.html.twig",["f"=>$form->createView()]);
    }

}
