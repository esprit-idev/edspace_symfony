<?php

namespace App\Controller;

use App\Entity\Club;
use App\Entity\User;
use App\Entity\Message;
use App\Entity\Classe;
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
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ClubController extends AbstractController
{

    /**
     * @Route("/displayClub", name="displayClub")
     */
    public function displayClub(Request $request, ClubRepository $rep, ClubPubRepository $repository, CategorieClubRepository $categorieClubRepository): Response
    {
        $hasAccessAgent = $this->isGranted('ROLE_ADMIN');
        $hasAccessStudent = $this->isGranted('ROLE_STUDENT');
        $hasAccessResponsable = $this->isGranted('ROLE_RESPONSABLEC');

        /* messaging */
        if ($hasAccessStudent) {
            $em = $this->getDoctrine()->getManager();
            $user1 = $em->getRepository(User::class)->find($this->getUser()->getId());
            $em1 = $this->getDoctrine()->getRepository(User::class);
            $memebers = $em1->findBy(['classe' => $user1->getClasse()->getId()]);
            $classe = $em->getRepository(Classe::class)->find($user1->getClasse()->getId());

            $message = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository(Message::class)
                ->findBy(array(), array('postDate' => 'ASC'));
            $mymsg = [];
            $othersmsg = [];
            foreach ($message as $i) {
                if ($i->getUser()->getId() == $user1->getId()) {
                    $mymsg[] = $i;
                } else {
                    $othersmsg[] = $i;
                }
            }
        }


        $categories = $categorieClubRepository->findAll();
        if ($request->get('catego') != null) {
            $categorieSelected = $request->get('catego');
            $ids = $categorieClubRepository->findBy(array('categorieNom' => $categorieSelected));

            $club = $rep->findBy(array('clubCategorie' => $ids));
        } else {
            $club = $rep->findAll();
        }
        if ($hasAccessAgent) {
            return $this->render('club/displayClub(admin).html.twig', [
                'clubs' => $club, 'categories' => $categories
            ]);
        } elseif ($hasAccessStudent || $hasAccessResponsable) {
            return $this->render('club/displayClub(student).html.twig', [
                'clubs' => $club,
                'categories' => $categories,
                'memebers' => $memebers,
                'user' => $user1,
                'classe' => $classe,
                'message' => $message,
                'mymsg' => $mymsg,
                'others' => $othersmsg
            ]);
        } else return $this->render('/403.html.twig');

    }

    /**
     * @Route("/deleteClub/{id}", name="deleteClub")
     */
    public function deleteClub($id, ClubRepository $rep, UserRepository $userRepository): Response
    {
        $hasAccessAgent = $this->isGranted('ROLE_ADMIN');
        if ($hasAccessAgent) {
            $club = $rep->find($id);
            $entityManager = $this->getDoctrine()->getManager();
            $respoClubid = $club->getClubResponsable();
            $user = $userRepository->find($respoClubid);
            $user->setClub(null);
            $user->setRoles(["ROLE_STUDENT"]);
            $entityManager->remove($club);
            $entityManager->flush();
            return $this->redirectToRoute('displayClub');
        } else return $this->render('/403.html.twig');
    }

    /**
     * @Route("/updateClub/{id}", name="updateClub")
     */
    public function updateClub(\Symfony\Component\HttpFoundation\Request $request, $id, ClubRepository $rep, UserRepository $userRepository): Response
    {

        $hasAccessAgent = $this->isGranted('ROLE_ADMIN');
        if ($hasAccessAgent) {
            $club = $rep->find($id);
            $emails = $userRepository->findEmails($id);
            $form = $this->createForm(ClubType::class, $club);
            $form->add('clubResponsable', EntityType::class, [
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
                $d = $userRepository->findOneBy(array('club' => $club));
                $d->setClub(null);
                $d->setRoles(["ROLE_STUDENT"]);
                $respoClubid = $form->get('clubResponsable')->getData();
                $userRepository->find($respoClubid)->setRoles(["ROLE_STUDENT", "ROLE_RESPONSABLEC"]); //add not set
                $user = $userRepository->find($respoClubid);
                $user->setClub($club);
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                return $this->redirectToRoute('displayClub');

            }
            return $this->render('club/updateClub.html.twig', [
                'formClub' => $form->createView(), 'nomClub' => $club->getClubNom()
            ]);
        } else return $this->render('/403.html.twig');

    }

    /**
     * @Route("/addClub", name="addClub")
     */
    public function addClub(\Symfony\Component\HttpFoundation\Request $request, UserRepository $userRepository): Response
    {
        $hasAccessAgent = $this->isGranted('ROLE_ADMIN');
        if ($hasAccessAgent) {
            $club = new Club();
            $form = $this->createForm(ClubType::class, $club);
            $form->add('clubResponsable', EntityType::class, [
                'label' => 'Email du responsable ',
                'attr' => [
                    'placeholder' => "ex@ex.com",
                    'class' => 'name'
                ],
                'class' => User::class,
                'placeholder' => 'Choisissez unrespo',
                'query_builder' => function (UserRepository $repository) {

                    $qb = $repository->createQueryBuilder('u');

                    return $qb
                        ->where('u.roles NOT LIKE :roles')
                        ->setParameter('roles', '%"' . "ROLE_ADMIN" . '"%')
                        ->andwhere('u.club is NULL')
                        ->orderBy('u.email', 'ASC');

                    // find all users where 'role' is NOT '['ROLE_RESPONSABLE']'
                },
                'choice_label' => 'email',


            ]);
            $form->add('Ajouter', SubmitType::class);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $respoClubid = $form->get('clubResponsable')->getData();
                $userRepository->find($respoClubid)->setRoles(["ROLE_STUDENT", "ROLE_RESPONSABLEC"]); //add not set
                $user = $userRepository->find($respoClubid);
                $user->setClub($club);
                $club->setClubPic("defaultProfilePicture.png");
                $em->persist($club);
                $em->flush();


                return $this->redirectToRoute('displayClub');
            }
            return $this->render('club/addClub.html.twig', [
                'formClub' => $form->createView()
            ]);
        } else return $this->render('/403.html.twig');
    }

    /**
     * @Route("/allClubs", name="allClubs")
     */
    public function allClubs(NormalizerInterface $normalizer, ClubRepository $rep): Response
    {
        $clubs = $rep->findAll();
        $jsonContent = $normalizer->normalize($clubs, 'json', ['groups' => 'post:read']);
        return new Response(json_encode($jsonContent));
    }
    /**
     * @Route("/editClubJson/{clubId}", name="editClubJson")
     */
    public function editClubJson(Request $request,$clubId,NormalizerInterface $normalizer, ClubRepository $rep): Response
    {
        $em=$this->getDoctrine()->getManager();
        $club = $rep->find($clubId);
        $club->setClubDescription($request->get('clubDesc'));
        $em->flush();
        $jsonContent = $normalizer->normalize($club, 'json', ['groups' => 'post:read']);
        return new Response(json_encode($jsonContent));
    }
	
	 /**
     * @Route("/oneClub/{clubId}", name="oneClub")
     */
    public function oneClub($clubId,NormalizerInterface $normalizer, ClubRepository $rep): Response
    {
        $club = $rep->find($clubId);
        $jsonContent = $normalizer->normalize($club, 'json', ['groups' => 'post:read']);
        return new Response(json_encode($jsonContent));
    }
}