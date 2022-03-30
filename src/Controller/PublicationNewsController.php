<?php

namespace App\Controller;

use App\Entity\PublicationNews;
use App\Form\PublicationNewsFormType;
use App\Repository\CategorieNewsRepository;
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
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
class PublicationNewsController extends AbstractController
{

    /**
     * @Route("/allpublications", name="allPublications")
     */
    public function allPubs(PublicationNewsRepository $repo, CategorieNewsRepository $catRepo, Request $request): Response
    {
            $categories = $catRepo->findAll();
            $publications = $repo->findAll();
            $user1='';
            $em1='';
            $memebers='';
            $classe='';
            $message='';
            $hasAccessStudent = $this->isGranted('ROLE_STUDENT');
            $hasAccessResponsable = $this->isGranted('ROLE_RESPONSABLE');
            //messages
                $em=$this->getDoctrine()->getManager();
                $mymsg=[];
                $othersmsg=[];
            if($hasAccessStudent || $hasAccessResponsable ){
                $test=$this->getUser()->getId();
                $user1=$em->getRepository(User::class)->find($test);
                $em1=$this->getDoctrine()->getRepository(User::class);
                $memebers=$em1->findBy(['classe'=> $user1->getClasse()->getId()]);
                $classe=$em->getRepository(Classe::class)->find($user1->getClasse()->getId());
                $message=$this
                ->getDoctrine()
                ->getManager()
                ->getRepository(Message::class)
                ->findBy(array(),array('postDate' => 'ASC'));
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
            $templateName = 'publication_news/front/allPublication_FO.html.twig';
        }
        else{
            $templateName = 'publication_news/back/allPublication.html.twig';
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
            $user1='';
            $em1='';
            $memebers='';
            $classe='';
            $message='';
            $hasAccessStudent = $this->isGranted('ROLE_STUDENT');
        // to check if we did refresh the browser page
        $pageWasRefreshed = isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0';
        $publication = $repo->find($id);
        $publications = $repo->findAll();
        $likes = $publication->getLikes();
        $views = $publication->getVues();
        $comments = $publication->getComments();
        $comment = count(array($comments));
            //messages
       
        $em=$this->getDoctrine()->getManager();
        $mymsg=[];
        $othersmsg=[];
        if($hasAccessStudent){
            $test=$this->getUser()->getId();
                $user1=$em->getRepository(User::class)->find($test);
                $em1=$this->getDoctrine()->getRepository(User::class);
                $memebers=$em1->findBy(['classe'=> $user1->getClasse()->getId()]);
                $classe=$em->getRepository(Classe::class)->find($user1->getClasse()->getId());
                $message=$this
                ->getDoctrine()
                ->getManager()
                ->getRepository(Message::class)
                ->findBy(array(),array('postDate' => 'ASC'));
                //messages 
                foreach($message as $i){
                    if($i->getUser()->getId()==$user1->getId()){
                        $mymsg[]=$i;
                    }
                    else{
                        $othersmsg[]=$i;
                    }
                }
                if($pageWasRefreshed){
                    $views = $publication->increment();
                    $em->flush();
                }else{
                    $views = $publication->getVues();
                    $em->flush();
                } 
            //endmessages
            $templateName = 'publication_news/front/onePublication_FO.html.twig';
        }
        else{
            $templateName = 'publication_news/back/onePublication.html.twig';
        }
        return $this->render($templateName, [
            'publications' => $publication,
            'publication_title' => $publication->getTitle(),
            'publication_content' => $publication->getContent(),
            'publication_image' => $publication->getImage(),
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
            $path = $this->getParameter('NewsImages_directory');

            $image = $form->get('image')->getData();
            //$file = $image->getFile();
            if($image !=null){
                $imageName = md5(uniqid()).'.'.$image->guessExtension();
                try{
                    $image->move($path, $imageName);
                } catch(FileException $ex){
                    return $this->render('/403.html.twig');
                }
                $publication->setImage($imageName);
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
            $path = $this->getParameter('NewsImages_directory');
            $image = $form->get('image')->getData();
            if($image != null){
                $imageName = md5(uniqid()).'.'.$image->guessExtension();
                try{
                    $image->move($path, $imageName);
                }catch(FileException $e){
                    return $e;
                }
                $publication->setImage($imageName);
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
    public function searchPubByCategoryName(Request $request, PublicationNewsRepository $repo, CategorieNewsRepository $catRepo):Response
    {
        $publications = $repo->findAll();
        $categories = $catRepo->findAll();
        $user1='';
            $em1='';
            $memebers='';
            $classe='';
            $message='';
            $hasAccessStudent = $this->isGranted('ROLE_STUDENT');
            //messages
                $em=$this->getDoctrine()->getManager();
                $mymsg=[];
                $othersmsg=[];
            if($hasAccessStudent){
                $test=$this->getUser()->getId();
                $user1=$em->getRepository(User::class)->find($test);
                $em1=$this->getDoctrine()->getRepository(User::class);
                $memebers=$em1->findBy(['classe'=> $user1->getClasse()->getId()]);
                $classe=$em->getRepository(Classe::class)->find($user1->getClasse()->getId());
                $message=$this
                ->getDoctrine()
                ->getManager()
                ->getRepository(Message::class)
                ->findBy(array(),array('postDate' => 'ASC'));
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
            $templateName = 'publication_news/front/allPublication_FO.html.twig';
        }
        else{
            $templateName = 'publication_news/back/allPublication.html.twig';
        }
       
        if($request->isMethod('POST')){
            $category = $request->get('categoryKey');
            $date = $request->get('dateKey');
            $publications = $repo->findNewsByCategory($category); 
        }
        return $this->render($templateName, array(
        'publications' => $publications,
        'memebers'=> $memebers,
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
            $user1='';
            $em1='';
            $memebers='';
            $classe='';
            $message='';
            $em=$this->getDoctrine()->getManager();
            $mymsg=[];
            $othersmsg=[];
        if($hasAccessStudent){
            $test=$this->getUser()->getId();
            $user1=$em->getRepository(User::class)->find($test);
            $em1=$this->getDoctrine()->getRepository(User::class);
            $memebers=$em1->findBy(['classe'=> $user1->getClasse()->getId()]);
            $classe=$em->getRepository(Classe::class)->find($user1->getClasse()->getId());
            $message=$this
            ->getDoctrine()
            ->getManager()
            ->getRepository(Message::class)
            ->findBy(array(),array('postDate' => 'ASC'));
            $templateName = 'publication_news/front/onePublication_FO.html.twig';
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
            'num' => $comment,
            'user' => $user1,
            'classe'=> $classe,
            'message'=> $message,
            'mymsg' => $mymsg,
            'memebers'=> $memebers,
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
        $hasAccessResponsable = $this->isGranted('ROLE_RESPONSABLE');
            $user1='';
            $em1='';
            $memebers='';
            $classe='';
            $message='';
            $em=$this->getDoctrine()->getManager();
            $mymsg=[];
            $othersmsg=[];
        if($hasAccessStudent || $hasAccessResponsable){
            $test=$this->getUser()->getId();
            $user1=$em->getRepository(User::class)->find($test);
            $em1=$this->getDoctrine()->getRepository(User::class);
            $memebers=$em1->findBy(['classe'=> $user1->getClasse()->getId()]);
            $classe=$em->getRepository(Classe::class)->find($user1->getClasse()->getId());
            $message=$this
            ->getDoctrine()
            ->getManager()
            ->getRepository(Message::class)
            ->findBy(array(),array('postDate' => 'ASC'));
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
            'num' => $comment,
            'user' => $user1,
            'classe'=> $classe,
            'message'=> $message,
            'mymsg' => $mymsg,
            'memebers'=> $memebers,

        ]);
    }

    //Json methods

    /**
     * @param NormalizerInterface $normalizer
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route("/allpubsJSON", name="allPubsJSON")
     */
    public function allPubsJSON(PublicationNewsRepository $repository, NormalizerInterface $normalizer): Response
    {
        $publications = $repository->findAll();
        $jsonContent = $normalizer->normalize($publications,'json',['groups'=>['publications', 'categories','pubimage']]);
        return new Response(json_encode($jsonContent),200);
    }

     /**
     * @param NormalizerInterface $normalizer
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route ("/onepubjson/{id}",name="onepubsJSON")
     */
    public function displayOnePubJSON($id,PublicationNewsRepository $repository, NormalizerInterface $normalizer): Response
    {
        $publications = $repository->find($id);
        $jsonContent = $normalizer->normalize($publications,'json',['groups'=>['publications', 'categories','pubimage']]);
        return new Response(json_encode($jsonContent));
    }

    /**
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route ("/addpubsJSON/new",name="addpubs")
     */
    public function addPubJSON(NormalizerInterface $normalizer, Request $request, CategorieNewsRepository $repCat):Response
    {
        $em = $this->getDoctrine()->getManager();
        $publication = new PublicationNews();
        $publication->setTitle($request->get('title'));
        $publication->setOwner($request->get('owner'));
        $publication->setDate(new DateTime());
        $category=$repCat->findOneBy(array('categoryName'=>$request->get('categoryName')));
        $publication->setCategoryName($category);
        $publication->setImage($request->get('image'));
        $publication->setContent($request->get('content'));
        

        $em->persist($publication);
        $em->flush();

        $jsonContent = $normalizer->normalize($publication,'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }

     /**
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route ("/updatepubsJSON/{id}",name="updatepubs")
     */
    public function updatePubJSON(PublicationNewsRepository $repository, NormalizerInterface $normalizer,CategorieNewsRepository $repCat, Request $request,$id):Response
    {
        $em = $this->getDoctrine()->getManager();
        $publication = $repository->find($id);
        $publication->setTitle($request->get('title'));
        $publication->setOwner($request->get('owner'));
        $publication->setDate(new DateTime());
        $publication->setContent($request->get('content'));
        $category=$repCat->findOneBy(array('categoryName'=>$request->get('categoryName')));
        $publication->setCategoryName($category);
        $publication->setImage($request->get("image"));
        $em->flush();

        $jsonContent = $normalizer->normalize($publication,'json',['groups'=>'post:read']);
        return new Response("modified successfully".json_encode($jsonContent));
    }

    /**
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route ("/deletepubsJSON/{id}",name="deletepubs")
     */
    public function deletePubJSON(PublicationNewsRepository $repository, NormalizerInterface $normalizer, Request $request,$id):Response
    {
        $em = $this->getDoctrine()->getManager();
        $publication = $repository->find($id);
        $em->remove($publication);
        $em->flush();

        $jsonContent = $normalizer->normalize($publication,'json',['groups'=>'post:read']);
        return new Response("deleted successfully".json_encode($jsonContent));
    }

}
