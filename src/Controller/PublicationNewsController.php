<?php

namespace App\Controller;

use App\Entity\PublicationNews;
use App\Form\PublicationNewsFormType;
use App\Repository\PublicationNewsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class PublicationNewsController extends AbstractController
{
    
        
    /**
     * @Route("/publication", name="publication")
     */
    public function index(): Response
    {
        return $this->render('publication_news/back/index.html.twig', [
            'controller_name' => 'PublicationNewsController',
        ]);
    }
    /**
     * @Route("/allpublications", name="allPublications")
     */
    public function allPubs(PublicationNewsRepository $repo): Response
    {
        $user=0;
        $templateName = 'publication_news/back/allPublication.html.twig';
        $publications = $repo->findAll();
        if($user == 1){
            $templateName = 'publication_news/front/allPublication_FO.html.twig';
        }
        return $this->render($templateName, [
            'controller_name' => 'PublicationNewsController',
            'publications' => $publications,
        ]);
    }

    #read one single publication

    /**
     * @param $id
     * @Route("/unepublication/{id}", name="onePublication")
     */
    public function OnePublication($id, PublicationNewsRepository $repo): Response
    {
        $user=0;
        $templateName = 'publication_news/back/onePublication.html.twig';
        $publication = $repo->find($id);
        if($user == 1){
            $templateName = 'publication_news/front/onePublication_FO.html.twig';
        }
        return $this->render($templateName, [
            'publications' => $publication,
            'publication_title' => $publication->getTitle(),
            'publication_category' =>$publication->getCategorieNews()->getCatName(),
            'publication_content' => $publication->getContent(),
        ]);
    }
     # add a publication
    /**
     * @Route("/addpublication", name="addPublication")
     */
    public function AddPublications(Request $request, PublicationNewsRepository $repo): Response
    {
        $publications = $repo->findAll();
        $publication = new PublicationNews();
        $form = $this->createForm(PublicationNewsFormType::class,$publication);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($publication);
            $entityManager->flush();
            return $this->redirectToRoute('allPublications');
        }
        return $this->render('publication_news/back/addPublication.html.twig', [
            'form_title' => 'Ajouter une publication',
            'form_add' => $form->createView(),
            'publicationNews' => $publications,

        ]);
    }

      # update a publication
    /**
     * @param $id
     * @Route("/updatepublication/{id}", name="updatePublication")
     */
    public function UpdatePublication(Request $request, PublicationNewsRepository $repo, $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $publication = $repo->find($id);
        $form = $this->createForm(PublicationNewsFormType::class,$publication);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $entityManager->flush();
            return $this->redirectToRoute('allPublications');
        }
        return $this->render('publication_news/back/updatePublication.html.twig', [
            'form_title' => 'Modifier une publication',
            'form_add' => $form->createView(),
        ]);
    }
     #delete publication
    /**
     * @Route("/deletepublication/{id}", name="deletePublication")
     */
    public function DeletePublication(PublicationNewsRepository $repo, $id): Response
    {
         $entityManager = $this->getDoctrine()->getManager();
         $publication = $repo->find($id);
         $entityManager->remove($publication);
         $entityManager->flush();

        return $this->redirectToRoute('allPublications');

    }
    /**
     * @Route("/search", name="search")
     */
    public function searchPublicationByDate(Request $request, PublicationNewsRepository $repo){

        $templateName = 'publication_news/back/searchAllPublication.html.twig';
        $publications = $repo->findAll();
        if($request->isMethod('POST')){
            $publicationTitle = $request->get('publicationTitle');
            $publications = $repo->findBy(array('title' => $publicationTitle));
        }
        return $this->render($templateName,[
            'controller_name' => 'PublicationNewsController',
            array('publications' => $publications)
            ]);
    }
}
