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
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mercure\Discovery;

class ConversationController extends AbstractController
{


    /**
     * @Route("/conversation", name="conversation")
     */
    public function start(): Response
    {
        $em=$this->getDoctrine()->getManager();
        $user1=$em->getRepository(User::class)->find(1);
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
     * @Route("/conversation3", name="conversation3")
     */
    public function start3(): Response
    {
        $em=$this->getDoctrine()->getManager();
        $user1=$em->getRepository(User::class)->find(2);
        $em1=$this->getDoctrine()->getRepository(User::class);
        $memebers=$em1->findBy(['classe'=> $user1->getClasse()->getId()]);
        $classe=$em->getRepository(Classe::class)->find($user1->getClasse()->getId());
      
        $message=$this
        ->getDoctrine()
        ->getManager()
        ->getRepository(Message::class)
        ->findBy(array('classe'=> $user1->getClasse()->getId()),array('postDate' => 'ASC'));
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
     * @Route("/conversation2", name="conversation2")
     */
    public function start2(): Response
    {
        $em=$this->getDoctrine()->getManager();
        $user1=$em->getRepository(User::class)->find(3);
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



    
    /**
     * @Route("/push", name="push")
     * @param Request $request
     */

    public function publish(PublisherInterface $publisher,Request $request): Response
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



        $update = new Update(
            'http://127.0.0.1:8000/conversation',
            json_encode(['message' => $request->request->get('content'),
            'user' => $request->request->get('idu'),
            'classe' => $request->request->get('classe'),
        ]),
        );

        // Publisher's JWT must contain this topic, a URI template it matches or * in mercure.publish or you'll get a 401
        // Subscriber's JWT must contain this topic, a URI template it matches or * in mercure.subscribe to receive the update
       
        $publisher($update);

        return $this->json('Done');
    }




      /**
     * @Route("/discover", name="discover")
     */

    public function discover(Request $request, Discovery $discovery): JsonResponse
    {
        // Link: <https://hub.example.com/.well-known/mercure>; rel="mercure"
        $discovery->addLink($request);

        return $this->json([
            '@id' => '/books/1',
            'availability' => 'https://schema.org/InStock',
        ]);
    }





}
