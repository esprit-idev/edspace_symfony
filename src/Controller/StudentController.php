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
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

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
            4
        );
       
            return $this->render ('student/afficheBack.html.twig',['etudiant'=>$etudiant]);
        }


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
       // if ($hasAccessAgent){
        return $this->render('student/add.html.twig',[
            'form'=>$form->createView()
        ]);
       // }
       // elseif ($hasAccessStudent) {
          //  return $this->render('/403.html.twig');}
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


    /**
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route ("/addStudentJSON",name="addStudentJSON")
     */
    public function addStudentJSON(NormalizerInterface $normalizer, Request $request):Response
    {
        $em = $this->getDoctrine()->getManager();
        $student = new User();
        $student->setUsername($request->get('username'));
        $student->setPrenom($request->get('prenom'));
        $student->setEmail($request->get('email'));
        $student->setPassword($request->get('password'));
        $student->setIsBanned($request->get('isBanned'));
        $student->setRoles(["ROLE_STUDENT"]);
       // $student->setImage($request->get('image'));

        $em->persist($student);
        $em->flush();

        $jsonContent = $normalizer->normalize($student,'json',['groups'=>'post:read']);
        return new Response("added successfully".json_encode($jsonContent));
    }

    /**
     * @param UserRepository $repository
     * @return \Symfony\component\httpFoundation\Response
     * @Route("/afficheStudent",name="afficheStudent")
     */
    public function AfficheJson(UserRepository $repository  , Request $request , NormalizerInterface $normalizer){

        $rep=$this->getDoctrine()->getRepository(User::class);
        $etudiant=$repository->findByRole('ROLE_STUDENT');
       $jsonContent=$normalizer->normalize($etudiant, 'json', ['groups'=>'students']);
       return new Response(json_encode($jsonContent));
        }

    /**
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route ("/updateStudentJSON/{id}",name="updateStudentJSON")
     */
    public function updateStudentJSON(UserRepository $repository, NormalizerInterface $normalizer, Request $request,$id):Response
    {
        $em = $this->getDoctrine()->getManager();
        $student = $repository->find($id);
        $student->setUsername($request->get('username'));
        $student->setPrenom($request->get('prenom'));
        $student->setEmail($request->get('email'));
        $student->setPassword($request->get('password'));
        $student->setIsBanned($request->get('isBanned'));
        $em->flush();

        $jsonContent = $normalizer->normalize($student,'json',['groups'=>'post:read']);
        return new Response("modified successfully".json_encode($jsonContent));
    }


    /**
     * @param UserRepository $repository
     * @return \Symfony\component\httpFoundation\Response
     * @Route("/deleteStudent/{id}",name="deleteStudent")
     */
    function deleteStudentJson(NormalizerInterface $normalizer,$id): Response
    {
        $em=$this->getDoctrine()->getManager();
        $user=$em->getRepository(User::class)->find($id);
        $em->remove($user);
        $em->flush();
        $jsonContent=$normalizer->normalize($user,'json',['groups'=>'post:read']);
        return new Response("Student deleted successfully".json_encode($jsonContent));
    }

}
