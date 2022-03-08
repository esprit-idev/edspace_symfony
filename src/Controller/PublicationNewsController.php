<?php

namespace App\Controller;

use App\Entity\CategorieNews;
use App\Entity\PublicationNews;
use App\Form\PublicationNewsFormType;
use App\Repository\CategorieNewsRepository;
use App\Repository\EmploiRepository;
use App\Repository\PublicationNewsRepository;
use PhpParser\Node\Stmt\Foreach_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
class PublicationNewsController extends AbstractController
{
    private $user=1;
    /**
     * @Route("/publication", name="publication")
     */
    public function index(PublicationNewsRepository $repo, ChartBuilderInterface $chartBuilder, CategorieNewsRepository $catRepo, EmploiRepository $E_Repo): Response
    {
        $publications = $repo->findAll();
        $categories = $catRepo->findAll();
        $pubNum = $repo->CountPublications();
        $emploiNum = $E_Repo->CountEmploi();
        $pubs = array();
        $arrayCategoryName = array();
        Foreach($categories as $category){
            array_push($arrayCategoryName, $category->getCategoryName());
            array_push($pubs, $category->getId());
        }
        $chart = $chartBuilder->createChart(Chart::TYPE_PIE);
        $chart->setData([
            'labels' => $arrayCategoryName,
            'datasets' => [
                [
                    'label' => 'N Publications Par Categorie',
                    'backgroundColor' => [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(255, 5, 86)',
                        'rgb(255, 05, 86)',
                    ],
                    'borderColor' => 'rgb(255, 255, 255)',
                    'data' => $pubs,
                ],
            ],
        ]);
        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 100,
                ],
            ],
        ]);

        //line chart 
        $chartLine = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chartLine->setData([
            'labels' => $arrayCategoryName,
            'datasets' => [
                [
                    'label' => 'Nombre de Publication Par Categorie',
                    'backgroundColor' => [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                    ],
                    'borderColor' => 'rgb(255, 255, 255)',
                    'data' => [12, 4,0,1],
                ],
            ],
        ]);
        $chartLine->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 100,
                ],
            ],
        ]);
        return $this->render('/home.html.twig', [
            'controller_name' => 'PublicationNewsController',
            'publications' => $pubNum,
            'emplois' => $emploiNum,
            'chart' => $chart,
            'chartLine' => $chartLine,
        ]);
    }
    /**
     * @Route("/allpublications", name="allPublications")
     */
    public function allPubs(PublicationNewsRepository $repo, CategorieNewsRepository $catRepo, Request $request): Response
    {

        $templateName = 'publication_news/back/allPublication.html.twig';
        $categories = $catRepo->findAll();
        $publications = $repo->findAll();
        if($this->user == 1){
            $templateName = 'publication_news/front/allPublication_FO.html.twig';
        }
        return $this->render($templateName, [
            'controller_name' => 'PublicationNewsController',
            'publications' => $publications,
            'categories' =>$categories,
        ]);
    }

    #read one single publication

    /**
     * @param $id
     * @Route("/unepublication/{id}", name="onePublication")
     */
    public function OnePublication($id, PublicationNewsRepository $repo): Response
    {
        $em = $this->getDoctrine()->getManager();
        // to check if we did refresh the browser page
        $pageWasRefreshed = isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0';
        $templateName = 'publication_news/back/onePublication.html.twig';
        $publication = $repo->find($id);
        $publications = $repo->findAll();
        $likes = $publication->getLikes();
        $views = $publication->getVues();
        $comments = $publication->getComments();
        $comment = count(array($comments));
        if($pageWasRefreshed){
            $views = $publication->increment();
            $em->flush();
        }else{
            $views = $publication->getVues();
            $em->flush();
        } 
        if($this->user == 1){
            $templateName = 'publication_news/front/onePublication_FO.html.twig';
        }
        return $this->render($templateName, [
            'publications' => $publication,
            'publication_title' => $publication->getTitle(),
            'publication_content' => $publication->getContent(),
            'publication_image' => $publication->getImage()->getName(),
            'likes' => $likes,
            'id' => $id,
            'comments' =>$comments,
            'num' => $comment,
            'views' =>$views,
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
            $path = $this->getParameter('kernel.project_dir').'/public/images';
            $image = $publication->getImage();
            /** @var UploadedFile $file */
            $file = $image->getFile();
            if(!empty($file)){
                $imageName = md5(uniqid()).'.'.$file->guessExtension();
                $file->move($path, $imageName);
                $image->setName($imageName);
            }
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
            $path = $this->getParameter('kernel.project_dir').'/public/images';
            $image = $publication->getImage();
            $file = $image->getFile();
            $imageName = md5(uniqid()).'.'.$file->guessExtension();
            $file->move($path, $imageName);
            $image->setName($imageName);
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
     * @Route("/allpublications/search", name="search")
     */
    public function searchPublication(Request $request, PublicationNewsRepository $repo){

        $templateName = 'publication_news/back/allPublication.html.twig';
        $publications = $repo->findAll();
        if($request->isMethod('POST')){
            $publicationTitle = $request->get('publicationTitle');
            if($publicationTitle !== ''){
                $publications = $repo->SearchByTitle($publicationTitle);
            }
            
        }
        return $this->render($templateName, array('publications' => $publications));
    }

    /**
     * @Route("/allpublications/searchByCat", name="searchByCat")
     */
    public function searchPubByCategoryName(Request $request, PublicationNewsRepository $repo, CategorieNewsRepository $catRepo){

        $templateName = 'publication_news/front/allPublication_FO.html.twig';
        $publications = $repo->findAll();
        // $publication= $repo->find($id);
        $categories = $catRepo->findAll();
        if($request->isMethod('POST')){
            $category = $request->get('categoryKey');
            $date = $request->get('dateKey');
            $publications = $repo->findNewsByCategory($category); 
            // $publications = $repo->SortByDateASC();  
        }
        return $this->render($templateName, array('publications' => $publications,'categories' => $categories));
    }

    /**
     * @param $id
     * @Route("/unepublication/post/{id}", name="postComment")
     */
    public function PostComment($id, PublicationNewsRepository $repo, Request $request): Response
    {
        $templateName = 'publication_news/back/onePublication.html.twig';
        $em = $this->getDoctrine()->getManager();
        $publications = $repo->findAll();
        $publication = $repo->find($id);
        $likes = $publication->getLikes();
        $views = $publication->getVues();
        $comments = $publication->getComments();
        $comment = count(array($comments));

        if($request->isMethod('POST')){
            $comment = $request->get('comment');
            $publication->setComments($comment);
            $em->flush();
        }
        $array = array();
        Foreach($publications as $pub){
            array_push($array, $pub->getComments());
        }
        if($this->user == 1){
            $templateName = 'publication_news/front/onePublication_FO.html.twig';
        }
        return $this->render($templateName, array(
            'publications' => $publication,
            'publication_title' => $publication->getTitle(),
            'publication_content' => $publication->getContent(),
            'publication_image' => $publication->getImage()->getName(),
            'likes' => $likes,
            'id' => $id,
            'comments' => $array,
            'views' =>$views,
            'num' => $comment
        ));
    }
    
    /**
     * @param $id
     * @Route("/unepublicationLikes/{id}", name="onePublicationLikes")
     */
    public function OnePublicationLikes($id, PublicationNewsRepository $repo, Request $request): Response
    {
        $pageWasRefreshed = isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0';
        $em = $this->getDoctrine()->getManager();
        $templateName = 'publication_news/back/onePublication.html.twig';
        $publication = $repo->find($id);
        $nextPublication = $repo->getPreviousUser($publication->getTitle());
        $likes = $publication->getLikes();
        $views = $publication->getVues();
        $comments = $publication->getComments();
        $comment = count(array($comments));
        if($pageWasRefreshed){
            $likes = $publication->getLikes();
            $comments = $publication->getComments();
            $em->flush();
        }else{
            $likes = $publication->incrementLikes();
            $em->flush();
        }
        if($this->user == 1){
            $templateName = 'publication_news/front/onePublication_FO.html.twig';
        }
        return $this->render($templateName, [
            'publications' => $publication,
            'publication_title' => $publication->getTitle(),
            'publication_content' => $publication->getContent(),
            'publication_image' => $publication->getImage()->getName(),
            'likes' => $likes,
            'id' => $id,
            'comments' =>$publication->getComments(),
            'views' =>$views,
            'num' => $comment
        ]);
    }
}
