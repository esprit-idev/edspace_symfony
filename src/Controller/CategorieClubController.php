<?php

namespace App\Controller;

use App\Entity\CategorieClub;
use App\Form\CategorieClubType;
use App\Repository\CategorieClubRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategorieClubController extends AbstractController
{

    /**
     * @Route("/displayClubCategories", name="displayClubCategories")
     */
    public function displayClubCategories(CategorieClubRepository $rep): Response
    {
        $hasAccessAgent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessAgent) {
            $categorie = $rep->findAll();
            return $this->render('categorie_club/displayClubCategories.html.twig', [
                'categorie' => $categorie,
            ]);
        }
        else return new Response(null, 403);

    }
    /**
     * @Route("/deleteClubCategories/{id}", name="deleteClubCategories")
     */
    public function deleteClubCategories($id,CategorieClubRepository $rep): Response
    {
        $hasAccessAgent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessAgent){
        $categorie = $rep->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($categorie);
        $entityManager->flush();
        return $this->redirectToRoute('displayClubCategories');
        }
        else return new Response(null, 403);

    }
    /**
     * @Route("/updateClubCategories/{id}", name="updateClubCategories")
     */
    public function updateClubCategories($id,Request $request,CategorieClubRepository $rep): Response
    {
        $hasAccessAgent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessAgent){
        $categorie = $rep->find($id);
        $form=$this->createForm(CategorieClubType::class,$categorie);
        $form->add('Valider', SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute('displayClubCategories');

        }
        return $this->render('categorie_club/updateClubCategories.html.twig', [
            'formCategorie' => $form->createView()
        ]);
        }
        else return new Response(null, 403);
    }

    /**
     * @Route("/addClubCategories", name="addClubCategories")
     */
    public function addClubCategories(Request $request,CategorieClubRepository $rep): Response
    {
        $hasAccessAgent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessAgent){
        $categorie = new CategorieClub();
        $form = $this->createForm(CategorieClubType::class, $categorie);
        $form->add('Ajouter', SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($categorie);
            $em->flush();
            return $this->redirectToRoute('displayClubCategories');

        }
        return $this->render('categorie_club/addClubCategories.html.twig', [
            'formCategorie' => $form->createView()
        ]);
        }
        else return new Response(null, 403);
    }
}