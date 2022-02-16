<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\Matiere;
use App\Form\DocumentType;
use App\Form\MatiereType;
use App\Repository\DocumentRepository;
use App\Repository\MatiereRepository;
use App\Repository\NiveauRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use \PDO;

class DocumentController extends AbstractController
{
    private $user=0;
    /**
     * @Route("/document", name="document")
     */
    public function index(): Response
    {
        return $this->render('document/index.html.twig', [
            'controller_name' => 'DocumentController',
        ]);
    }

    /**
     * @param DocumentRepository $repository
     * @return Response
     * @Route ("/document/listDocuments",name="listDocuments")
     */
    function ListDocuments(DocumentRepository $documentRepository,NiveauRepository $niveauRepository,MatiereRepository $matiereRepository){
        $documents=$documentRepository->findAll();
        $niveaux=$niveauRepository->findAll();
        $matieres=$matiereRepository->findAll();
        if($this->user==1){
            return $this->render("document/listDocumentsAgent.html.twig",['documents'=>$documents,'niveaux'=>$niveaux,'matieres'=>$matieres]);
        }else{
            return $this->render("document/listDocumentsEtudiant.html.twig",['documents'=>$documents,'niveaux'=>$niveaux,'matieres'=>$matieres]);
        }
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Route ("/document/ajoutDocument",name="ajoutDocument")
     */
    function AjoutDocument(Request $request,DocumentRepository $repository){
        $document=new Document();
        $form=$this->createForm(DocumentType::class,$document);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $fic=$document->getFichier();
            $nomFic=$document->getNom().'.'.$fic->guessExtension();
            //upload to public under documents
            $fic->move($this->getParameter('document_dir'),$nomFic);
            $document->setNom($nomFic);
            $document->setDateInsert(date("d/m/y"));
            $document->setProprietaire("Meriam2"); //get username
            $document->setFichier(file_get_contents($this->getParameter('document_dir').'/'.$nomFic));
            $document->setType(mime_content_type($this->getParameter('document_dir').'/'.$nomFic));
            $em=$this->getDoctrine()->getManager();
            $em->persist($document);
            $em->flush();
            return $this->redirectToRoute('ajoutDocument');
        }
        return $this->render("document/ajoutDocument.html.twig",["f"=>$form->createView()]);
    }
/*
    /**
     * @Route ("/document/renomDocument/{id}",name="renomDocument")
     */
    /*function ModifMatiere($id,DocumentRepository $repository,Request $request){
        $document=$repository->find($id);
        $form=$this->createForm(DocumentType::class,$document);
        $form->add("Renommer",SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em=$this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute('ajoutDocument');
        }
        return $this->render("document/renomDocument.html.twig",["f"=>$form->createView()]);
    }*/

    /**
     * @param $id
     * @param DocumentRepository $repository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route ("/document/suppDocument/{id}",name="suppDocument")
     */
    function SuppDocument($id,DocumentRepository $repository){
        $document=$repository->find($id);
        $em=$this->getDoctrine()->getManager();
        $em->remove($document);
        $em->flush();
        return $this->redirectToRoute('listDocuments');
    }

    /**
     * @Route("/afficheDocument/{id}", name="afficheDocument")
     */
    public function AfficheDocument($id,DocumentRepository $repository)
    {
        $document = $repository->find($id);
        if (!$document) {
            throw $this->createNotFoundException("File with ID $id does not exist!");
        }
        $fic = stream_get_contents($document->getFichier()); //returns file stored as mysql blob
        $response = new Response($fic, 200, array('Content-Type' => $document->getType()));
        return $response;
    }

    /**
     * @Route ("/document/triDocuments",name="triDocuments")
     */
    function TriDocument(DocumentRepository $documentRepository,NiveauRepository $niveauRepository,MatiereRepository $matiereRepository,Request $request){
        $niveaux=$niveauRepository->findAll();
        $niveau=$request->get('niveauKey');
        //echo $niveau;
        /*if($niveau!=null){
            $matieres=$matiereRepository->findBy(['niveau'=>$niveau]);
        }*/
        $matieres=$matiereRepository->findAll();
        $matiere=$request->get('matiereKey');
        //$documents=$documentRepository->findBy(array('niveau' => $niveau, 'matiere' => $matiere));
        $documents=$documentRepository->TriByNiveauMatiere($niveau,$matiere);
        if($this->user){
            return $this->render("document/listDocumentsAgent.html.twig",['documents'=>$documents,'niveaux'=>$niveaux,'matieres'=>$matieres]);
        } else{
            return $this->render("document/listDocumentsEtudiant.html.twig",['documents'=>$documents,'niveaux'=>$niveaux,'matieres'=>$matieres]);
        }
    }
}
