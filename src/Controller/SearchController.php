<?php

namespace App\Controller;
use App\Entity\User;
use App\Entity\Classe;
use App\Entity\Message;

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
                    'aria-label' => 'Rechercher',
                    'class'=>'form-control rounded',
                    'placeholder' => 'Qu\'est ce que vous chercher ?'
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
            if($this->getUser()!= null){
                $em=$this->getDoctrine()->getManager();
                $user1=$em->getRepository(User::class)->find($this->getUser()->getId());
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
                return $this->render('thread/searchedThreads.html.twig', [
                    'posts' => $thread,
                    'memebers'=> $memebers,
                    'user' => $user1,
                    'classe'=> $classe,
                    'message'=> $message,
                    'mymsg' => $mymsg,
                    'others' =>$othersmsg,
                ]);
            }
            else {
                return $this->render("/403.html.twig");

            }
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
