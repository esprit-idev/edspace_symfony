<?php

namespace App\Controller;
use App\Entity\Emploi;
use App\Repository\EmploiRepository;
use App\Form\EmploiFormType;
use App\Repository\CategorieEmploiRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Message;
use App\Entity\Classe;
use App\Entity\User;

class EmploiController extends AbstractController
{
    /**
     * @Route("/emploi", name="emploi")
     */
    public function index(): Response
    {
        return $this->render('emploi/back/index.html.twig', [
            'controller_name' => 'EmploiController',
        ]);
    }

  /**
     * @Route("/allemploi", name="allemploi")
     */
    public function allEmploi(EmploiRepository $repo, CategorieEmploiRepository $catRepo): Response
    {
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
            $templateName = 'emploi/front/allEmploi_FO.html.twig';
        }else{
            $templateName = 'emploi/back/allEmploi.html.twig';
        }
        $emplois = $repo->findAll();
        $categories = $catRepo->findAll();
        return $this->render($templateName, [
            'controller_name' => 'EmploiController',
            'emplois' => $emplois,
            'categories' => $categories,
            'user' => $user1,
            'classe'=> $classe,
            'message'=> $message,
            'mymsg' => $mymsg,
            'memebers'=> $memebers,
        ]);
    }

    #consulter une proposition 

    /**
     * @param $id
     * @Route("/oneemploi/{id}", name="oneEmploi")
     */
    public function OneEmploi($id, EmploiRepository $repo): Response
    {
        $emploi = $repo->find($id);
         //messages
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
             $templateName = 'emploi/front/unEmploi_FO.html.twig';
         }else{
             $templateName = 'emploi/back/unEmploi.html.twig';
         }
        return $this->render($templateName, [
            'emploi' => $emploi,
            'emploi_title' => $emploi->getTitle(),
            'emploi_date' =>$emploi->getDate(),
            'emploi_content' => $emploi->getContent(),
            'emploi_category' => $emploi->getCategoryName(),
            'emploi_image' => $emploi->getImage()->getName(),
            'user' => $user1,
            'classe'=> $classe,
            'message'=> $message,
            'mymsg' => $mymsg,
            'memebers'=> $memebers,
        ]);
    }

    # add emploi

    /**
     * @Route("/addemploi", name="addEmploi")
     */
    public function AddEmploi(Request $request, EmploiRepository $repo): Response
    {
        $hasAccessStudent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessStudent){
        $emplois = $repo->findAll();
        $emploi = new Emploi();
        $form = $this->createForm(EmploiFormType::class,$emploi);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $path = $this->getParameter('kernel.project_dir').'/public/images';
            $image = $emploi->getImage();
            /** @var UploadedFile $file */
            $file = $image->getFile();
            if(!empty($file)){
                $imageName = md5(uniqid()).'.'.$file->guessExtension();
                $file->move($path, $imageName);
                $image->setName($imageName);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($emploi);
            $entityManager->flush();
            return $this->redirectToRoute('allemploi');
        }
        }else{
            return new Response("Not authorized", 403);
        }
        return $this->render('emploi/back/addEmploi.html.twig', [
            'form_title' => 'Ajouter une proposition d\' emploi',
            'form_add' => $form->createView(),
            'emplois' => $emplois,
        ]);
    }

    # update emploi

    /**
     * @Route("/updateemploi/{id}", name="updateEmploi")
     */
    public function UpdateEmploi(Request $request, $id, EmploiRepository $repo): Response
    {
        $hasAccessStudent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessStudent){
        $entityManager = $this->getDoctrine()->getManager();
        $emploi = $repo->find($id);
        $form = $this->createForm(EmploiFormType::class, $emploi);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $path = $this->getParameter('kernel.project_dir').'/public/images';
            $image = $emploi->getImage();
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
            return $this->redirectToRoute('allemploi');
        }
        }else{
            return new Response("Not authorized", 403);
        }
        return $this->render('emploi/back/modifierEmploi.html.twig', [
            'form_title' => 'Modifier une publication d\'emploi',
            'form_add' => $form->createView(),
        ]);
    }

    #delete emploi

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/deleteemploi/{id}", name="deleteEmploi")
     */
    public function DeleteEmploi($id, EmploiRepository $repo): Response
    {
        $hasAccessStudent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessStudent){
        $entityManager = $this->getDoctrine()->getManager();
        $emploi = $repo->find($id);
        $entityManager->remove($emploi);
        $entityManager->flush();

        return $this->redirectToRoute('allemploi');
        }
        else{
            return new Response("Not authorized", 403);
        }
    }

    /**
     * @Route("/allemploi/search", name="searchEmploi")
     */
    public function searchPublication(Request $request,EmploiRepository $repo){

        $templateName = 'emploi/back/allEmploi.html.twig';
        $emplois = $repo->findAll();
        if($request->isMethod('POST')){
            $emploiTitle = $request->get('emploiTitle');
            if($emploiTitle !== ''){
                $emplois = $repo->SearchByTitle($emploiTitle);
            }
        }
        return $this->render($templateName, array('emplois' => $emplois));
    }

    /**
     * @Route("/allemploi/searchByCat", name="searchByEmploi")
     */
    public function searchPubByCategoryName(Request $request, EmploiRepository $repo, CategorieEmploiRepository $catRepo){
        $hasAccessStudent = $this->isGranted('ROLE_STUDENT');
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
                $templateName = 'emploi/front/allEmploi_FO.html.twig';
            }else{
                $templateName = 'emploi/back/allEmploi.html.twig';
            }
        $emplois = $repo->findAll();
        $categories = $catRepo->findAll();
        if($request->isMethod('POST')){
            $category = $request->get('categoryKey');
            $emplois = $repo->findNewsByCategory($category); 
        }
        return $this->render($templateName, array('emplois' => $emplois,'categories' => $categories,'user' => $user1,
        'classe'=> $classe,
        'message'=> $message,
        'mymsg' => $mymsg,
        'memebers'=> $memebers,)
    );
    }
}
