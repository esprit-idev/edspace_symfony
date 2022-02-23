<?php

namespace App\Controller;

use App\Entity\Thread;
use App\Form\ReponseType;
use App\Form\ThreadType;
use App\Repository\ThreadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/thread")
 */
class ThreadController extends AbstractController
{
    /**
     * @Route("/", name="thread_index", methods={"GET"})
     */
    public function index(ThreadRepository $threadRepository):Response
    {
        
        return $this->render('thread/index.html.twig', [
            'threads' => $threadRepository->findDisplay(),
            
        ]);
    }

    /**
     * @Route("/new", name="thread_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $thread = new Thread();
        $form = $this->createForm(ThreadType::class, $thread);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $thread->setNbLikes(0);
            $thread->setPostDate(new \DateTime());
            $thread->setDisplay(0);
            $entityManager->persist($thread);
            $entityManager->flush();

            return $this->redirectToRoute('thread_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('thread/new.html.twig', [
            'thread' => $thread,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/show/{id}", name="thread_show", methods={"POST","GET"})
     */
    public function show(Thread $thread,ThreadRepository $threadRepository): Response
    {
        $form = $this->createForm(ReponseType::class);
        $reponses = $threadRepository->getReponses($thread->getId());
        return $this->render('thread/show.html.twig', [
            'thread' => $thread,
            'reponses' => $reponses,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="thread_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Thread $thread, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ThreadType::class, $thread);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('thread_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('thread/edit.html.twig', [
            'thread' => $thread,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/display/{id}", name="thread_display", methods={"POST"})
     */
    public function delete(Request $request, Thread $thread, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$thread->getId(), $request->request->get('_token'))) {
            $thread->setDisplay(1);
            $entityManager->persist($thread);
            $entityManager->flush();
        }

        return $this->redirectToRoute('threads_admin', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/{id}/admin", name="thread_admin", methods={"GET"})
     */

    public function showAdmin(Thread $thread,ThreadRepository $threadRepository): Response
    {
        $reponses = $threadRepository->getReponses($thread->getId());
        return $this->render('thread/adminThread.html.twig', [
            'thread' => $thread,
            'reponses' => $reponses
        ]);
    }

    /**
     * @Route("/shows", name="threads_admin", methods={"GET"})
     */
    public function showAllThreads(ThreadRepository $threadRepository): Response
    {
        return $this->render('thread/adminIndex.html.twig', [
            'threads' => $threadRepository->findDisplay(),
        ]);
    }
}
