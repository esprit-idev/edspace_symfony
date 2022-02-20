<?php

namespace App\Controller;

use App\Entity\CategorieNews;
use App\Form\CategorieNewsFormType;
use App\Repository\CategorieNewsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class CategorieNewsController extends AbstractController
{
    /**
     * @Route("/categorienews", name="categoryNews")
     */
    public function index(): Response
    {
        return $this->render('categorie_news/index.html.twig', [
            'controller_name' => 'CategorieNewsController',
        ]);
    }


  /**
     * @Route("/allcategoriesnews", name="allCategoriesNews")
     */
    public function allCategories(CategorieNewsRepository $repo): Response
    {
        $categories = $repo->findAll();
        return $this->render('categorie_news/allCategories.html.twig', [
            'controller_name' => 'CategorieNewsController',
            'categories' => $categories,
        ]);
    }

    // #consulter 

    // /**
    //  * @param $id
    //  * @Route("/oneCategoryNews", name="oneCategoryNews")
    //  */
    // public function OneCategoryNews($id, CategorieNewsRepository $repo): Response
    // {
    //     $categorie = $repo->find($id);
    //     return $this->render('categorie_news/oneCategory.html.twig', [
    //         'categorie' => $categorie,
    //     ]);
    // }

    # add categorie

    /**
     * @Route("/addcategorienews", name="addCategoryNews")
     */
    public function AddCategory(Request $request, CategorieNewsRepository $repo): Response
    {
        $categories = $repo->findAll();
        $category = new CategorieNews();
        $form = $this->createForm(CategorieNewsFormType::class,$category);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();
            return $this->redirectToRoute('allCategoriesNews');
        }
        return $this->render('categorie_news/addCategory.html.twig', [
            'form_title' => 'Ajouter une categorie de News',
            'form_add' => $form->createView(),
            'categories' => $categories,

        ]);
    }

    # update categorie

    /**
     * @Route("/updateCategoryNews/{id}", name="updateCategoryNews")
     */
    public function UpdateCategoryNews(Request $request, $id, CategorieNewsRepository $repo): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $category = $repo->find($id);
        $form = $this->createForm(CategorieNewsFormType::class, $category);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $entityManager->flush();
            return $this->redirectToRoute('allCategoriesNews');
        }
        return $this->render('categorie_news/updateCategory.html.twig', [
            'form_title' => 'Modifier une categorie',
            'form_add' => $form->createView(),
        ]);
    }

    #delete cat

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/deleteCategory/{id}", name="deleteCategory")
     */
    public function DeleteCategory($id, CategorieNewsRepository $repo): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $category = $repo->find($id);
        $entityManager->remove($category);
        $entityManager->flush();

        return $this->redirectToRoute('allCategoriesNews');

    }
}
