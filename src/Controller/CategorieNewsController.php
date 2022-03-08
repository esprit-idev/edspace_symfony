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
        return $this->render('categorie_news/back/index.html.twig', [
            'controller_name' => 'CategorieNewsController',
        ]);
    }


  /**
     * @Route("/allcategoriesnews", name="allCategoriesNews")
     */
    public function allCategories(CategorieNewsRepository $repo): Response
    {
        $hasAccessStudent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessStudent){
        $categories = $repo->findAll();
        }else{
            return $this->render('/403.html.twig');
        }
        return $this->render('categorie_news/back/allCategories.html.twig', [
            'controller_name' => 'CategorieNewsController',
            'categories' => $categories,
        ]);
    }

    # add categorie

    /**
     * @Route("/addcategorienews", name="addCategoryNews")
     */
    public function AddCategory(Request $request, CategorieNewsRepository $repo): Response
    {
        $hasAccessStudent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessStudent){
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
        }else{
            return $this->render('/403.html.twig');
        }
        return $this->render('categorie_news/back/addCategory.html.twig', [
            'form_title' => 'Ajouter une categorie de News',
            'form' => $form->createView(),
            'categories' => $categories,

        ]);
    }

    # update categorie

    /**
     * @Route("/updateCategoryNews/{id}", name="updateCategoryNews")
     */
    public function UpdateCategoryNews(Request $request, $id, CategorieNewsRepository $repo): Response
    {
        $hasAccessStudent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessStudent){
        $entityManager = $this->getDoctrine()->getManager();
        $category = $repo->find($id);
        $form = $this->createForm(CategorieNewsFormType::class, $category);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $entityManager->flush();
            return $this->redirectToRoute('allCategoriesNews');
        }
        }else{
            return $this->render('/403.html.twig');
        }
        return $this->render('categorie_news/back/updateCategory.html.twig', [
            'form_title' => 'Modifier une categorie',
            'form_add' => $form->createView(),
        ]);
    }

    #delete cat

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/deleteCategory/{id}", name="deleteCategoryNews")
     */
    public function DeleteCategory($id, CategorieNewsRepository $repo): Response
    {
        $hasAccessStudent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessStudent){
        $entityManager = $this->getDoctrine()->getManager();
        $category = $repo->find($id);
        $entityManager->remove($category);
        $entityManager->flush();
        }else{
            return $this->render('/403.html.twig');
        }
        return $this->redirectToRoute('allCategoriesNews');

    }
      /**
     * @Route("/allcategoriesnews/search", name="SearchCatName")
     */
    public function SearchCategory(CategorieNewsRepository $repo, Request $request): Response
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
            return $this->render('/403.html.twig');
        }
        return $this->render('categorie_news/back/allCategories.html.twig', 
            array('categories' => $categories,)
        );
    }
}
