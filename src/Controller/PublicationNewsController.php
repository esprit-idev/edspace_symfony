<?php

namespace App\Controller;

use App\Entity\PublicationNews;
use App\Form\PublicationNewsFormType;
use App\Repository\CategorieEmploiRepository;
use App\Repository\CategorieNewsRepository;
use App\Repository\EmploiRepository;
use App\Repository\PublicationNewsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Entity\Message;
use App\Entity\Classe;
use DateTime;
class PublicationNewsController extends AbstractController
{

    /**
     * @Route("/allpublications", name="allPublications")
     */
    public function allPubs(PublicationNewsRepository $repo, CategorieNewsRepository $catRepo, Request $request): Response
    {
        $categories = $catRepo->findAll();
        $publications = $repo->findAll();
        $hasAccessStudent = $this->isGranted('ROLE_ADMIN');
        $test=$this->getUser()->getId();
            $em=$this->getDoctrine()->getManager();
            $user1=$em->getRepository(User::class)->find($test);
            $em1=$this->getDoctrine()->getRepository(User::class);
            $memebers=$em1->findBy(['classe'=> $user1->getClasse()->getId()]);
            $classe=$em->getRepository(Classe::class)->find($user1->getClasse()->getId());
            $message=$this
            ->getDoctrine()
            ->getManager()
            ->getRepository(Message::class)
            ->findBy(array(),array('postDate' => 'ASC'));
            $mymsg=[];
            $othersmsg=[];
        if($hasAccessStudent){
             //messages 
            foreach($message as $i){
                if($i->getUser()->getId()==$user1->getId()){
                    $mymsg[]=$i;
                }
                else{
                    $othersmsg[]=$i;
                }
            }
            //endmessages
            $templateName = 'publication_news/back/allPublication.html.twig';
        }
        else{
            $templateName = 'publication_news/front/allPublication_FO.html.twig';
        }
        return $this->render($templateName, [
            'controller_name' => 'PublicationNewsController',
            'publications' => $publications,
            'categories' =>$categories,
            'user' => $user1,
            'classe'=> $classe,
            'message'=> $message,
            'mymsg' => $mymsg,
            'memebers'=> $memebers,
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
        $publication = $repo->find($id);
        $publications = $repo->findAll();
        $likes = $publication->getLikes();
        $views = $publication->getVues();
        $comments = $publication->getComments();
        $comment = count(array($comments));
        //messages
        $test=$this->getUser()->getId();
            $em=$this->getDoctrine()->getManager();
            $user1=$em->getRepository(User::class)->find($test);
            $em1=$this->getDoctrine()->getRepository(User::class);
            $memebers=$em1->findBy(['classe'=> $user1->getClasse()->getId()]);
            $classe=$em->getRepository(Classe::class)->find($user1->getClasse()->getId());
            $message=$this
            ->getDoctrine()
            ->getManager()
            ->getRepository(Message::class)
            ->findBy(array(),array('postDate' => 'ASC'));
            $mymsg=[];
            $othersmsg=[];
            if($pageWasRefreshed){
                $views = $publication->increment();
                $em->flush();
            }else{
                $views = $publication->getVues();
                $em->flush();
            }
        $hasAccessStudent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessStudent){
             //messages 
             foreach($message as $i){
                if($i->getUser()->getId()==$user1->getId()){
                    $mymsg[]=$i;
                }
                else{
                    $othersmsg[]=$i;
                }
            }
            $templateName = 'publication_news/back/onePublication.html.twig';
        }
        else{
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
            'user' => $user1,
            'classe'=> $classe,
            'message'=> $message,
            'mymsg' => $mymsg,
            'memebers'=> $memebers,
        ]);
    }
     # add a publication
    /**
     * @Route("/addpublication", name="addPublication")
     */
    public function AddPublications(Request $request, PublicationNewsRepository $repo): Response
    {   
        $hasAccessStudent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessStudent){
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
        }else{
            return $this->render('/403.html.twig');
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
        $hasAccessStudent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessStudent){
        $entityManager = $this->getDoctrine()->getManager();
        $publication = $repo->find($id);
        $form = $this->createForm(PublicationNewsFormType::class,$publication);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $path = $this->getParameter('kernel.project_dir').'/public/images';
            $image = $publication->getImage();
            $file = $image->getFile();
            if($file != null){
                $imageName = md5(uniqid()).'.'.$file->guessExtension();
                try{
                    $file->move($path, $imageName);
                }catch(FileException $e){
                    return $e;
                }
                $image->setName($imageName);
            } 
            
            $entityManager->flush();
            return $this->redirectToRoute('allPublications');
        }
        }else{
            return $this->render('/403.html.twig');
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
        $hasAccessStudent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessStudent){
         $entityManager = $this->getDoctrine()->getManager();
         $publication = $repo->find($id);
         $entityManager->remove($publication);
         $entityManager->flush();

        return $this->redirectToRoute('allPublications');
        }else{
            return $this->render('/403.html.twig');
        }
    }
    /**
     * @Route("/allpublications/search", name="searchNews")
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

        $test=$this->getUser()->getId();
        $em=$this->getDoctrine()->getManager();
        $user1=$em->getRepository(User::class)->find($test);
        $em1=$this->getDoctrine()->getRepository(User::class);
        $memebers=$em1->findBy(['classe'=> $user1->getClasse()->getId()]);
        $classe=$em->getRepository(Classe::class)->find($user1->getClasse()->getId());
      
        $message=$this
        ->getDoctrine()
        ->getManager()
        ->getRepository(Message::class)
        ->findBy(array(),array('postDate' => 'ASC'));
        $mymsg=[];
        $othersmsg=[];
        foreach($message as $i){
            if($i->getUser()->getId()==$user1->getId()){
                $mymsg[]=$i;
            }
            else{
                $othersmsg[]=$i;
            }
        }


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
        return $this->render($templateName, array('publications' => $publications,'memebers'=> $memebers,
        'user' => $user1,
        'classe'=> $classe,
        'message'=> $message,
        'mymsg' => $mymsg,
        'others' =>$othersmsg,'categories' => $categories));
    }

    /**
     * @param $id
     * @Route("/unepublication/post/{id}", name="postComment")
     */
    public function PostComment($id, PublicationNewsRepository $repo, Request $request): Response
    {
        $hasAccessStudent = $this->isGranted('ROLE_STUDENT');
        if($hasAccessStudent){
            $templateName = 'publication_news/front/onePublication_FO.html.twig';
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
        }else{
            return $this->render('/403.html.twig');
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
        //check if page is being refreshed
        $pageWasRefreshed = isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0';
        $hasAccessStudent = $this->isGranted('ROLE_STUDENT');
        if($hasAccessStudent){
            $em = $this->getDoctrine()->getManager();
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
                $templateName = 'publication_news/front/onePublication_FO.html.twig';
        }else{
            return $this->render('/403.html.twig');
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
