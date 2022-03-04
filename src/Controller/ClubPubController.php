<?php

namespace App\Controller;

use App\Entity\ClubPub;
use App\Form\ChangeClubPictureType;
use App\Form\ClubDescription;
use App\Form\ClubPubType;
use App\Form\ClubType;
use App\Repository\ClubPubRepository;
use App\Repository\ClubRepository;
use Knp\Component\Pager\PaginatorInterface;
use MartinGeorgiev\SocialPostBundle\SocialPostBundle;
use mysql_xdevapi\Exception;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ClubPubController extends Controller
{
    private $respo =0;
    private $etud = 0;

    /**
     * @Route("/club/pub", name="club_pub")
     */
    public function index(): Response
    {
        return $this->render('club_pub/index.html.twig', [
            'controller_name' => 'ClubPubController',
        ]);
    }

    /**
     * @Route("/displayPubClub/{id}", name="displayPubClub")
     */
    public function displayPubClub(PaginatorInterface $paginator,$id, Request $request, ClubPubRepository $repPub, ClubRepository $repClub): Response
    {


        $club = $repClub->find($id);
        $clubPic = $club->getClubPic();
        $desc = $club->getClubDescription();

        /*photo club*/

        $formPic = $this->createForm(ChangeClubPictureType::class, $club);
        $formPic->add('Update', SubmitType::class);
        $formPic->handleRequest($request);
        if ($formPic->isSubmitted() && $formPic->isValid()) {

            $file = $club->getClubPic();
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();
            try {
                $file->move($this->getParameter('ClubPictures_directory'),
                    $fileName
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }
            $club->setClubPic($fileName);
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute('displayPubClub', ['id' => $id]);
        }

        /*description club*/

        $formDesc = $this->createForm(ClubDescription::class, $club);
        $formDesc->add('Update', SubmitType::class);
        $formDesc->handleRequest($request);
        if ($formDesc->isSubmitted() && $formDesc->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute('displayPubClub', ['id' => $id]);
        }

        /*display publciation*/
        $minDate = $request->get('minDate');
        //  $minDateFormatted = date('Y-m-d', strtotime(str_replace('/', '-', $minDate)));
        $maxDate = $request->get('maxDate');

        //  $maxDateFormatted = date('Y-m-d', strtotime(str_replace('/', '-', $maxDate)));
        //  var_dump($minDateFormatted);

        //  var_dump($maxDateFormatted);
        if (($minDate == null && $maxDate == null) || ($minDate == "minDate" && $maxDate == "maxDate") || ($minDate == "minDate" && $maxDate != "maxDate") || ($minDate != "minDate" && $maxDate == "maxDate")) {
            $allPub = $repPub->find_all_approved_pub_ordredByDate($id);
        } elseif ($minDate != 'minDate' && $maxDate != 'maxDate') {
            $allPub = $repPub->find_all_pub_between_dates($minDate, $maxDate, $id);
        }
        $pubdisplay=$paginator->paginate($allPub,$request->query->getInt('page',1),2);
        $pub_hanging = $repPub->find_all_hanging_pub_ordredByDate($id);
        $pub_refused = $repPub->find_all_refused_pub_ordredByDate($id);

        $pubadd = new ClubPub();
        $form = $this->createForm(ClubPubType::class, $pubadd);
        $form->add('Ajouter', SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $img = $form->get('ClubImg')->getData();
            if ($img != null) {
                $fileName = md5(uniqid()) . '.' . $img->guessExtension();
                try {
                    $img->move($this->getParameter('PubPictures_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $pubadd->setClubImg($fileName);
            }

            $fileUploaded = $form->get('pubFile')->getData();
            if ($fileUploaded != null) {
                $nameFileUploaded = md5(uniqid()) . '.' . $fileUploaded->guessExtension();
                $fileUploaded->move($this->getParameter('PubFiles_directory'), $nameFileUploaded);
                $pubadd->setPubFileName($fileUploaded->getClientOriginalName());
                $pubadd->setPubFile(file_get_contents($this->getParameter('PubFiles_directory') . '/' . $nameFileUploaded));
                $pubadd->setTypeFichier(mime_content_type($this->getParameter('PubFiles_directory') . '/' . $nameFileUploaded));
            }
            $pubadd->setIsPosted(0);
            $pubadd->setPubDate(new \DateTime());
            $pubadd->setClub($club);
            $em = $this->getDoctrine()->getManager();

            $em->persist($pubadd);
            $em->flush();
            return $this->redirectToRoute('displayPubClub', ['id' => $id]);
        }

        /* redirection*/

        if ($this->etud) {
            return $this->render('club_pub/displayPubClub(etudiant).html.twig', [
                'pubs' => $pubdisplay, 'formPub' => $form->createView(), 'nom' => $club, 'idclub' => $id, 'descClub' => $desc, 'clubPic' => $clubPic
            ]);
        }
        if ($this->respo) {
            return $this->render('club_pub/displayPubClub(responsable).html.twig', [
                'pubs' => $pubdisplay, 'formPub' => $form->createView(), 'nom' => $club, 'idclub' => $id, 'descClub' => $desc, 'clubPic' => $clubPic, 'formDesc' => $formDesc->createView(), 'formPic' => $formPic->createView(), 'pub_hanging' => $pub_hanging, 'pub_refused' => $pub_refused
            ]);
        }
        return $this->render('club_pub/displayPubClub(admin).html.twig', [
            'pubs' => $pubdisplay, 'formPub' => $form->createView(), 'nom' => $club, 'idclub' => $id, 'descClub' => $desc, 'clubPic' => $clubPic, 'pub_hanging' => $pub_hanging, 'pub_refused' => $pub_refused
        ]);

    }

    /**
     * @Route("/deletePubClub/{id}/{idclub}", name="deletePubClub")
     */
    public function deletePubClub($idclub, $id, ClubPubRepository $rep): Response
    {
        $pub = $rep->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($pub);
        $entityManager->flush();
        return $this->redirectToRoute('displayPubClub', ['id' => $idclub]);
    }


    /**
     * @Route("/updatePubClub/{idpub}/{idclub}", name="updatePubClub")
     */

    public function updatePubClub($idclub, $idpub, Request $request, ClubPubRepository $rep): Response
    {


        $pub = $rep->find($idpub);
        $formPubEdit = $this->createForm(ClubPubType::class, $pub);
        $formPubEdit->add('Valider', SubmitType::class);
        $formPubEdit->handleRequest($request);
        if ($formPubEdit->isSubmitted() && $formPubEdit->isValid()) {
            $img = $formPubEdit->get('ClubImg')->getData();
            if ($img != null) {
                $fileName = md5(uniqid()) . '.' . $img->guessExtension();
                try {
                    $img->move($this->getParameter('PubPictures_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $pub->setClubImg($fileName);
            }
            $em = $this->getDoctrine()->getManager();
            // $pub->setPubDate(new \DateTime());
            $em->flush();
            return $this->redirectToRoute('displayPubClub', ['id' => $idclub]);

        }
        $currentImg = $pub->getClubImg();
        return $this->render('club_pub/updatePubClub(respo).html.twig', [
            'formPubEdit' => $formPubEdit->createView(), 'currentImg' => $currentImg
        ]);
    }


    /**
     * @Route("/DisplayPubFile/{id}", name="DisplayPubFile")
     */
    public function DisplayPubFile($id, ClubPubRepository $repository)
    {
        $pub = $repository->find($id);
        if (!$pub->getPubFile()) {
            throw $this->createNotFoundException("File with ID $id does not exist!");
        }
        $fic = stream_get_contents($pub->getPubFile()); //returns file stored as mysql blob
        $response = new Response($fic, 200, array('Content-Type' => $pub->getTypeFichier()));
        return $response;
    }

    /**
     * @Route("/FilterByDate/{idclub}", name="FilterByDate")
     */
    public function FilterByDate($idclub, ClubPubRepository $repository)
    {

        $pubdisplay = $repository->findby(['club' => $idclub], ['pubDate' => 'ASC']);
        return $this->redirectToRoute('displayPubClub', ['id' => $idclub, 'pubs' => $pubdisplay]);

    }

    /**
     * @Route("/acceptRefusePub/{idpub}/{idclub}/{value}", name="acceptRefusePub")
     */
    public function acceptRefusePub(ClubPubRepository $clubPubRepository, $idclub, $idpub, $value, Request $request)
    {
        $pub = $clubPubRepository->find($idpub);
        var_dump($value);
        if (strtoupper($value) == 'ACCEPT') {
            var_dump("true");

            $pub->setIsPosted(1);
            $this->getDoctrine()->getManager()->flush();

        } else {
            var_dump($value);

            $pub->setIsPosted(-1);
            $this->getDoctrine()->getManager()->flush();

        }
        var_dump($value);

        //  $pubdisplay = $repository->findby(['club' => $idclub], ['pubDate' => 'ASC']);
        return $this->redirectToRoute('displayPubClub', ['id' => $idclub]);
        //    return $this->render('club_pub/teest.html.twig');

    }



}
