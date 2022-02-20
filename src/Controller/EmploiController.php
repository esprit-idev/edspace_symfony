<?php

namespace App\Controller;
use App\Entity\Emploi;
use App\Repository\EmploiRepository;
use App\Form\EmploiFormType;
use App\Repository\PublicationNewsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class EmploiController extends AbstractController
{
    private $job_type = array('stage', 'emploi');
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
    public function allEmploi(EmploiRepository $repo): Response
    {
        
        $user=0;
        $templateName = 'emploi/back/allEmploi.html.twig';
        if($user == 1){
            $templateName = 'emploi/front/allEmploi_FO.html.twig';
        }
        $emplois = $repo->findAll();
        return $this->render($templateName, [
            'controller_name' => 'EmploiController',
            'emplois' => $emplois,
        ]);
    }

    #consulter une proposition 

    /**
     * @param $id
     * @Route("/oneemploi/{id}", name="oneEmploi")
     */
    public function OneEmploi($id, EmploiRepository $repo): Response
    {
        $user=0;
        $templateName = 'emploi/back/unEmploi.html.twig';
        if($user == 0){
            $templateName = 'emploi/front/unEmploi_FO.html.twig';
        }
        $emploi = $repo->find($id);
        return $this->render($templateName, [
            'emploi' => $emploi,
            'emploi_title' => $emploi->getTitle(),
            'emploi_date' =>$emploi->getDate(),
            'emploi_content' => $emploi->getContent(),
        ]);
    }

    # add emploi

    /**
     * @Route("/addemploi", name="addEmploi")
     */
    public function AddEmploi(Request $request, EmploiRepository $repo): Response
    {
        $emplois = $repo->findAll();
        $emploi = new Emploi();
        $form = $this->createForm(EmploiFormType::class,$emploi);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($emploi);
            $entityManager->flush();
            return $this->redirectToRoute('allemploi');
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
        $entityManager = $this->getDoctrine()->getManager();
        $emploi = $repo->find($id);
        $form = $this->createForm(EmploiFormType::class, $emploi);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $entityManager->flush();
            return $this->redirectToRoute('allemploi');
        }
        return $this->render('emploi/back/modifierEmploi.html.twig', [
            'form_title' => 'Modifier une publication',
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
        $entityManager = $this->getDoctrine()->getManager();
        $emploi = $repo->find($id);
        $entityManager->remove($emploi);
        $entityManager->flush();

        return $this->redirectToRoute('allemploi');

    }



}
