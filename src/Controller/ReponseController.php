<?php

namespace App\Controller;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use App\Entity\Reponse;
use App\Entity\User;
use App\Entity\Message;
use App\Entity\Classe;
use App\Form\ReponseType;
use App\Repository\ReponseRepository;
use App\Repository\ThreadRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/reponse")
 */
class ReponseController extends AbstractController
{
    /**
     * @Route("/", name="reponse_index", methods={"GET"})
     */
    public function index(ReponseRepository $reponseRepository): Response
    {
        return $this->render('reponse/index.html.twig', [
            'reponses' => $reponseRepository->findDisplay(),
        ]);
    }

    /**
     * @Route("/new/{id}", name="reponse_new", methods={"GET", "POST"})
     */
    public function new(UserRepository $userRepository,Request $request, EntityManagerInterface $entityManager,ThreadRepository $threadRepository,SessionInterface $session): Response
    {
        $reponse = new Reponse();
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);
        $thread_id = $request->get('id');
        $user =$userRepository->find($this->getUser()->getId());
        
        $thread = $threadRepository->find($thread_id);
        if ($form->isSubmitted() && $form->isValid()) {
            $reponse->setReplyDate(new \DateTime());
            $reponse->setDisplay(0);
            $reponse->setThread($thread);
            $reponse->setUser($user);
            $entityManager->persist($reponse);
            $entityManager->flush();

            return $this->redirectToRoute('thread_show',array('id' =>$thread_id), Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('thread_show', [
            'id' => $thread_id,
            'reponse' => $reponse,
            'form' => $form->createView(),

        ]);
    }

    /**
     * @Route("show/{id}", name="reponse_show", methods={"GET"})
     */
    public function show(Reponse $reponse): Response
    {
        return $this->render('reponse/show.html.twig', [
            'reponse' => $reponse,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="reponse_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Reponse $reponse, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('reponse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reponse/edit.html.twig', [
            'reponse' => $reponse,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("delete/{id}", name="reponse_delete", methods={"POST"})
     */
    public function delete(Request $request, Reponse $reponse, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reponse->getId(), $request->request->get('_token'))) {
            $reponse->setDisplay(1);
            $entityManager->persist($reponse);
            $entityManager->flush();
        }

        return $this->redirectToRoute('threads_admin', [], Response::HTTP_SEE_OTHER);
    }
     /**
     * @Route("/AllReponses/{id}", name="getReponses")
     */
    public function getAllReponses(NormalizerInterface $norm, ReponseRepository $reponseRepository,$id){
        $reponses = $reponseRepository->findByThreadDisplay($id);
        $jsonContent = $norm->normalize($reponses,'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }
    /**
     * @Route("/addReponse/{reply}/{id}/{id2}", name="addReponse")
     */
    public function addReponse($id2,ThreadRepository $threadRepository,ReponseRepository $reponseRepository,$id,$reply,EntityManagerInterface $entityManager,UserRepository $userRepository){
        $reponse = new Reponse();
        $reponse->setReply($reply);
        $reponse->setDisplay(0);
        $reponse->setUser($userRepository->find(1));
        $reponse->setReplyDate(new \DateTime());
        $reponse->setThread($threadRepository->find($id2));
        $user = $userRepository->find($id);
        $reponse->setUser($user);
        $entityManager->persist($reponse);
        $entityManager->flush();
        return new Response("202");
    }
    /**
     * @Route("/deleteReponse/{id}", name="delReponse")
     */
    public function deleteJSON($id,ReponseRepository $reponseRepository,EntityManagerInterface $entityManager){
        $reponse = $reponseRepository->find($id);
        $reponse->setDisplay(true);
        $entityManager->persist($reponse);
        $entityManager->flush();

        return new Response("202");
    }
}
