<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\DocumentFavoris;
use App\Entity\Matiere;
use App\Entity\Niveau;
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
use App\Entity\User;
use App\Entity\Message;
use App\Entity\Classe;
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
            $em=$this->getDoctrine()->getManager();
            $user1=$em->getRepository(User::class)->find($this->getUser()->getId());
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
            foreach($message as $i){
                if($i->getUser()->getId()==$user1->getId()){
                    $mymsg[]=$i;
                }
                else{
                    $othersmsg[]=$i;
                }
            }
            return $this->render("document/choixNiveauEtudiant.html.twig",
                ['niveaux'=>$niveaux,'documents'=>$documents,
                    'favoris'=>$favoris,
                'memebers'=> $memebers,
                'user' => $user1,
                'classe'=> $classe,
                'message'=> $message,
                'mymsg' => $mymsg,
                'others' =>$othersmsg]);
        } else{
            return $this->render('/403.html.twig');
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
            $em=$this->getDoctrine()->getManager();
            $user1=$em->getRepository(User::class)->find($this->getUser()->getId());
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
            foreach($message as $i){
                if($i->getUser()->getId()==$user1->getId()){
                    $mymsg[]=$i;
                }
                else{
                    $othersmsg[]=$i;
                }
            }
            return $this->render("document/listDocumentsEtudiant.html.twig", ['documents' => $documents, 'matieres' => $matieres,'favoris'=>$favoris,
                'memebers'=> $memebers,
                'user' => $user1,
                'classe'=> $classe,
                'message'=> $message,
                'mymsg' => $mymsg,
                'others' =>$othersmsg]);
        } else{
            return $this->render('/403.html.twig');
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
            $em=$this->getDoctrine()->getManager();
            $user1=$em->getRepository(User::class)->find($this->getUser()->getId());
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
            foreach($message as $i){
                if($i->getUser()->getId()==$user1->getId()){
                    $mymsg[]=$i;
                }
                else{
                    $othersmsg[]=$i;
                }
            }
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
                $document->setProprietaire($prop);
                $document->setFichier(file_get_contents($this->getParameter('document_dir') . '/' . $nomFic));
                $document->setBase64(base64_encode(file_get_contents($this->getParameter('document_dir') . '/' . $nomFic)));
                $document->setType(mime_content_type($this->getParameter('document_dir') . '/' . $nomFic));
                $document->setSignalements(0);
                $document->setUrl(null);
                $em = $this->getDoctrine()->getManager();
                $em->persist($document);
                $em->flush();
                $notifier->success("Votre document a été ajouté");
                return $this->redirectToRoute('choixNiveau');
            }
            return $this->render("document/ajoutDocument.html.twig", ["f" => $form->createView(),
                'memebers'=> $memebers,
                'user' => $user1,
                'classe'=> $classe,
                'message'=> $message,
                'mymsg' => $mymsg,
                'others' =>$othersmsg]);
        }else{
            return $this->render('/403.html.twig');
        }
    }


    /**
     * @param Request $request
     * @param FlashyNotifier $notifier
     * @return mixed
     * @Route ("/document/ajoutWebPdf",name="ajoutWebPdf")
     */
    function AjoutWebPDF(Request $request, FlashyNotifier $notifier){
        $em=$this->getDoctrine()->getManager();
        $user1=$em->getRepository(User::class)->find($this->getUser()->getId());
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
        foreach($message as $i){
            if($i->getUser()->getId()==$user1->getId()){
                $mymsg[]=$i;
            }
            else{
                $othersmsg[]=$i;
            }
        }
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
                $nomFic = $document->getNom();
                $document->setNom($nomFic);
                $document->setDateInsert(date("d/m/y"));
                $document->setProprietaire($prop);
                $document->setType("url");
                $document->setSignalements(0);
                $document->setFichier(null);
                $document->setBase64(null);
                $em = $this->getDoctrine()->getManager();
                $em->persist($document);
                $em->flush();
                $notifier->success("L'URL a été ajouté");
                return $this->redirectToRoute('choixNiveau');
            }
            return $this->render("document/ajoutWebPdf.html.twig", ["f" => $form->createView(),
                'memebers'=> $memebers,
                'user' => $user1,
                'classe'=> $classe,
                'message'=> $message,
                'mymsg' => $mymsg,
                'others' =>$othersmsg]);
        } else{
                return $this->render('/403.html.twig');
            }
    }

    /**
     * @Route ("/document/modifDocument/{id}",name="modifDocument")
     */
    function ModifDocument($id,DocumentRepository $repository,Request $request,FlashyNotifier $notifier){
        $hasAccessStudent= $this->isGranted('ROLE_STUDENT');
        if($hasAccessStudent) {
            $em=$this->getDoctrine()->getManager();
            $user1=$em->getRepository(User::class)->find($this->getUser()->getId());
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
            foreach($message as $i){
                if($i->getUser()->getId()==$user1->getId()){
                    $mymsg[]=$i;
                }
                else{
                    $othersmsg[]=$i;
                }
            }
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
        return $this->render("document/modifDocument.html.twig",["f"=>$form->createView(),
            'memebers'=> $memebers,
            'user' => $user1,
            'classe'=> $classe,
            'message'=> $message,
            'mymsg' => $mymsg,
            'others' =>$othersmsg]);
        } else{
            return $this->render('/403.html.twig');
        }
    }

    /**
     * @Route ("/document/modifDocumentMine/{id}",name="modifDocumentMine")
     */
    function ModifDocumentMine($id,DocumentRepository $repository,Request $request,FlashyNotifier $notifier){
        $hasAccessStudent= $this->isGranted('ROLE_STUDENT');
        if($hasAccessStudent) {
            $em=$this->getDoctrine()->getManager();
            $user1=$em->getRepository(User::class)->find($this->getUser()->getId());
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
            foreach($message as $i){
                if($i->getUser()->getId()==$user1->getId()){
                    $mymsg[]=$i;
                }
                else{
                    $othersmsg[]=$i;
                }
            }
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
        return $this->render("document/modifDocument.html.twig",["f"=>$form->createView(),
            'memebers'=> $memebers,
            'user' => $user1,
            'classe'=> $classe,
            'message'=> $message,
            'mymsg' => $mymsg,
            'others' =>$othersmsg]);
        } else{
            return $this->render('/403.html.twig');
        }
    }

    /**
     * @Route ("/document/modifDocumentFavoris/{id}",name="modifDocumentFavoris")
     */
    function ModifDocumentFavoris($id,DocumentRepository $repository,Request $request,FlashyNotifier $notifier){
        $hasAccessStudent= $this->isGranted('ROLE_STUDENT');
        if($hasAccessStudent) {
            $em=$this->getDoctrine()->getManager();
            $user1=$em->getRepository(User::class)->find($this->getUser()->getId());
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
            foreach($message as $i){
                if($i->getUser()->getId()==$user1->getId()){
                    $mymsg[]=$i;
                }
                else{
                    $othersmsg[]=$i;
                }
            }
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
        return $this->render("document/modifDocument.html.twig",["f"=>$form->createView(),
            'memebers'=> $memebers,
            'user' => $user1,
            'classe'=> $classe,
            'message'=> $message,
            'mymsg' => $mymsg,
            'others' =>$othersmsg]);
        } else{
            return $this->render('/403.html.twig');
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
            return $this->render('/403.html.twig');
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
            return $this->render('/403.html.twig');
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
            return $this->render('/403.html.twig');
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
            return $this->render('/403.html.twig');
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
            return $this->render('/403.html.twig');
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
            return $this->render('/403.html.twig');
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
            $em=$this->getDoctrine()->getManager();
            $user1=$em->getRepository(User::class)->find($this->getUser()->getId());
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
            foreach($message as $i){
                if($i->getUser()->getId()==$user1->getId()){
                    $mymsg[]=$i;
                }
                else{
                    $othersmsg[]=$i;
                }
            }
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
        return $this->render("document/mesDoc.html.twig", ['documents' => $documents,
            'memebers'=> $memebers,
            'user' => $user1,
            'classe'=> $classe,
            'message'=> $message,
            'mymsg' => $mymsg,
            'others' =>$othersmsg]);
        } else{
            return $this->render('/403.html.twig');
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
        $userId=$this->getUser()->getId();
        if($hasAccessStudent) {
            $em=$this->getDoctrine()->getManager();
            $user1=$em->getRepository(User::class)->find($this->getUser()->getId());
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
            foreach($message as $i){
                if($i->getUser()->getId()==$user1->getId()){
                    $mymsg[]=$i;
                }
                else{
                    $othersmsg[]=$i;
                }
            }
        $user=$userRepository->find($userId);
        $docsInFav=$documentFavorisRepository->findBy(array('user'=>$user));
        return $this->render("document/mesFavoris.html.twig", ['docsInFav' => $docsInFav,
            'memebers'=> $memebers,
            'user' => $user1,
            'classe'=> $classe,
            'message'=> $message,
            'mymsg' => $mymsg,
            'others' =>$othersmsg]);
        } else{
            return $this->render('/403.html.twig');
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
            return $this->render('/403.html.twig');
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
            return $this->render('/403.html.twig');
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
            return $this->render('/403.html.twig');
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
            return $this->render('/403.html.twig');
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
        $userId=$this->getUser()->getId();
        if($hasAccessStudent) {
        $document=$documentRepository->find($id);
        $user=$userRepository->find($userId);
        $docFavoris=new DocumentFavoris();
        $docFavoris->setDocument($document);
        $docFavoris->setUser($user);
        $em=$this->getDoctrine()->getManager();
        $em->persist($docFavoris);
        $em->flush();
        $notifier->primary("Document ajouté aux favoris!");
        return $this->redirectToRoute('choixNiveau');
        } else{
            return $this->render('/403.html.twig');
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
            return $this->render('/403.html.twig');
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
        $this->UnPinDoc($id,$documentRepository,$userRepository,$documentFavorisRepository);
        $notifier->primary("Document supprimé des favoris!");
        return $this->redirectToRoute('mesFavoris');
        } else{
            return $this->render('/403.html.twig');
        }
    }

    function UnPinDoc($id,DocumentRepository $documentRepository,UserRepository $userRepository,DocumentFavorisRepository $documentFavorisRepository){
        $userId=$this->getUser()->getId();
        $user=$userRepository->find($userId);
        $document=$documentRepository->find($id);
        $docInFav=$documentFavorisRepository->findOneBy(array('document'=>$document,'user'=>$user));
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
        return $this->ShareDocUrl($request,$id,$documentRepository,$mailer,$notifier,0);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Route ("/document/shareDocMine/{id}",name="shareDocMine")
     */
    public function ShareDocMine(Request $request,$id,DocumentRepository $documentRepository,\Swift_Mailer $mailer,FlashyNotifier $notifier) {
        return $this->ShareDocUrl($request,$id,$documentRepository,$mailer,$notifier,1);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Route ("/document/shareDocFavoris/{id}",name="shareDocFavoris")
     */
    public function ShareDocFavoris(Request $request,$id,DocumentRepository $documentRepository,\Swift_Mailer $mailer,FlashyNotifier $notifier) {
        return $this->ShareDocUrl($request,$id,$documentRepository,$mailer,$notifier,2);
    }

    public function ShareDocUrl(Request $request,$id,DocumentRepository $documentRepository,\Swift_Mailer $mailer,FlashyNotifier $notifier,$pos) {

        $hasAccessStudent= $this->isGranted('ROLE_STUDENT');
        if($hasAccessStudent) {
            $em=$this->getDoctrine()->getManager();
            $user1=$em->getRepository(User::class)->find($this->getUser()->getId());
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
            foreach($message as $i){
                if($i->getUser()->getId()==$user1->getId()){
                    $mymsg[]=$i;
                }
                else{
                    $othersmsg[]=$i;
                }
            }
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
            return $this->render("document/shareDoc.html.twig", ["f"=>$form->createView(),
                'memebers'=> $memebers,
                'user' => $user1,
                'classe'=> $classe,
                'message'=> $message,
                'mymsg' => $mymsg,
                'others' =>$othersmsg]);
        } else{
            return $this->render('/403.html.twig');
        }
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

    /**
     * @param NormalizerInterface $normalizer
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route ("/allPinnedDocs",name="allPinnedDocs")
     */
    function AllPinnedDocsJSON(NormalizerInterface $normalizer, DocumentFavorisRepository $repository): Response
    {
        $documents=$repository->findAll();
        $jsonContent=$normalizer->normalize($documents,'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }

    /**
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route ("/addUrl/new",name="addUrl")
     */
    public function addUrlJSON(NormalizerInterface $normalizer, Request $request,NiveauRepository $niveauRepository,MatiereRepository $matiereRepository):Response
    {
        $em = $this->getDoctrine()->getManager();
        $document = new Document();
        $document->setNom($request->get('nom'));
        $document->setDateInsert($request->get('date_insert'));
        $document->setProprietaire($request->get('proprietaire'));

        $niveau=$niveauRepository->find($request->get('niveau'));
        $matiere=$matiereRepository->find($request->get('matiere'));

        $matiere->setNiveau($niveau);
        $document->setMatiere($matiere);
        $document->setNiveau($niveau);
        $document->setType("url");
        $document->setSignalements(0);
        $document->setUrl($request->get('url'));
        $document->setBase64($request->get('base64'));
        $em->persist($document);
        $em->flush();
        $jsonContent = $normalizer->normalize($document,'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }

    /**
     * @param NormalizerInterface $normalizer
     * @param $id
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route ("/deletePin/{docId}/{userId}",name="deletePin")
     */
    function UnpinDocJSON(NormalizerInterface $normalizer,$docId,$userId,UserRepository $userRepository,DocumentRepository $documentRepository,DocumentFavorisRepository $documentFavorisRepository): Response
    {
        $user=$userRepository->find($userId);
        $document=$documentRepository->find($docId);
        $docInFav=$documentFavorisRepository->findOneBy(array('document'=>$document,'user'=>$user));
        $em=$this->getDoctrine()->getManager();
        $em->remove($docInFav);
        $em->flush();
        $jsonContent=$normalizer->normalize($document,'json',['groups'=>'post:read']);
        return new Response("Document unpinned successfully".json_encode($jsonContent));
    }

    /**
     * @param NormalizerInterface $normalizer
     * @param UserRepository $userRepository
     * @param Request $request
     * @param DocumentRepository $documentRepository
     * @Route ("/addPin/new",name="addPin")
     */
    function PinDocJSON(NormalizerInterface $normalizer,UserRepository $userRepository,Request $request,DocumentRepository $documentRepository){
        $user=$userRepository->find($request->get('userId'));
        $document=$documentRepository->find($request->get('docId'));
        $docFavoris=new DocumentFavoris();
        $docFavoris->setDocument($document);
        $docFavoris->setUser($user);
        $em=$this->getDoctrine()->getManager();
        $em->persist($docFavoris);
        $em->flush();
        $jsonContent=$normalizer->normalize($docFavoris,'json',['groups'=>'post:read']);
        return new Response("Document pinned successfully".json_encode($jsonContent));
    }

    /**
     * @param NormalizerInterface $normalizer
     * @param $id
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route ("/signalDoc/{id}",name="signalDoc")
     */
    function SignalDocJSON(NormalizerInterface $normalizer,$id,DocumentRepository $repository): Response
    {
        $document=$repository->find($id);
        $repository->IncrementCountSignal($document);
        $jsonContent=$normalizer->normalize($document,'json',['groups'=>'post:read']);
        return new Response("Document reported successfully".json_encode($jsonContent));
    }

    /**
     * @param NormalizerInterface $normalizer
     * @param $id
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route ("/ignoreSignalDoc/{id}",name="ignoreSignalDoc")
     */
    function IgnoreSignalDocJSON(NormalizerInterface $normalizer,$id,DocumentRepository $repository): Response
    {
        $document=$repository->find($id);
        $repository->DecrementCountSignal($document);
        $jsonContent=$normalizer->normalize($document,'json',['groups'=>'post:read']);
        return new Response("Document reported successfully".json_encode($jsonContent));
    }

    /**
     * @param NormalizerInterface $normalizer
     * @param $id
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route ("/shareDoc/{id}",name="shareDoc")
     */
    function ShareDocJSON(NormalizerInterface $normalizer,$id,DocumentRepository $repository,\Swift_Mailer $mailer,Request $request): Response
    {
        //get doc to send
        $document=$repository->find($id);
        //init vars
        $userEmail=$request->get('userEmail');
        $userName=$request->get('username');
        $destEmail=$request->get('destEmail');
        $body=$request->get('body');
        $subject=$request->get('subject');
        $docNom=$document->getNom();
        if($document->getType()!="url"){
            //if file
            $message=(new \Swift_Message($subject))
                ->setFrom($userEmail)
                ->setTo($destEmail)
                ->setBody($this->renderView(
                    'document/emailBodyMobile.html.twig',
                    ['textBody'=>$body,'cuEt'=>$userName]
                ),'text/html'
                )
                ->attach(\Swift_Attachment::fromPath($this->getParameter('document_dir').'/'.$docNom)->setFilename($docNom));
        } else{
            //if url
            $message=(new \Swift_Message($subject))
                ->setFrom($userEmail)
                ->setTo($destEmail)
                ->setBody($this->renderView(
                    'document/emailBodyMobile.html.twig',
                    ['textBody'=>$body." ".$document->getUrl(),'cuEt'=>$userName]
                ),'text/html'
                );
        }
        $mailer->send($message);
        $jsonContent=$normalizer->normalize($document,'json',['groups'=>'post:read']);
        return new Response("Document sent successfully".json_encode($jsonContent));
    }

}
