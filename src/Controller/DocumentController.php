<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\DocumentFavoris;
use App\Entity\Matiere;
use App\Form\DocShareType;
use App\Form\DocumentType;
use App\Form\WebPdfType;
use App\Form\MatiereType;
use App\Form\ModifDocumentType;
use App\Repository\DocumentFavorisRepository;
use App\Repository\DocumentRepository;
use App\Repository\MatiereRepository;
use App\Repository\NiveauRepository;
use App\Repository\UserRepository;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use MercurySeries\FlashyBundle\FlashyNotifier;
use PhpParser\Comment\Doc;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

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
     * @Route ("/document/choixNiveau",name="choixNiveau")
     */
    function ChoixNiveau(DocumentRepository $documentRepository,Request $request,DocumentFavorisRepository $documentFavorisRepository){
        $niveaux=$documentRepository->FindNiveaux();
        $documents=$documentRepository->findAll();
        $favoris=$documentFavorisRepository->findAll();
        $hasAccessAgent = $this->isGranted('ROLE_ADMIN');
        $hasAccessStudent = $this->isGranted('ROLE_STUDENT');
        if($hasAccessAgent){
            return $this->render("document/choixNiveauAgent.html.twig",['niveaux'=>$niveaux,'documents'=>$documents]);
        }elseif ($hasAccessStudent){
            return $this->render("document/choixNiveauEtudiant.html.twig",['niveaux'=>$niveaux,'documents'=>$documents,'favoris'=>$favoris]);
        } else{
            return new Response(null, 403);
        }
    }

    /**
     * @param DocumentRepository $repository
     * @return Response
     * @Route ("/document/listDocuments",name="listDocuments")
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
        $hasAccessAgent = $this->isGranted('ROLE_ADMIN');
        $hasAccessStudent = $this->isGranted('ROLE_STUDENT');
        if($hasAccessAgent){
            return $this->render("document/listDocumentsAgent.html.twig", ['documents' => $documents, 'matieres' => $matieres]);
        } elseif($hasAccessStudent) {
            return $this->render("document/listDocumentsEtudiant.html.twig", ['documents' => $documents, 'matieres' => $matieres,'favoris'=>$favoris]);
        } else{
            return new Response(null, 403);
        }
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Route ("/document/ajoutDocument",name="ajoutDocument")
     */
    function AjoutDocument(Request $request,FlashyNotifier $notifier) : Response
    {
        $userEmail=$this->getUser()->getEmail();
        $username=$this->getUser()->getUsername();
        $userPrenom=$this->getUser()->getPrenom();
        $prop=$username." ".$userPrenom;
        $hasAccessStudent= $this->isGranted('ROLE_STUDENT');
        if($hasAccessStudent) {
            //$niveau=$request->get('niveauKey');
            //$matieres=$repository->FindMatieres($niveau);
            //$niv=$repository->find($niveau);
            //$matieres=$niv->getMatieres();
            $document = new Document();
            $form = $this->createForm(DocumentType::class, $document);
            //$form->add("niveau",TextareaType::class,['label'=> 'Niveau sélectionné','data'=>$niveau]);
            //$form->add("matiere",ChoiceType::class,['label'=> 'Choisissez une matière ','choice_label'=>'id','choices'=>$matieres,'placeholder'=>'-- Sélectionnez une matière --']);
            $form->add("Ajouter", SubmitType::class);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $fic = $document->getFichier();
                $nomFic = $document->getNom() . '.' . $fic->guessExtension();
                //upload to public under documents
                $fic->move($this->getParameter('document_dir'), $nomFic);
                //$document->setNiveau($niveau);
                $document->setNom($nomFic);
                $document->setDateInsert(date("d/m/y"));
                $document->setProprietaire($prop); //get username //to-change
                $document->setFichier(file_get_contents($this->getParameter('document_dir') . '/' . $nomFic));
                $document->setType(mime_content_type($this->getParameter('document_dir') . '/' . $nomFic));
                $document->setSignalements(0);
                $document->setUrl(null);
                $em = $this->getDoctrine()->getManager();
                $em->persist($document);
                $em->flush();
                $notifier->success("Votre document a été ajouté");
                return $this->redirectToRoute('choixNiveau');
            }
            return $this->render("document/ajoutDocument.html.twig", ["f" => $form->createView()]);
        }else{
            return new Response(null, 403);
        }
    }


    /**
     * @param Request $request
     * @param FlashyNotifier $notifier
     * @return mixed
     * @Route ("/document/ajoutWebPdf",name="ajoutWebPdf")
     */
    function AjoutWebPDF(Request $request, FlashyNotifier $notifier){
        $username=$this->getUser()->getUsername();
        $userPrenom=$this->getUser()->getPrenom();
        $prop=$username." ".$userPrenom;
        $hasAccessStudent= $this->isGranted('ROLE_STUDENT');
        if($hasAccessStudent) {
            $document = new Document();
            $form = $this->createForm(WebPdfType::class, $document);
            $form->add("Ajouter", SubmitType::class);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $nomFic = $document->getNom() . '.pdf';
                $document->setNom($nomFic);
                $document->setDateInsert(date("d/m/y"));
                $document->setProprietaire($prop); //get username //to-change
                $document->setType("application/pdf");
                $document->setSignalements(0);
                $document->setFichier(null);
                $em = $this->getDoctrine()->getManager();
                $em->persist($document);
                $em->flush();
                $notifier->success("L'URL a été ajouté");
                return $this->redirectToRoute('choixNiveau');
            }
            return $this->render("document/ajoutWebPdf.html.twig", ["f" => $form->createView()]);
        } else{
                return new Response(null, 403);
            }
    }

    /**
     * @Route ("/document/modifDocument/{id}",name="modifDocument")
     */
    function ModifDocument($id,DocumentRepository $repository,Request $request,FlashyNotifier $notifier){
        $hasAccessStudent= $this->isGranted('ROLE_STUDENT');
        if($hasAccessStudent) {
        $document=$repository->find($id);
        $form=$this->createForm(ModifDocumentType::class,$document);
        $form->add("Modifier",SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em=$this->getDoctrine()->getManager();
            $em->flush();
            $notifier->info("Votre document a été modifié");
            return $this->redirectToRoute('choixNiveau');
        }
        return $this->render("document/modifDocument.html.twig",["f"=>$form->createView()]);
        } else{
            return new Response(null, 403);
        }
    }

    /**
     * @Route ("/document/modifDocumentMine/{id}",name="modifDocumentMine")
     */
    function ModifDocumentMine($id,DocumentRepository $repository,Request $request,FlashyNotifier $notifier){
        $hasAccessStudent= $this->isGranted('ROLE_STUDENT');
        if($hasAccessStudent) {
        $document=$repository->find($id);
        $form=$this->createForm(ModifDocumentType::class,$document);
        $form->add("Modifier",SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em=$this->getDoctrine()->getManager();
            $em->flush();
            $notifier->info("Votre document a été modifié");
            return $this->redirectToRoute('mesDoc');
        }
        return $this->render("document/modifDocument.html.twig",["f"=>$form->createView()]);
        } else{
            return new Response(null, 403);
        }
    }

    /**
     * @Route ("/document/modifDocumentFavoris/{id}",name="modifDocumentFavoris")
     */
    function ModifDocumentFavoris($id,DocumentRepository $repository,Request $request,FlashyNotifier $notifier){
        $hasAccessStudent= $this->isGranted('ROLE_STUDENT');
        if($hasAccessStudent) {
        $document=$repository->find($id);
        $form=$this->createForm(ModifDocumentType::class,$document);
        $form->add("Modifier",SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em=$this->getDoctrine()->getManager();
            $em->flush();
            $notifier->info("Votre document a été modifié");
            return $this->redirectToRoute('mesFavoris');
        }
        return $this->render("document/modifDocument.html.twig",["f"=>$form->createView()]);
        } else{
            return new Response(null, 403);
        }
    }

    /**
     * @param $id
     * @param DocumentRepository $repository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route ("/document/suppDocument/{id}",name="suppDocument")
     */
    function SuppDocument($id,DocumentRepository $repository,Request $request,FlashyNotifier $notifier){
        $hasAccessAgent = $this->isGranted('ROLE_ADMIN');
        $hasAccessStudent = $this->isGranted('ROLE_STUDENT');
        if($hasAccessAgent or $hasAccessStudent) {
            $this->DelDoc($id, $repository);
            $notifier->error("Votre document a été supprimé!");
            return $this->redirectToRoute('choixNiveau');
        } else{
            return new Response(null, 403);
            }
    }

    /**
     * @param $id
     * @param DocumentRepository $repository
     * @param FlashyNotifier $notifier
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route ("/document/suppDocumentMine/{id}",name="suppDocumentMine")
     */
    function SuppDocumentMine($id,DocumentRepository $repository,FlashyNotifier $notifier){
        $hasAccessStudent= $this->isGranted('ROLE_STUDENT');
        if($hasAccessStudent) {
        $this->DelDoc($id,$repository);
        $notifier->error("Votre document a été supprimé!");
        return $this->redirectToRoute('mesDoc');
        } else{
            return new Response(null, 403);
        }

    }

    /**
     * @param $id
     * @param DocumentRepository $repository
     * @param FlashyNotifier $notifier
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route ("/document/suppDocumentFavoris/{id}",name="suppDocumentFavoris")
     */
    function SuppDocumentFavoris($id,DocumentRepository $repository,FlashyNotifier $notifier){
        $hasAccessStudent= $this->isGranted('ROLE_STUDENT');
        if($hasAccessStudent) {
        $this->DelDoc($id,$repository);
        $notifier->error("Votre document a été supprimé!");
        return $this->redirectToRoute('mesFavoris');
        } else{
            return new Response(null, 403);
        }
    }

    function DelDoc($id,DocumentRepository $repository){
        $document=$repository->find($id);
        $em=$this->getDoctrine()->getManager();
        $em->remove($document);
        $em->flush();
    }

    /**
     * @Route ("/document/apercuDocument/{id}",name="apercuDocument")
     */
    function ApercuDocument($id,DocumentRepository $repository){
        $hasAccessAgent = $this->isGranted('ROLE_ADMIN');
        $hasAccessStudent = $this->isGranted('ROLE_STUDENT');
        if($hasAccessAgent or $hasAccessStudent) {
        $document=$repository->find($id);
        return $this->render("document/apercuDocument.html.twig",["document"=>$document]);
        } else{
            return new Response(null, 403);
        }
    }

    /**
     * @Route ("/document/apercuUrl/{id}",name="apercuUrl")
     */
    function ApercuUrl($id,DocumentRepository $repository,\Knp\Snappy\Pdf $pdf){
        $hasAccessAgent = $this->isGranted('ROLE_ADMIN');
        $hasAccessStudent = $this->isGranted('ROLE_STUDENT');
        if($hasAccessAgent or $hasAccessStudent) {
        $document=$repository->find($id);
        $filename = 'myFirstSnappyPDF';
        $url = $document->getUrl();
        return new Response(
            $pdf->getOutput($url),
            200,
            array(
                'Content-Type'          => 'application/pdf',
                'Content-Disposition'   => 'inline; filename="'.$filename.'.pdf"'
            )
        );
        } else{
            return new Response(null, 403);
        }
    }
    /**
     * @param $id
     * @param DocumentRepository $repository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route ("/document/suppDocumentSignales/{id}",name="suppDocumentSignales")
     */
    function SuppDocumentSignale(FlashyNotifier $notifier,$id,DocumentRepository $repository){
        $hasAccessAgent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessAgent){
        $document=$repository->find($id);
        $em=$this->getDoctrine()->getManager();
        $em->remove($document);
        $em->flush();
        $notifier->error("Le document a été supprimé!");
        return $this->redirectToRoute('listDocumentsSignales');
        } else{
            return new Response(null, 403);
        }
    }

    /**
     * @param DocumentRepository $documentRepository
     * @return Response
     * @Route ("/document/mesDoc", name="mesDoc")
     */
    function AfficheMesDocuments(DocumentRepository $documentRepository,Request $request){
        $username=$this->getUser()->getUsername();
        $userPrenom=$this->getUser()->getPrenom();
        $prop=$username." ".$userPrenom;
        $hasAccessStudent= $this->isGranted('ROLE_STUDENT');
        if($hasAccessStudent) {
        $type=$request->get('typeKey');
        if($type){
            if($type=="Tous les types") $docType="tous";
            elseif($type=="PDF") $docType="application/pdf";
            elseif($type=="Office") $docType="officedocument";
            elseif($type==".rar") $docType="application/x-rar";
            elseif($type==".zip") $docType="application/zip";
            elseif($type=="Image") $docType="image";
            elseif($type=="Autres") $docType="autres";
            else $docType="url";
            $documents=$documentRepository->FindDocByType($prop,$docType);
        } else{
            $documents=$documentRepository->findBy(array('proprietaire' => $prop));
        }
        return $this->render("document/mesDoc.html.twig", ['documents' => $documents]);
        } else{
            return new Response(null, 403);
        }
    }

    /**
     * @param DocumentRepository $documentRepository
     * @param UserRepository $userRepository
     * @param DocumentFavorisRepository $documentFavorisRepository
     * @return Response
     * @Route ("/document/mesFavoris",name="mesFavoris")
     */
    function AfficheMesFavoris(UserRepository $userRepository,DocumentFavorisRepository $documentFavorisRepository){
        $hasAccessStudent= $this->isGranted('ROLE_STUDENT');
        if($hasAccessStudent) {
        $user=$userRepository->find(2); //to-change
        $docsInFav=$documentFavorisRepository->findBy(array('user'=>$user));
        return $this->render("document/mesFavoris.html.twig", ['docsInFav' => $docsInFav]);
        } else{
            return new Response(null, 403);
        }
    }

    /**
     * @param $id
     * @param DocumentRepository $repository
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route ("/document/signalDoc/{id}",name="signalDoc")
     */
    function SignalDoc(FlashyNotifier $notifier,$id,DocumentRepository $repository){
        $hasAccessStudent= $this->isGranted('ROLE_STUDENT');
        if($hasAccessStudent) {
        $this->ReportDoc($id,$repository);
        $notifier->warning("Le document a été signalé!");
        return $this->redirectToRoute('choixNiveau');
        } else{
            return new Response(null, 403);
        }
    }

    /**
     * @param FlashyNotifier $notifier
     * @param $id
     * @param DocumentRepository $repository
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route ("/document/signalDocFavoris/{id}",name="signalDocFavoris")
     */
    function SignalDocFavoris(FlashyNotifier $notifier,$id,DocumentRepository $repository){
        $hasAccessStudent= $this->isGranted('ROLE_STUDENT');
        if($hasAccessStudent) {
        $this->ReportDoc($id,$repository);
        $notifier->warning("Le document a été signalé!");
        return $this->redirectToRoute('mesFavoris');
        } else{
            return new Response(null, 403);
        }
    }

    function ReportDoc($id,DocumentRepository $repository){
        $document=$repository->find($id);
        $repository->IncrementCountSignal($document);
    }

    /**
     * @param DocumentRepository $repository
     * @return Response
     * @Route ("/document/listDocumentsSignales",name="listDocumentsSignales")
     */
    function DocumentsSignales(DocumentRepository $repository){
        $hasAccessAgent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessAgent) {
        $documents=$repository->FindDocSignales();
        return $this->render("document/listDocumentsSignales.html.twig", ['documents' => $documents]);
        } else{
            return new Response(null, 403);
        }
    }


    /**
     * @param $id
     * @param DocumentRepository $repository
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route ("/document/ignorerSignalDoc/{id}",name="ignorerSignalDoc")
     */
    function IgnorerSignalDoc(FlashyNotifier $notifier,$id,DocumentRepository $repository){
        $hasAccessAgent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessAgent) {
        $document=$repository->find($id);
        $repository->DecrementCountSignal($document);
        $notifier->info("Signal ignoré!");
        return $this->redirectToRoute('listDocumentsSignales');
        } else{
            return new Response(null, 403);
        }
    }

    /**
     * @param $id
     * @param DocumentRepository $documentRepository
     * @param UserRepository $userRepository
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route ("/document/pinDoc/{id}",name="pinDoc")
     */
    function PinDocument(FlashyNotifier $notifier,$id,DocumentRepository $documentRepository,UserRepository $userRepository,Request $request,DocumentFavorisRepository $documentFavorisRepository){
        $hasAccessStudent= $this->isGranted('ROLE_STUDENT');
        if($hasAccessStudent) {
        $document=$documentRepository->find($id);
        $user=$userRepository->find(2); //to-change
        $docFavoris=new DocumentFavoris();
        $docFavoris->setDocument($document);
        $docFavoris->setUser($user);
        $em=$this->getDoctrine()->getManager();
        $em->persist($docFavoris);
        $em->flush();
        $notifier->primary("Document ajouté aux favoris!");
        return $this->redirectToRoute('choixNiveau');
        } else{
            return new Response(null, 403);
        }
    }

    /**
     * @param $id
     * @param DocumentRepository $documentRepository
     * @param UserRepository $userRepository
     * @param Request $request
     * @param DocumentFavorisRepository $documentFavorisRepository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route ("/document/unPinDoc/{id}",name="unPinDoc")
     */
    function UnPinDocFromList(FlashyNotifier $notifier,$id,DocumentRepository $documentRepository,UserRepository $userRepository,Request $request,DocumentFavorisRepository $documentFavorisRepository){
        $hasAccessStudent= $this->isGranted('ROLE_STUDENT');
        if($hasAccessStudent) {
        $this->UnPinDoc($id,$documentRepository,$userRepository,$documentFavorisRepository);
        $notifier->primary("Document supprimé des favoris!");
        return $this->redirectToRoute('choixNiveau');
        } else{
            return new Response(null, 403);
        }
    }
    /**
     * @param $id
     * @param DocumentRepository $documentRepository
     * @param UserRepository $userRepository
     * @param Request $request
     * @param DocumentFavorisRepository $documentFavorisRepository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route ("/document/unPinDocFavoris/{id}",name="unPinDocFavoris")
     */
    function UnPinDocFromFav(FlashyNotifier $notifier,$id,DocumentRepository $documentRepository,UserRepository $userRepository,Request $request,DocumentFavorisRepository $documentFavorisRepository){
        $hasAccessStudent= $this->isGranted('ROLE_STUDENT');
        if($hasAccessStudent) {
        $this->UnPinDoc($id,$documentRepository,$userRepository,$request,$documentFavorisRepository);
        $notifier->primary("Document supprimé des favoris!");
        return $this->redirectToRoute('mesFavoris');
        } else{
            return new Response(null, 403);
        }
    }

    function UnPinDoc($id,DocumentRepository $documentRepository,UserRepository $userRepository,DocumentFavorisRepository $documentFavorisRepository){
        $userid=$userRepository->find(2); //to-change
        $document=$documentRepository->find($id);
        $docInFav=$documentFavorisRepository->findOneBy(array('document'=>$document,'user'=>$userid));
        $em=$this->getDoctrine()->getManager();
        $em->remove($docInFav);
        $em->flush();
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Route ("/document/shareDoc/{id}",name="shareDoc")
     */
    public function ShareDoc(Request $request,$id,DocumentRepository $documentRepository,\Swift_Mailer $mailer,FlashyNotifier $notifier) {
        $hasAccessStudent= $this->isGranted('ROLE_STUDENT');
        if($hasAccessStudent) {
        return $this->ShareDocUrl($request,$id,$documentRepository,$mailer,$notifier,0);
        } else{
            return new Response(null, 403);
        }
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Route ("/document/shareDocMine/{id}",name="shareDocMine")
     */
    public function ShareDocMine(Request $request,$id,DocumentRepository $documentRepository,\Swift_Mailer $mailer,FlashyNotifier $notifier) {
        $hasAccessStudent= $this->isGranted('ROLE_STUDENT');
        if($hasAccessStudent) {
        return $this->ShareDocUrl($request,$id,$documentRepository,$mailer,$notifier,1);
        } else{
            return new Response(null, 403);
        }
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Route ("/document/shareDocFavoris/{id}",name="shareDocFavoris")
     */
    public function ShareDocFavoris(Request $request,$id,DocumentRepository $documentRepository,\Swift_Mailer $mailer,FlashyNotifier $notifier) {
        $hasAccessStudent= $this->isGranted('ROLE_STUDENT');
        if($hasAccessStudent) {
        return $this->ShareDocUrl($request,$id,$documentRepository,$mailer,$notifier,2);
        } else{
            return new Response(null, 403);
        }
    }

    public function ShareDocUrl(Request $request,$id,DocumentRepository $documentRepository,\Swift_Mailer $mailer,FlashyNotifier $notifier,$pos) {
        $userEmail=$this->getUser()->getEmail();
        $userName=($this->getUser()->getUsername())." ".($this->getUser()->getUsername());
        $form=$this->createForm(DocShareType::class);
        $document=$documentRepository->find($id);
        $docNom=$document->getNom();
        $form->add("Envoyer",SubmitType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $data=$form->getData();
            if($document->getFichier()){
                $message=(new \Swift_Message($data['subject']))
                    ->setFrom($userEmail)
                    ->setTo($data['to'])
                    ->setBody($this->renderView(
                        'document/emailBody.html.twig',
                        ['textBody'=>$data['body']]
                    ),'text/html'
                    )
                    //$data['body']."\nCE DOCUMENT EST ENVOYE DEPUIS LA PLATEFORME EDSPACE PAR ".$userName
                    ->attach(\Swift_Attachment::fromPath($this->getParameter('document_dir').'/'.$docNom)->setFilename($docNom));
            } else{
                $message=(new \Swift_Message($data['subject']))
                    ->setFrom($userEmail)
                    ->setTo($data['to'])
                    ->setBody($this->renderView(
                        'document/emailBody.html.twig',
                        ['textBody'=>$data['body']]
                    ),'text/html'
                    );
            }
            $mailer->send($message);
            $notifier->success("Un email a été envoyé");
            if($pos==0)
                return $this->redirectToRoute('choixNiveau');
            elseif ($pos==1)
                return $this->redirectToRoute('mesDoc');
            else
                return $this->redirectToRoute('mesFavoris');
        }
        return $this->render("document/shareDoc.html.twig", ["f"=>$form->createView()]);
    }

    /**
     * @param NormalizerInterface $normalizer
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route ("/allDocs",name="allDocs")
     */
    function AllDocsJSON(NormalizerInterface $normalizer, DocumentRepository $repository): Response
    {
        $documents=$repository->findAll();
        $jsonContent=$normalizer->normalize($documents,'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }

    /**
     * @param Request $request
     * @param $id
     * @param NormalizerInterface $normalizer
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route ("/showDoc/{id}",name="showDoc")
     */
    function showDocJSON($id,NormalizerInterface $normalizer): Response
    {
        $em=$this->getDoctrine()->getManager();
        $document=$em->getRepository(Document::class)->find($id);
        $jsonContent=$normalizer->normalize($document,'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }

    /**
     * @param NormalizerInterface $normalizer
     * @param $id
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route ("/deleteDoc/{id}",name="deleteDoc")
     */
    function DeleteDocJSON(NormalizerInterface $normalizer,$id): Response
    {
        $em=$this->getDoctrine()->getManager();
        $document=$em->getRepository(Document::class)->find($id);
        $em->remove($document);
        $em->flush();
        $jsonContent=$normalizer->normalize($document,'json',['groups'=>'post:read']);
        return new Response("Document deleted successfully".json_encode($jsonContent));
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

    /*
    /**
     * @param NiveauRepository $niveauRepository
     * @return Response
     * @Route ("/document/choixNiveauAjout",name="choixNiveauAjout")
     */
    /*
    function ChoixNiveauAjout(NiveauRepository $niveauRepository){

        $niveaux=$niveauRepository->findAll();
        return $this->render("document/choixNiveauAjout.html.twig",['niveaux'=>$niveaux]);
    } */
}
