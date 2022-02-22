<?php

namespace App\Controller;

use App\Entity\Club;
use App\Form\ClubType;
use App\Repository\ClubRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
    public function displayClub(ClubRepository $rep): Response
    {
        $club = $rep->findAll();
        if ($this->admin) {
            return $this->render('club/displayClub(admin).html.twig', [
                'clubs' => $club,
            ]);
        } else return $this->render('club/displayClub(student).html.twig', [
            'clubs' => $club,
        ]);

    }

    /**
     * @Route("/deleteClub/{id}", name="deleteClub")
     */
    public function deleteClub($id, ClubRepository $rep): Response
    {
        $club = $rep->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($club);
        $entityManager->flush();
        return $this->redirectToRoute('displayClub');
    }

    /**
     * @Route("/updateClub/{id}", name="updateClub")
     */
    public function updateClub(\Symfony\Component\HttpFoundation\Request $request, $id, ClubRepository $rep): Response
    {


        $club = $rep->find($id);

        $form = $this->createForm(ClubType::class, $club);
        $form->add('Update', SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute('displayClub');

        }
        if ($this->admin) {
        return $this->render('club/updateClub.html.twig', [
            'formClub' => $form->createView(), 'nomClub' => $club->getClubNom()
        ]);}
        else {
            $descClub=$request->get('descClub');
            $club->setClubDescription("$descClub");
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute('displayPubClub', [
            'clubs' => $club,'id' => $id
        ]);}
    }

    /**
     * @Route("/addClub", name="addClub")
     */
    public function addClub(\Symfony\Component\HttpFoundation\Request $request,UserRepository $userRepository): Response
    {
        $club = new Club();
        $form = $this->createForm(ClubType::class, $club);
        $form->add('Ajouter', SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($club);
            $em->flush();
            return $this->redirectToRoute('displayClub');
        }
        return $this->render('club/addClub.html.twig', [
            'formClub' => $form->createView()
        ]);
    }
}
