<?php

namespace App\Controller;

use App\Entity\ThreadType;
use App\Form\ThreadTypeType;
use App\Repository\ThreadTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/threadtype")
 */
class ThreadTypeController extends AbstractController
{
    /**
     * @Route("/", name="thread_type_index", methods={"GET"})
     */
    public function index(ThreadTypeRepository $threadTypeRepository): Response
    {
        
        return $this->render('thread_type/index.html.twig', [
            'thread_types' => $threadTypeRepository->findDisplay(),
        ]);
    }

    /**
     * @Route("/new", name="thread_type_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $threadType = new ThreadType();
        $form = $this->createForm(ThreadTypeType::class, $threadType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $threadType->setDisplay(0);
            $entityManager->persist($threadType);
            $entityManager->flush();

            return $this->redirectToRoute('thread_type_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('thread_type/new.html.twig', [
            'thread_type' => $threadType,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/display/{id}", name="thread_type_display", methods={"GET"})
     */
    public function delete(Request $request, ThreadType $thread, EntityManagerInterface $entityManager,ThreadTypeRepository $threadTypeRepository):Response
    {
        $thread->setDisplay(1);
        $entityManager->persist($thread);
        $entityManager->flush();
        
        return $this->redirectToRoute('thread_type_index', [], Response::HTTP_SEE_OTHER);
    
    }



    /**
     * @Route("/show/{id}", name="thread_type_show", methods={"GET"})
     */
    public function show(ThreadType $threadType): Response
    {
        return $this->render('thread_type/show.html.twig', [
            'thread_type' => $threadType,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="thread_type_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, ThreadType $threadType, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ThreadTypeType::class, $threadType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('thread_type_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('thread_type/edit.html.twig', [
            'thread_type' => $threadType,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/search/{id}", name="threadtype_search", methods={"GET", "POST"})
     */
    public function search(ThreadTypeRepository $threadTypeRepository,$id){
        $threadType= $threadTypeRepository->find($id);
        $threads = $threadTypeRepository->findThreads($id);
        return $this->render('thread_type/showThreads.html.twig', [
            'threadType' => $threadType,
            'threads' => $threads,
        ]);
    }
    
}
