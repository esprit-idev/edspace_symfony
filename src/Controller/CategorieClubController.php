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
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
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
		
	  /**
     * @Route("/AllClubCategories", name="AllClubCategories")
     */
    public function AllClubCategories(NormalizerInterface $normalizer, CategorieClubRepository $rep): Response
    {
        $categories = $rep->findAll();
        $jsonContent = $normalizer->normalize($categories, 'json', ['groups' => 'post:read']);
        return new Response(json_encode($jsonContent));
    }
	
	 /**
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route ("/addClubCategorie/new",name="addClubCategorie")
     */
    public function addClubCategorie(NormalizerInterface $normalizer, Request $request,CategorieClubRepository $rep):Response
    {
        $em = $this->getDoctrine()->getManager();
        $categ = new CategorieClub();
        $categ->setCategorieNom($request->get('categorieNom'));
        $em->persist($categ);
        $em->flush();
        $jsonContent = $normalizer->normalize($categ,'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }
	
	    /**
     * @param NormalizerInterface $normalizer
     * @param $id
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route ("/deleteClubCategorie/{id}",name="deleteClubCategorie")
     */
    function deleteClubCategorie(NormalizerInterface $normalizer,$id): Response
    {
        $em=$this->getDoctrine()->getManager();
        $categ=$em->getRepository(CategorieClub::class)->find($id);
        $em->remove($categ);
        $em->flush();
        $jsonContent=$normalizer->normalize($categ,'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }
	
	    /**
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route ("/updateClubCategorie/{id}",name="updateClubCategorie")
     */
    public function updateClubCategorie(CategorieClubRepository $rep, NormalizerInterface $normalizer, Request $request,$id):Response
    {
        $em = $this->getDoctrine()->getManager();
        $categ = $rep->find($id);
        $categ->setCategorieNom($request->get('categorieNom'));
        $em->flush();

        $jsonContent = $normalizer->normalize($categ,'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }
}