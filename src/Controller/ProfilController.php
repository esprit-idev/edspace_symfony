<?php

namespace App\Controller;

use App\Form\EditProfilType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends AbstractController
{
    /**
     * @Route("/profil", name="app_profil")
     */
    public function index(): Response
    {
        return $this->render('profil/index.html.twig', [
            'controller_name' => 'ProfilController',
        ]);
    }
    /**
     * @Route ("/profil/show", name="show")
     */
    public function show()
    {

        return $this->render('profil/show.html.twig');
    }

    /**
     * @Route ("/profil/edit",name="ProfilEdit")
     */
    public function editProfile(Request $request){
        //$user= new User();
        $user=$this->getUser();
        $form=$this->createForm(EditProfilType::class , $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em=$this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->addFlash('message','Profil mis a jour');
            return $this->redirectToRoute('show');
        }
        return $this->render('profil/edit.html.twig',[
            'form'=>$form->createView()
        ]);
    }
}
