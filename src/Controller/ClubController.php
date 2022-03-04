<?php

namespace App\Controller;

use App\Entity\Club;
use App\Entity\User;
use App\Form\ChangeClubPictureType;
use App\Form\ClubDescription;
use App\Form\ClubType;
use App\Repository\CategorieClubRepository;
use App\Repository\ClubPubRepository;
use App\Repository\ClubRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClubController extends AbstractController
{
    private $admin = 1;

    /**
     * @Route("/club", name="club")
     */
    public function index(): Response
    {
        return $this->render('club/index.html.twig', [
            'controller_name' => 'ClubController',
        ]);
    }


    /**
     * @Route("/displayClub", name="displayClub")
     */
    public function displayClub(Request $request,ClubRepository $rep,ClubPubRepository $repository,CategorieClubRepository $categorieClubRepository): Response
    {
        $categories=$categorieClubRepository->findAll();
        if($request->get('catego')!=null){
            $categorieSelected=$request->get('catego');
            $ids=$categorieClubRepository->findBy(array('categorieNom'=>$categorieSelected));

            $club=$rep->findBy(array('clubCategorie'=>$ids));
        }
        else{ $club = $rep->findAll();
    }
        if ($this->admin) {
            return $this->render('club/displayClub(admin).html.twig', [
                'clubs' => $club,'categories'=>$categories
            ]);
        } else return $this->render('club/displayClub(student).html.twig', [
            'clubs' => $club,'categories'=>$categories
        ]);

    }

    /**
     * @Route("/deleteClub/{id}", name="deleteClub")
     */
    public function deleteClub($id, ClubRepository $rep ,UserRepository $userRepository): Response
    {
        $club = $rep->find($id);
        $club->getClubResponsable()->setRoles(['ROLE_RESPONSABLE']);
        $entityManager = $this->getDoctrine()->getManager();
        $respoClubid=$club->getClubResponsable();
        $user=$userRepository->find($respoClubid);
        $user->setClub(null);
        $user->setRoles([]);
        $entityManager->remove($club);
        $entityManager->flush();
        return $this->redirectToRoute('displayClub');
    }

    /**
     * @Route("/updateClub/{id}", name="updateClub")
     */
    public function updateClub(\Symfony\Component\HttpFoundation\Request $request, $id, ClubRepository $rep,UserRepository $userRepository): Response
    {


            $club = $rep->find($id);
            $emails=$userRepository->findEmails($id);
            $form = $this->createForm(ClubType::class, $club);
            $form->add('clubResponsable',EntityType::class, [
            'label' => 'Email du responsable ',
            'attr' => [
                'placeholder' => "ex@ex.com",
                'class' => 'name'
            ],
            'class' => User::class,
            'placeholder' => 'Choisissez unrespo',
            'query_builder' => $emails,
            'choice_label' => 'email',
        ]);
            $form->add('Update', SubmitType::class);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $d=$userRepository->findOneBy(array('club'=>$club));
                $d->setClub(null);
                $d->setRoles([]);
                $respoClubid=$form->get('clubResponsable')->getData();
                $userRepository->find($respoClubid)->setRoles(['ROLE_RESPONSABLE']); //add not set
                $user=$userRepository->find($respoClubid);
                $user->setClub($club);
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                return $this->redirectToRoute('displayClub');

            }
            return $this->render('club/updateClub.html.twig', [
                'formClub' => $form->createView(), 'nomClub' => $club->getClubNom()
            ]);

    }

    /**
     * @Route("/addClub", name="addClub")
     */
    public function addClub(\Symfony\Component\HttpFoundation\Request $request, UserRepository $userRepository): Response
    {
        $club = new Club();
        $form = $this->createForm(ClubType::class, $club);
        $form->add('clubResponsable',EntityType::class, [
            'label' => 'Email du responsable ',
            'attr' => [
                'placeholder' => "ex@ex.com",
                'class' => 'name'
            ],
            'class' => User::class,
            'placeholder' => 'Choisissez unrespo',
            'query_builder' => function(UserRepository $repository) {

                $qb = $repository->createQueryBuilder('u');

                return $qb
                    ->where('u.roles NOT LIKE :roles')
                    ->setParameter('roles','%"'.'ROLE_RESPONSABLE'.'"%');
                // find all users where 'role' is NOT '['ROLE_RESPONSABLE']'
            },
            'choice_label' => 'email',


        ]);
        $form->add('Ajouter', SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $respoClubid=$form->get('clubResponsable')->getData();
            $userRepository->find($respoClubid)->setRoles(['ROLE_RESPONSABLE']); //add not set
            $user=$userRepository->find($respoClubid);
            $user->setClub($club);
            $club->setClubPic("defaultProfilePicture.png");
            $em->persist($club);
            $em->flush();


            return $this->redirectToRoute('displayClub');
        }
        return $this->render('club/addClub.html.twig', [
            'formClub' => $form->createView()
        ]);
    }
}
