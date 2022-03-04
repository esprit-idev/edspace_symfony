<?php

namespace App\Controller;

use App\Entity\Thread;
use App\Form\ReponseType;
use App\Form\ThreadType;
use App\Repository\ThreadRepository;
use App\Repository\ThreadTypeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @Route("/thread")
 */
class ThreadController extends AbstractController
{
    /**
     * @Route("/", name="thread_index", methods={"GET"})
     */
    public function index(ThreadRepository $threadRepository,UserRepository $userRepository,SessionInterface $session,ThreadTypeRepository $threadTypeRepository):Response
    {
        $threadType = $threadTypeRepository->findByDisplay(0);
        $user = $userRepository->find($session->get('id'));
        return $this->render('thread/index.html.twig', [
            'threads' => $threadRepository->findDisplay(),
            'user' => $user,
            'threadType' => $threadType
            
        ]);
    }

    /**
     * @Route("/new", name="thread_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager,SessionInterface $session,UserRepository $userRepository): Response
    {
        $thread = new Thread();
        $form = $this->createForm(ThreadType::class, $thread);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $thread->setNbLikes(0);
            $thread->setPostDate(new \DateTime());
            $thread->setDisplay(0);
            $thread->setVerified(0);
            $user = $userRepository->find($session->get('id'));
            $thread->setUser($user);
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
        if($thread->getDisplay() == 0){
        $form = $this->createForm(ReponseType::class);
        $reponses = $threadRepository->getReponses($thread->getId());
        return $this->render('thread/show.html.twig', [
            'thread' => $thread,
            'reponses' => $reponses,
            'form' => $form->createView(),
        ]);
    }
    else {
        return $this->render('thread/404.html.twig');
    }
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
            'threadss' => $threadRepository->findByDisplay(1)
        ]);
    }
    /**
     * @Route("/json", name="json_admin", methods={"GET"})
     */
    public function JsonIndex(){
        $em = $this->getDoctrine()->getManager();
        $threads = $em->getRepository(Thread::class)->findBy(array('display' => '1'),array('postDate' =>'DESC'));
        
        $datas = array();
        foreach($threads as $key => $thread){
        $datas[$key]['id'] = $thread->getId();
        $datas[$key]['question'] = $thread->getQuestion();
        $datas[$key]['thread_type_id'] = $thread->getThreadType()->__toString();
        $datas[$key]['nb_likes'] = $thread->getNbLikes();
        $datas[$key]['post_date'] = $thread->getPostDate();
            
        }

        return new JsonResponse($datas);
    }

    /**
     * @Route("/add/{question}/{user}", name="json_add", methods={"GET","POST"})
     */
        public function addThreadJson($user,UserRepository $userRepository,ThreadTypeRepository $threadTypeRepository,$question){
            $thread = new Thread();
            $user = $userRepository->find($user);
            $thread->setQuestion($question);
            $thread->setDisplay(0);
            $threadType = $threadTypeRepository->find(1);
            $thread->setThreadType($threadType);
            $thread->setNbLikes(0);
            $thread->setUser($user);
            $thread->setVerified(0);
            $thread->setPostDate(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($thread);
            $em->flush();
            return new JsonResponse($thread);
            
        }


        /**
         * @Route("/myThread",name="thread_mine", methods={"GET"})
         */
        public function getThreads(ThreadRepository $threadRepository,SessionInterface $session,UserRepository $userRepository,ThreadTypeRepository $threadTypeRepository){
            $user = $userRepository->find($session->get('id'));
            $threadType = $threadTypeRepository->findByDisplay(0);
            $threads = $threadRepository->findByUser($user);
            dump($threads);
            
            //$threads= $threadRepository->findByUser
            return $this->render('thread/myThreads.html.twig', [
                'threads' => $threads,
                'user' => $user,
                'threadType' => $threadType
            ]);
        }

        /**
         * @Route("/changeuser/{id}",name="thread_change_user", methods={"GET"})
         */
        public function changeSession(SessionInterface $session,$id){
            $session->set('id',$id);
            dump($session);
            return new Response();
        }
        /**
         * @Route("/verify/{id}",name="thread_verify", methods={"GET"})
         */
        public function verify(ThreadRepository $threadRepository,$id){
            $thread = $threadRepository->find($id);
            $thread->setVerified(1);
            $em = $this->getDoctrine()->getManager();
            $em->persist($thread);
            $em->flush();
            $reponses = $threadRepository->getReponses($thread->getId());
        return $this->render('thread/adminThread.html.twig', [
            'thread' => $thread,
            'reponses' => $reponses
        ]);
        }

        /**
         * @Route("/reactivate/{id}",name="thread_reactivate", methods={"GET"})
         */
        public function reactivate(ThreadRepository $threadRepository,$id){
            $thread = $threadRepository->find($id);
            $thread->setDisplay(0);
            $em = $this->getDoctrine()->getManager();
            $em->persist($thread);
            $em->flush();
            $threads =$threadRepository->findDisplay();
            $threadss= $threadRepository->findByDisplay(1);
            return $this->render('thread/adminIndex.html.twig', [
                'threads' => $threads,
                'threadss' => $threadss,
            ]);
        }
}
