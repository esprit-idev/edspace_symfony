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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use \PDO;

class DocumentController extends AbstractController
{
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
     * @param NiveauRepository $niveauRepository
     * @param Request $request
     * @return Response
     * @Route ("/document/choixNiveau/{role}",name="choixNiveau")
     */
    function ChoixNiveau(NiveauRepository $niveauRepository,DocumentRepository $documentRepository,Request $request){
        $niveaux=$documentRepository->FindNiveaux();
        $documents=$documentRepository->findAll();
        $user=$request->get('role');
        if($user==0){
            return $this->render("document/choixNiveauAgent.html.twig",['niveaux'=>$niveaux,'documents'=>$documents]);
        }else{
            return $this->render("document/choixNiveauEtudiant.html.twig",['niveaux'=>$niveaux,'documents'=>$documents]);
        }
    }

    /**
     * @param DocumentRepository $repository
     * @return Response
     * @Route ("/document/listDocuments/{role}",name="listDocuments")
     */
    function ListDocuments(DocumentRepository $documentRepository,MatiereRepository $matiereRepository,Request $request){
        $niveau=$request->get('niveauKey');
        $matiere=$request->get('matiereKey');
        if($niveau==null){
            $document=$documentRepository->findOneBy(array('matiere'=>$matiere));
            if($document!=null){
                $niveau=$document->getNiveau();
            }
        }
        //$matieres=$matiereRepository->findBy(array('niveau'=>$niveau));
        $matieres=$documentRepository->FindMatieres($niveau);
        if ($matiere!=null){
            $documents=$documentRepository->findBy(array('niveau'=>$niveau,'matiere'=>$matiere));
        } else{
            $document=$documentRepository->findOneBy(array('niveau'=>$niveau));
            if($document!=null) {
                $matiere = $document->getMatiere();
            }
            $documents=$documentRepository->findBy(array('niveau'=>$niveau));
        }
        $user = $request->get('role');
        if ($user == 0) {
            return $this->render("document/listDocumentsAgent.html.twig", ['documents' => $documents, 'matieres' => $matieres]);
        } else {
            return $this->render("document/listDocumentsEtudiant.html.twig", ['documents' => $documents, 'matieres' => $matieres]);
        }

    }
    /*
    /**
     * @Route ("/document/triDocuments/{role}",name="triDocuments")
     */
    /*
    function TriDocument(DocumentRepository $documentRepository,MatiereRepository $matiereRepository,Request $request){
        $matiere=$request->get('matiereKey');
        $documents=$documentRepository->findBy(array('matiere' => $matiere));
        $niveau=$request->get('niveauKey');
        $matieres=$matiereRepository->findBy(array('niveau'=>$niveau));
        //$documents=$documentRepository->TriByNiveauMatiere($matiere);
        $user=$request->get('role');
        if($user==0){
            return $this->render("document/listDocumentsAgent.html.twig",['documents'=>$documents,'matieres'=>$matieres]);
        } else{
            return $this->render("document/listDocumentsEtudiant.html.twig",['documents'=>$documents,'matieres'=>$matieres]);
        }
    }*/


    /**
     * @param NiveauRepository $niveauRepository
     * @return Response
     * @Route ("/document/choixNiveauAjout",name="choixNiveauAjout")
     */
    function ChoixNiveauAjout(NiveauRepository $niveauRepository){
        $niveaux=$niveauRepository->findAll();
        return $this->render("document/choixNiveauAjout.html.twig",['niveaux'=>$niveaux]);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Route ("/document/ajoutDocument",name="ajoutDocument")
     */
    function AjoutDocument(Request $request,NiveauRepository $repository){
        //$niveau=$request->get('niveauKey');
        //$matieres=$repository->FindMatieres($niveau);
        //$niv=$repository->find($niveau);
        //$matieres=$niv->getMatieres();
        $document=new Document();
        $form=$this->createForm(DocumentType::class,$document);
        //$form->add("niveau",TextareaType::class,['label'=> 'Niveau sélectionné','data'=>$niveau]);
        //$form->add("matiere",ChoiceType::class,['label'=> 'Choisissez une matière ','choice_label'=>'id','choices'=>$matieres,'placeholder'=>'-- Sélectionnez une matière --']);
        $form->add("Ajouter",SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $fic=$document->getFichier();
            $nomFic=$document->getNom().'.'.$fic->guessExtension();
            //upload to public under documents
            $fic->move($this->getParameter('document_dir'),$nomFic);
            //$document->setNiveau($niveau);
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

    /**
     * @Route ("/document/modifDocument/{id}",name="modifDocument")
     */
    function ModifDocument($id,DocumentRepository $repository,Request $request){
        $document=$repository->find($id);
        $nom=$document->getNom();
        $fic=$document->getFichier();
        $form=$this->createForm(DocumentType::class,$document);
        $form->add("Modifier",SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em=$this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute('choixNiveau',["role"=>1]);
        }
        return $this->render("document/modifDocument.html.twig",["f"=>$form->createView()]);
    }

    /**
     * @param $id
     * @param DocumentRepository $repository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route ("/document/suppDocument/{id}/{role}",name="suppDocument")
     */
    function SuppDocument($id,DocumentRepository $repository,Request $request){
        $document=$repository->find($id);
        $em=$this->getDoctrine()->getManager();
        $em->remove($document);
        $em->flush();
        $user=$request->get('role');
        return $this->redirectToRoute('choixNiveau',["role"=>$user]);
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
}
