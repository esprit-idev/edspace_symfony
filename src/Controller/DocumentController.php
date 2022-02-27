<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\DocumentFavoris;
use App\Entity\Matiere;
use App\Form\DocumentType;
use App\Form\MatiereType;
use App\Repository\DocumentFavorisRepository;
use App\Repository\DocumentRepository;
use App\Repository\MatiereRepository;
use App\Repository\NiveauRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\MailerServices;
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
    function ChoixNiveau(DocumentRepository $documentRepository,Request $request,DocumentFavorisRepository $documentFavorisRepository){
        $niveaux=$documentRepository->FindNiveaux();
        $documents=$documentRepository->findAll();
        $favoris=$documentFavorisRepository->findAll();
        $user=$request->get('role');
        if($user==0){
            return $this->render("document/choixNiveauAgent.html.twig",['niveaux'=>$niveaux,'documents'=>$documents]);
        }else{
            return $this->render("document/choixNiveauEtudiant.html.twig",['niveaux'=>$niveaux,'documents'=>$documents,'favoris'=>$favoris]);
        }
    }

    /**
     * @param DocumentRepository $repository
     * @return Response
     * @Route ("/document/listDocuments/{role}",name="listDocuments")
     */
    function ListDocuments(DocumentRepository $documentRepository,Request $request,DocumentFavorisRepository $documentFavorisRepository){
        $favoris=$documentFavorisRepository->findAll();
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
            return $this->render("document/listDocumentsEtudiant.html.twig", ['documents' => $documents, 'matieres' => $matieres,'favoris'=>$favoris]);
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
            $document->setProprietaire("Meriam2"); //get username //to-change
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
     * @param $id
     * @param DocumentRepository $repository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route ("/document/suppDocumentSiganles/{id}",name="suppDocumentSiganles")
     */
    function SuppDocumentSignale($id,DocumentRepository $repository,Request $request){
        $document=$repository->find($id);
        $em=$this->getDoctrine()->getManager();
        $em->remove($document);
        $em->flush();
        $user=$request->get('role');
        return $this->redirectToRoute('listDocumentsSignales');
    }

    /**
     * @Route("/afficheDocument/{id}", name="afficheDocument")
     */
    function AfficheDocument($id,DocumentRepository $repository)
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
     * @param DocumentRepository $documentRepository
     * @return Response
     * @Route ("/document/mesDoc", name="mesDoc")
     */
    function AfficheMesDocuments(DocumentRepository $documentRepository){
        $prop="Meriam2"; //to-change
        $documents=$documentRepository->findBy(array('proprietaire' => $prop));
        return $this->render("document/mesDoc.html.twig", ['documents' => $documents]);
    }

    /**
     * @param DocumentRepository $documentRepository
     * @param UserRepository $userRepository
     * @param DocumentFavorisRepository $documentFavorisRepository
     * @return Response
     * @Route ("/document/mesFavoris",name="mesFavoris")
     */
    function AfficheMesFavoris(DocumentRepository $documentRepository,UserRepository $userRepository,DocumentFavorisRepository $documentFavorisRepository){
        $user=$userRepository->find(2); //to-change
        $docsInFav=$documentFavorisRepository->findBy(array('user'=>$user));
        return $this->render("document/mesFavoris.html.twig", ['docsInFav' => $docsInFav]);
    }



    /**
     * @param $id
     * @param DocumentRepository $repository
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route ("/document/signalDoc/{id}/{role}",name="signalDoc")
     */
    function SignlaDoc($id,DocumentRepository $repository,Request $request){
        $document=$repository->find($id);
        $repository->IncrementCountSignal($document);
        $user=$request->get('role');
        return $this->redirectToRoute('choixNiveau',["role"=>$user]);
    }

    /**
     * @param DocumentRepository $repository
     * @return Response
     * @Route ("/document/listDocumentsSignales",name="listDocumentsSignales")
     */
    function DocumentsSignales(DocumentRepository $repository){
        $documents=$repository->FindDocSignales();
        return $this->render("document/listDocumentsSignales.html.twig", ['documents' => $documents]);
    }


    /**
     * @param $id
     * @param DocumentRepository $repository
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route ("/document/ignorerSignalDoc/{id}",name="ignorerSignalDoc")
     */
    function IgnorerSignalDoc($id,DocumentRepository $repository,Request $request){
        $document=$repository->find($id);
        $repository->DecrementCountSignal($document);
        return $this->redirectToRoute('listDocumentsSignales');
    }

    /**
     * @param $id
     * @param DocumentRepository $documentRepository
     * @param UserRepository $userRepository
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route ("/document/pinDoc/{id}/{role}",name="pinDoc")
     */
    function PinDocument($id,DocumentRepository $documentRepository,UserRepository $userRepository,Request $request,DocumentFavorisRepository $documentFavorisRepository){
        $document=$documentRepository->find($id);
        $user=$userRepository->find(2); //to-change
        $docFavoris=new DocumentFavoris();
        $docFavoris->setDocument($document);
        $docFavoris->setUser($user);
        $em=$this->getDoctrine()->getManager();
        $em->persist($docFavoris);
        $em->flush();
        $user=$request->get('role');
        return $this->redirectToRoute('choixNiveau',["role"=>$user]);
    }

    /**
     * @param $id
     * @param DocumentRepository $documentRepository
     * @param UserRepository $userRepository
     * @param Request $request
     * @param DocumentFavorisRepository $documentFavorisRepository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route ("/document/unPinDoc/{id}/{role}",name="unPinDoc")
     */
    function UnPinDocFromList($id,DocumentRepository $documentRepository,UserRepository $userRepository,Request $request,DocumentFavorisRepository $documentFavorisRepository){
        $this->UnPinDoc($id,$documentRepository,$userRepository,$request,$documentFavorisRepository);
        $user=$request->get('role');
        return $this->redirectToRoute('choixNiveau',["role"=>$user]);
    }
    /**
     * @param $id
     * @param DocumentRepository $documentRepository
     * @param UserRepository $userRepository
     * @param Request $request
     * @param DocumentFavorisRepository $documentFavorisRepository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route ("/document/unPinDocFavoris/{id}/{role}",name="unPinDocFavoris")
     */
    function UnPinDocFromFav($id,DocumentRepository $documentRepository,UserRepository $userRepository,Request $request,DocumentFavorisRepository $documentFavorisRepository){
       $this->UnPinDoc($id,$documentRepository,$userRepository,$request,$documentFavorisRepository);
        return $this->redirectToRoute('mesFavoris');
    }

    function UnPinDoc($id,DocumentRepository $documentRepository,UserRepository $userRepository,Request $request,DocumentFavorisRepository $documentFavorisRepository){
        $userid=$userRepository->find(2); //to-change
        $document=$documentRepository->find($id);
        $docInFav=$documentFavorisRepository->findOneBy(array('document'=>$document,'user'=>$userid));
        $em=$this->getDoctrine()->getManager();
        $em->remove($docInFav);
        $em->flush();
    }

    /**
     * @param Request $request
     * @param MailerServices $mailerServices
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Route ("/document/shareDoc/{id}",name="shareDoc")
     */
    public function ShareDoc($id,DocumentRepository $documentRepository, MailerServices $mailerServices) {
            $document=$documentRepository->find($id);
            $fic=$document->getFichier();
            $emailreceptor="meriam.benida@esprit.tn";
            $subject="this is the subject";
            $texto="this is the body";
            $mailerServices->sendEmail($emailreceptor,$subject, $texto);
        return $this->render(
            'document/shareDoc.html.twig',
            array(
                'destination' => $emailreceptor,
                'subject' => $subject
            )
        );
    }

}
