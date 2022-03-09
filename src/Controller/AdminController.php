<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AdminType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Knp\Component\Pager\PaginatorInterfaces ;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="app_admin")
     */
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
    /**
     * @param UserRepository $repository
     * @return \Symfony\component\httpFoundation\Response
     * @Route("/AfficheA",name="afficheA")
     */
    public function Affiche(UserRepository $repository , Request $request){
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $rep=$this->getDoctrine()->getRepository(User::class);
         //$admin=$repository->findAll();
       $admin=$repository->findByRole('ROLE_ADMIN');
        //$admin=$this->get('knp_paginator')->paginate(
        // Doctrine Query, not results
            //$alladmin,
            // Define the page parameter
           // $request->query->getInt('page', 1),
            // Items per page
           // 3
      //  );
        return $this->render ('Admin/Affiche.html.twig',['admin'=>$admin]);
    }
    /**
     * @Route("/suppA/{id}", name="deleteA")
     */
    public function Delete($id, UserRepository $repository)
    { $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $admin=$repository->find($id);
        $em=$this->getDoctrine()->getManager();
        $em->remove($admin);
        $em->flush();
        return $this->redirectToRoute('afficheA');
    }

    /**

     * @Route ("/admin/add", name="ajoutA")
     */
    public function add(Request $request, UserPasswordEncoderInterface $encoder){
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $admin=new User();
        $form=$this->createForm(AdminType::class,$admin);
        $form->add('Ajouter',SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $hash=$encoder->encodePassword($admin ,$admin->getPassword());
            $admin->setPassword($hash);
            $admin->setRoles(["ROLE_ADMIN"]);
            $em=$this->getDoctrine()->getManager();
            $em->persist($admin);
            $em->flush();
            return $this->redirectToRoute('afficheA');
        }
        return $this->render('admin/add.html.twig',[
            'form'=>$form->createView()
        ]);
    }
    /**
     * @Route("admin/update/{id}",name="updateA")
     */
    public function Update(UserRepository $repository , $id,Request $request, UserPasswordEncoderInterface $encoder){
       //$this->denyAccessUnlessGranted('ROLE_ADMIN');
        $admin=$repository->find($id);
        $form=$this->createForm(AdminType::class,$admin);
        $form->add('Update', SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $hash=$encoder->encodePassword($admin ,$admin->getPassword());
            $admin->setPassword($hash);
            $em=$this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute("afficheA");
        }
        return $this->render('admin/update.html.twig',[
            'form'=>$form->createView()
        ]);
    }
}
