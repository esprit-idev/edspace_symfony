<?php

namespace App\Controller;

use App\Entity\Classe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Message;
use DateTime;
use Symfony\Component\HttpFoundation\Request;

class ConversationController extends AbstractController
{


    /**
     * @Route("/conversation", name="conversation")
     */
    public function start(): Response
    {
        $em=$this->getDoctrine()->getManager();
        $user1=$em->getRepository(User::class)->find(8);
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
        

        return $this->render('conversation/conversation.html.twig', [
            'memebers'=> $memebers,
            'user' => $user1,
            'classe'=> $classe,
            'message'=> $message,
            'mymsg' => $mymsg,
            'others' =>$othersmsg
        ]);
    }



    /**
     * @Route("/addconv", name="addconv")
     * @param Request $request
     */
    public function add(Request $request): Response
    {   


        if($request->request->count() > 0){

        $em2=$this->getDoctrine()->getManager();
        $user=$em2->getRepository(User::class)->find($request->request->get('idu'));
        $classe=$em2->getRepository(Classe::class)->find($request->request->get('idc'));

        $date=DateTime::createFromFormat('m/d/Y',02/23/2022);
        $message=new Message();
        $message->setUser($user);
        $message->setClasse($classe);
        $message->setContent($request->request->get('content'));
        $message->setPostDate(\DateTime::createFromFormat('Y-m-d', "2022-02-23"));
        
        $em2->persist($message);
        $em2->flush();

        }
        return $this->redirectToRoute('conversation');
    }



}
