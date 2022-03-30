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
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

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
        $hasAccessAgent = $this->isGranted('ROLE_ADMIN');
        $hasAccessStudent = $this->isGranted('ROLE_STUDENT');
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
        if ($hasAccessAgent){
        return $this->render ('Admin/Affiche.html.twig',['admin'=>$admin]);}
        elseif ($hasAccessStudent) {
            return $this->render('/403.html.twig');}
    }
    /**
     * @Route("/suppA/{id}", name="deleteA")
     */
    public function Delete($id, UserRepository $repository)
    { $hasAccessAgent = $this->isGranted('ROLE_ADMIN');
        $hasAccessStudent = $this->isGranted('ROLE_STUDENT');
       // $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $admin=$repository->find($id);
        $em=$this->getDoctrine()->getManager();
        $em->remove($admin);
        $em->flush();
        if ($hasAccessAgent){
        return $this->redirectToRoute('afficheA');}
        elseif ($hasAccessStudent) {
            return $this->render('/403.html.twig');}
    }

    /**

     * @Route ("/admin/add", name="ajoutA")
     */
    public function add(Request $request, UserPasswordEncoderInterface $encoder){
        $hasAccessAgent = $this->isGranted('ROLE_ADMIN');
        $hasAccessStudent = $this->isGranted('ROLE_STUDENT');
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
        if ($hasAccessAgent){
        return $this->render('admin/add.html.twig',[
            'form'=>$form->createView()
        ]);}
        elseif ($hasAccessStudent) {
            return $this->render('/403.html.twig');}
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

    /**
     * @param UserRepository $repository
     * @return \Symfony\component\httpFoundation\Response
     * @Route("/afficheAdmin",name="afficheAdmin")
     */
    public function AfficheAdminJson(UserRepository $repository  , Request $request , NormalizerInterface $normalizer){

        $rep=$this->getDoctrine()->getRepository(User::class);
        $admin=$repository->findByRole('ROLE_ADMIN');
        $jsonContent=$normalizer->normalize($admin, 'json', ['groups'=>'students']);
        return new Response(json_encode($jsonContent));
    }


    /**
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route ("/addAdminJSON",name="addAdminJSON")
     */
    public function addAdminJSON(NormalizerInterface $normalizer, Request $request):Response
    {
        $em = $this->getDoctrine()->getManager();
        $admin = new User();
        $admin->setUsername($request->get('username'));
        $admin->setPrenom($request->get('prenom'));
        $admin->setEmail($request->get('email'));
        $admin->setPassword($request->get('password'));
       // $admin->setIsBanned($request->get('isBanned'));
        $admin->setRoles(["ROLE_ADMIN"]);

        $em->persist($admin);
        $em->flush();

        $jsonContent = $normalizer->normalize($admin,'json',['groups'=>'post:read']);
        return new Response("added successfully".json_encode($jsonContent));
    }

    /**
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route ("/updateAdminJSON{id}",name="updateAdminJSON")
     */
    public function updateAdminJSON(UserRepository $repository, NormalizerInterface $normalizer, Request $request,$id):Response
    {
        $em = $this->getDoctrine()->getManager();
        $admin = $repository->find($id);
        $admin->setUsername($request->get('username'));
        $admin->setPrenom($request->get('prenom'));
        $admin->setEmail($request->get('email'));
        $admin->setPassword($request->get('password'));
        $em->flush();

        $jsonContent = $normalizer->normalize($admin,'json',['groups'=>'post:read']);
        return new Response("modified successfully".json_encode($jsonContent));
    }



    /**
     * @param UserRepository $repository
     * @return \Symfony\component\httpFoundation\Response
     * @Route("/deleteAdminJson/{id}",name="deleteStudent")
     */
    function deleteAdminJson(NormalizerInterface $normalizer,$id): Response
    {
        $em=$this->getDoctrine()->getManager();
        $user=$em->getRepository(User::class)->find($id);
        $em->remove($user);
        $em->flush();
        $jsonContent=$normalizer->normalize($user,'json',['groups'=>'post:read']);
        return new Response("Admin deleted successfully".json_encode($jsonContent));
    }

}
