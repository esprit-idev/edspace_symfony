<?php

namespace App\Controller;
use App\Entity\User;
use App\Form\RegistrationType;
use App\Form\StudentType;
use App\Repository\UserRepository;
use Doctrine\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class StudentController extends AbstractController
{
    /**
     * @Route("/student", name="student")
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
    public function Affiche(UserRepository $repository){
        $rep=$this->getDoctrine()->getRepository(User::class);
        //$etudiant=$repository->findAll();
        $etudiant=$repository->findByRole('ROLE_STUDENT');
        return $this->render ('Student/Affiche.html.twig',['etudiant'=>$etudiant]);
    }
    /**
     * @Route("/supp/{id}", name="delete")
     */
    public function Delete($id, UserRepository $repository)
    { $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $etudiant=$repository->find($id);
        $em=$this->getDoctrine()->getManager();
        $em->remove($etudiant);
        $em->flush();
        return $this->redirectToRoute('affiche');
    }

    /**
     *
     * @Route ("/add", name="ajout")
     */
    public function add(Request $request, UserPasswordEncoderInterface $encoder){
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $student=new User();
        $form=$this->createForm(StudentType::class,$student);
        //$form->add('Ajouter',SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
           $hash=$encoder->encodePassword($student ,$student->getPassword());
            $student->setPassword($hash);
            $student->setActivationToken(md5(uniqid()));
            $em=$this->getDoctrine()->getManager();
            $em->persist($student);
            $em->flush();
            //return $this->redirectToRoute('affiche');
        }
        return $this->render('student/add.html.twig',[
            'form'=>$form->createView()
        ]);
    }
    /**
     * @Route("/update/{id}",name="update")
     */
    public function Update(UserRepository  $repository , $id,Request $request){
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $etudiant=$repository->find($id);
        $form=$this->createForm(StudentType::class,$etudiant);
        $form->add('Update', SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em=$this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute("affiche");
        }
        return $this->render('student/update.html.twig',[
            'form'=>$form->createView()
        ]);
    }
}
