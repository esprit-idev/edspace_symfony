<?php

namespace App\Controller;

use App\Entity\Matiere;
use App\Form\MatiereType;
use App\Repository\MatiereRepository;
use App\Repository\NiveauRepository;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\DocumentRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;


class MatiereController extends AbstractController
{
    /**
     * @Route("/matiere", name="matiere")
     */
    public function index(): Response
    {
        return $this->render('matiere/index.html.twig', [
            'controller_name' => 'MatiereController',
        ]);
    }

    /**
     * @param $id
     * @param MatiereRepository $repository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route ("/matiere/suppMatiere/{id}",name="suppMatiere")
     */
    function SuppMatiere($id,MatiereRepository $repository,FlashyNotifier $notifier,DocumentRepository $documentRepository){
        $hasAccessAgent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessAgent) {
            $matiere = $repository->find($id);
            $document = $documentRepository->findOneBy(array("matiere" => $matiere));
            if ($document == null) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($matiere);
                $em->flush();
                $notifier->error("La matière a été supprimé!");
            } else {
                $notifier->error("Veuillez supprimer les documents concernés par cette matière!");
            }
            return $this->redirectToRoute('ajoutMatiere');
        } else{
            return $this->render('/403.html.twig');
        }
    }

    /**
     * @param Request $request
     * @return Response
     * @Route ("/matiere/ajoutMatiere",name="ajoutMatiere")
     */
    function AjoutMatiere(Request $request,MatiereRepository $repository,FlashyNotifier $notifier){
        $hasAccessAgent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessAgent) {
        $matieres=$repository->findAll();
        $matiere=new Matiere();
        $form=$this->createForm(MatiereType::class,$matiere);
        $form->add("Ajouter",SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em=$this->getDoctrine()->getManager();
            $em->persist($matiere);
            $em->flush();
            $notifier->success("Une matière a été ajoutée");
            return $this->redirectToRoute('ajoutMatiere');
        }
        return $this->render("matiere/ajoutMatiere.html.twig",["f"=>$form->createView(),'matieres'=>$matieres]);
        } else{
            return $this->render('/403.html.twig');        }
    }

    /**
     * @Route ("/matiere/modifMatiere/{id}",name="modifMatiere")
     */
    function ModifMatiere($id,MatiereRepository $repository,Request $request,FlashyNotifier $notifier){
        $hasAccessAgent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessAgent) {
        $matiere=$repository->find($id);
        $form=$this->createForm(MatiereType::class,$matiere);
        $form->add("Modifier",SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em=$this->getDoctrine()->getManager();
            $em->flush();
            $notifier->info("La matière a été modifiée");
            return $this->redirectToRoute('ajoutMatiere');
        }
        return $this->render("matiere/modifMatiere.html.twig",["f"=>$form->createView()]);
    } else{
            return $this->render('/403.html.twig');
        }
    }

    /**
     * @param NormalizerInterface $normalizer
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route ("/allMatieres",name="allMatieres")
     */
    function AllMatieresJSON(NormalizerInterface $normalizer, MatiereRepository $repository): Response
    {
        $matieres=$repository->findAll();
        $jsonContent=$normalizer->normalize($matieres,'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }

    /**
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route ("/addMatiere/new",name="addMatiere")
     */
    public function addMatiereJSON(NormalizerInterface $normalizer, Request $request,NiveauRepository $niveauRepository,MatiereRepository $matiereRepository):Response
    {
        $em = $this->getDoctrine()->getManager();
        $matiere = new Matiere();
        $matiere->setId($request->get('matiereId'));
        $niveau=$niveauRepository->find($request->get('niveauId'));
        $matiere->setNiveau($niveau);
        $em->persist($matiere);
        $em->flush();
        $jsonContent = $normalizer->normalize($matiere,'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }

    /**
     * @param NormalizerInterface $normalizer
     * @param $id
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route ("/deleteMatiere/{id}",name="deleteMatiere")
     */
    function DeleteMatiereJSON(NormalizerInterface $normalizer,$id): Response
    {
        $em=$this->getDoctrine()->getManager();
        $matiere=$em->getRepository(Matiere::class)->find($id);
        $em->remove($matiere);
        $em->flush();
        $jsonContent=$normalizer->normalize($matiere,'json',['groups'=>'post:read']);
        return new Response("Matiere deleted successfully".json_encode($jsonContent));
    }

    /**
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route ("/updateMatiere/{id}",name="updateMatiere")
     */
    public function updateMatiereJSON(NiveauRepository $niveauRepository,MatiereRepository $matiereRepository, NormalizerInterface $normalizer, Request $request,$id):Response
    {
        $em = $this->getDoctrine()->getManager();
        $matiere = $matiereRepository->find($id);
        $matiere->setId($request->get('matiereId'));
        $niveau=$niveauRepository->find($request->get('niveauId'));
        $matiere->setNiveau($niveau);
        $em->flush();

        $jsonContent = $normalizer->normalize($matiere,'json',['groups'=>'post:read']);
        return new Response("Matiere updated successfully".json_encode($jsonContent));
    }
}
