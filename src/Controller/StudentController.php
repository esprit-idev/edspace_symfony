<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Classe;
use App\Entity\Message;
use App\Form\StudentType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Knp\Component\Pager\PaginatorInterfaces ;

class StudentController extends Controller
{
    /**
     * @Route("/student", name="app_student")
     */
    public function index(): Response
    {
        return $this->render('student/index.html.twig', [
            'controller_name' => 'StudentController',
        ]);
    }
    /**
     * @param UserRepository $repository
     * @return \Symfony\component\httpFoundation\Response
     * @Route("/AfficheE",name="affiche")
     */
    public function Affiche(UserRepository $repository  , Request $request){
        $hasAccessAgent = $this->isGranted('ROLE_ADMIN');
        $hasAccessStudent = $this->isGranted('ROLE_STUDENT');

        $rep=$this->getDoctrine()->getRepository(User::class);
       // $etudiant=$repository->findAll();
        $alletudiant=$repository->findByRole('ROLE_STUDENT');
        //$active=$etudiant->getIsBanned();
        $etudiant=$this->get('knp_paginator')->paginate(
        //$etudiant = $paginator->paginate(
        // Doctrine Query, not results
            $alletudiant,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            3
        );
        if ($hasAccessAgent){
            return $this->render ('student/afficheBack.html.twig',['etudiant'=>$etudiant]);
        }
        elseif ($hasAccessStudent) {
            $test=$this->getUser()->getId();
        $em=$this->getDoctrine()->getManager();
        $user1=$em->getRepository(User::class)->find($test);
        $em1=$this->getDoctrine()->getRepository(User::class);
        $memebers=$em1->findBy(['classe'=> $user1->getClasse()->getId()]);
        $classe=$em->getRepository(Classe::class)->find($user1->getClasse()->getId());

        $message=$this
            ->getDoctrine()
            ->getManager()
            ->getRepository(Message::class)
            ->findBy(array(),array('postDate' => 'ASC'));
        $mymsg=[];
        $othersmsg=[];
        foreach($message as $i){
            if($i->getUser()->getId()==$user1->getId()){
                $mymsg[]=$i;
            }
            else{
                $othersmsg[]=$i;
            }
        }
            return $this->render ('student/afficheFront.html.twig',['etudiant'=>$etudiant]);
        }}


    /**
     * @Route("/supp/{id}", name="delete")
     */
    public function Delete($id, UserRepository $repository)
    {  $hasAccessAgent = $this->isGranted('ROLE_ADMIN');
        $hasAccessStudent = $this->isGranted('ROLE_STUDENT');

        $etudiant=$repository->find($id);
        $em=$this->getDoctrine()->getManager();
        $em->remove($etudiant);
        $em->flush();
        if ($hasAccessAgent){
        return $this->redirectToRoute('affiche');}
        elseif ($hasAccessStudent) {
            return $this->render('/403.html.twig');}

    }

    /**
     *
     * @Route ("/add", name="ajout")
     */
    public function add(Request $request, UserPasswordEncoderInterface $encoder){
        $hasAccessAgent = $this->isGranted('ROLE_ADMIN');
        $hasAccessStudent = $this->isGranted('ROLE_STUDENT');

        $student=new User();
        $form=$this->createForm(StudentType::class,$student);
        //$form->add('Ajouter',SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $hash=$encoder->encodePassword($student ,$student->getPassword());
            $student->setPassword($hash);
            $student->setRoles(["ROLE_STUDENT"]);
           // $student->setActivationToken(md5(uniqid()));
            $em=$this->getDoctrine()->getManager();
            $em->persist($student);
            $em->flush();

            return $this->redirectToRoute('affiche');
        }
        if ($hasAccessAgent){
        return $this->render('student/add.html.twig',[
            'form'=>$form->createView()
        ]);}
        elseif ($hasAccessStudent) {
            return $this->render('/403.html.twig');}
    }
    /**
     * @Route("/update/{id}",name="update")
     */
    public function Update(UserRepository  $repository , $id,Request $request,UserPasswordEncoderInterface $encoder){
        $hasAccessAgent = $this->isGranted('ROLE_ADMIN');
        $hasAccessStudent = $this->isGranted('ROLE_STUDENT');

        $etudiant=$repository->find($id);
        $form=$this->createForm(StudentType::class,$etudiant);
       // $form->add('Update', SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $hash=$encoder->encodePassword($etudiant ,$etudiant->getPassword());
            $etudiant->setPassword($hash);
            $em=$this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute("affiche");
        }
        if ($hasAccessAgent){
        return $this->render('student/update.html.twig',[
            'form'=>$form->createView()
        ]);}
        elseif ($hasAccessStudent) {
            return $this->render('/403.html.twig');}
    }
    

}
