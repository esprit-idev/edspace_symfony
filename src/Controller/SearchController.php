<?php

namespace App\Controller;

use App\Entity\Thread;
use App\Repository\ThreadRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    /**
     * @Route("/search", name="search")
     */
    public function index(): Response
    {
        return $this->render('search/index.html.twig', [
            'controller_name' => 'SearchController',
        ]);
    }
    public function searchBar(){
        $form = $this->createFormBuilder(null)
        ->setAction($this->generateUrl('handleSearch'))
        ->add('query',TextType::class,[
            'attr' => [
                'style' => 'width : 100%',
                'aria-describedby' => 'search-addon',
                'aria-label' => 'Search',
                'class'=>'form-control rounded',
                'placeholder' => 'What are you looking for ?'
            ]
        ])
        ->add('search',SubmitType::class, [
            'attr' =>[
                'class' =>'btn btn-outline-primary',
                'style' =>'margin-top:-96px; margin-left:350px;'
            ]
        ])
        ->getForm();

        return $this->render('thread/searchBard/searchBar.html.twig',[
            'form' => $form->createView()
        ]);
    }
    /**
     * @Route("/handleSearch",name="handleSearch") 
     */
    public function handleSearch(Request $request,ThreadRepository $threadRepository){
        $query = $request->request->get('form')['query'];
        if ($query ){
        $thread = $threadRepository->findThreadByName($query);
        return $this->render('thread/searchedThreads.html.twig',[
            'posts' => $thread
        ]);
        }
        if($query == "0"){
            return $this->render('thread/404.html.twig');
        }
        

    }
    public function handleUser(Request $request, UserRepository $rep){
        $query = $request->request->get('form')['query'];
        if ($query ){
            $User = $rep->findByUserName($query);
            return $this->render('thread/searchedThreads.html.twig',[
                'posts' => $User
            ]);
        }
        if($query == "0"){
            return $this->render('thread/404.html.twig');
        }

    }
}
