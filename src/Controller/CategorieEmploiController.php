<?php

namespace App\Controller;

use App\Entity\CategorieEmploi;
use App\Form\CategorieEmploiFormType;
use App\Repository\CategorieEmploiRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CategorieEmploiController extends AbstractController
{
    /**
     * @Route("/categorie/emploi", name="categorie_emploi")
     */
    public function index(): Response
    {
        return $this->render('categorie_emploi/index.html.twig', [
            'controller_name' => 'CategorieEmploiController',
        ]);
    }

     /**
     * @Route("/allcategoriesemploi", name="allCategoriesEmploi")
     */
    public function allCategories(CategorieEmploiRepository $repo): Response
    {
        $hasAccessStudent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessStudent){
        $categories = $repo->findAll();
        }else{
            return $this->render('/403.html.twig');
        }
        return $this->render('categorie_emploi/allCategories.html.twig', [
            'controller_name' => 'CategorieEmploiController',
            'categories' => $categories,
        ]);
    }

    # add categorie

    /**
     * @Route("/addcategorieemploi", name="addCategoryEmploi")
     */
    public function AddCategory(Request $request, CategorieEmploiRepository $repo): Response
    {
        $hasAccessStudent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessStudent){
            $categories = $repo->findAll();
            $category = new CategorieEmploi();
            $form = $this->createForm(CategorieEmploiFormType::class,$category);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($category);
                $entityManager->flush();
                return $this->redirectToRoute('allCategoriesEmploi');
            }
        }else{
            return $this->render('/403.html.twig');
        }
        return $this->render('categorie_emploi/addCategory.html.twig', [
            'form_title' => 'Ajouter une categorie de Emploi',
            'form' => $form->createView(),
            'categories' => $categories,

        ]);
    }

    # update categorie

    /**
     * @Route("/updateCategoryemploi/{id}", name="updateCategoryEmploi")
     */
    public function UpdateCategory(Request $request, $id, CategorieEmploiRepository $repo): Response
    {
        $hasAccessStudent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessStudent){
            $entityManager = $this->getDoctrine()->getManager();
            $category = $repo->find($id);
            $form = $this->createForm(CategorieEmploiFormType::class, $category);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $entityManager->flush();
                return $this->redirectToRoute('allCategoriesEmploi');
            }
        }else{
            return $this->render('/403.html.twig');
        }
        return $this->render('categorie_emploi/updateCategory.html.twig', [
            'form_title' => 'Modifier une categorie',
            'form_add' => $form->createView(),
        ]);
    }

    #delete cat

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/deleteCategoryEmploi/{id}", name="deleteCategoryEmploi")
     */
    public function DeleteCategory($id, CategorieEmploiRepository $repo): Response
    {
        $hasAccessStudent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessStudent){
        $entityManager = $this->getDoctrine()->getManager();
        $category = $repo->find($id);
        $entityManager->remove($category);
        $entityManager->flush();

        return $this->redirectToRoute('allCategoriesEmploi');
        }else{
            return $this->render('/403.html.twig');
        }
    }
    /**
     * @Route("/allcategoriesemploi/search", name="SearchCatNameEmploi")
     */
    public function SearchCat(CategorieEmploiRepository $repo, Request $request): Response
    {
        $hasAccessStudent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessStudent){
        $categories = $repo->findAll();
        if($request->isMethod('POST')){
            $categoryName = $request->get('catTitle');
            if($categoryName !== ''){
                $categories = $repo->SearchByName($categoryName);
            }
        }
        }else{
            return new Response("Not authorized", 403);
        }
        return $this->render('categorie_emploi/allCategories.html.twig',
            array('categories' => $categories,));
    }
        //Json methods

    /**
     * @param NormalizerInterface $normalizer
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route("/allcategoriesEmploiJSON", name="allCategoriesEmploiJSON")
     */
    public function allcategoriesJSON(CategorieEmploiRepository $repository, NormalizerInterface $normalizer): Response
    {
        $categories = $repository->findAll();
        $jsonContent = $normalizer->normalize($categories,'json',['groups'=>['categoriesEmploi','offre']]);
        return new Response(json_encode($jsonContent));
    }
}
