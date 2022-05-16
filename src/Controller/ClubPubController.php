<?php

namespace App\Controller;

use App\Entity\ClubPub;
use App\Entity\User;
use App\Entity\Message;
use App\Entity\Classe;
use App\Entity\PubLikes;
use App\Form\ChangeClubPictureType;
use App\Form\ClubDescription;
use App\Form\ClubPubType;
use App\Form\ClubType;
use App\Repository\ClubPubRepository;
use App\Repository\ClubRepository;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use MartinGeorgiev\SocialPostBundle\SocialPostBundle;
use mysql_xdevapi\Exception;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;


class ClubPubController extends Controller
{


    /**
     * @Route("/displayPubClub/{id}", name="displayPubClub")
     */
    public function displayPubClub(PaginatorInterface $paginator, $id, Request $request, ClubPubRepository $repPub, ClubRepository $repClub): Response
    {

        $hasAccessAgent = $this->isGranted('ROLE_ADMIN');
        $hasAccessStudent = $this->isGranted('ROLE_STUDENT');
        $hasAccessResponsable = $this->isGranted('ROLE_RESPONSABLEC');

        $club = $repClub->find($id);
        $clubPic = $club->getClubPic();
        $desc = $club->getClubDescription();

        /* messaging */

        if ($hasAccessResponsable || $hasAccessStudent) {
            $em = $this->getDoctrine()->getManager();
            $user1 = $em->getRepository(User::class)->find($this->getUser()->getId());
            $em1 = $this->getDoctrine()->getRepository(User::class);
            $memebers = $em1->findBy(['classe' => $user1->getClasse()->getId()]);
            $classe = $em->getRepository(Classe::class)->find($user1->getClasse()->getId());

            $message = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository(Message::class)
                ->findBy(array(), array('postDate' => 'ASC'));
            $mymsg = [];
            $othersmsg = [];
            foreach ($message as $i) {
                if ($i->getUser()->getId() == $user1->getId()) {
                    $mymsg[] = $i;
                } else {
                    $othersmsg[] = $i;
                }
            }
        }
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

        /*display publciation filtred by date*/

        $minDate = $request->get('minDate');
        $maxDate = $request->get('maxDate');

        if (($minDate == null && $maxDate == null) || ($minDate == "minDate" && $maxDate == "maxDate") || ($minDate == "minDate" && $maxDate != "maxDate") || ($minDate != "minDate" && $maxDate == "maxDate")) {
            $allPub = $repPub->find_all_approved_pub_ordredByDate($id);
        } elseif ($minDate != 'minDate' && $maxDate != 'maxDate') {
            $allPub = $repPub->find_all_pub_between_dates($minDate, $maxDate, $id);
        }
        $pub_hanging = $repPub->find_all_hanging_pub_ordredByDate($id);
        $pub_refused = $repPub->find_all_refused_pub_ordredByDate($id);

        /*paginatorr*/

        $pubdisplay = $paginator->paginate($allPub, $request->query->getInt('page', 1), 2);

        /*add pub */

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
        if ($hasAccessResponsable && $this->getUser()->getClub() == $club) {
            return $this->render('club_pub/displayPubClub(responsable).html.twig', [
                'pubs' => $pubdisplay,
                'formPub' => $form->createView(),
                'nom' => $club, 'idclub' => $id,
                'descClub' => $desc,
                'clubPic' => $clubPic,
                'formDesc' => $formDesc->createView(),
                'formPic' => $formPic->createView(),
                'pub_hanging' => $pub_hanging,
                'pub_refused' => $pub_refused,
                'memebers' => $memebers,
                'user' => $user1,
                'classe' => $classe,
                'message' => $message,
                'mymsg' => $mymsg,
                'others' => $othersmsg
            ]);
        } elseif ($hasAccessStudent) {
            return $this->render('club_pub/displayPubClub(etudiant).html.twig', [
                'pubs' => $pubdisplay,
                'formPub' => $form->createView(),
                'nom' => $club,
                'idclub' => $id,
                'descClub' => $desc,
                'clubPic' => $clubPic,
                'memebers' => $memebers,
                'user' => $user1,
                'classe' => $classe,
                'message' => $message,
                'mymsg' => $mymsg,
                'others' => $othersmsg
            ]);
        } elseif ($hasAccessAgent) {
            return $this->render('club_pub/displayPubClub(admin).html.twig', [
                'pubs' => $pubdisplay, 'formPub' => $form->createView(), 'nom' => $club, 'idclub' => $id, 'descClub' => $desc, 'clubPic' => $clubPic, 'pub_hanging' => $pub_hanging, 'pub_refused' => $pub_refused
            ]);
        } else return $this->render('/403.html.twig');

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

        $hasAccessResponsable = $this->isGranted('ROLE_RESPONSABLEC');
        if ($hasAccessResponsable) {
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
                $fileUploaded = $formPubEdit->get('pubFile')->getData();
                if ($fileUploaded != null) {
                    $nameFileUploaded = md5(uniqid()) . '.' . $fileUploaded->guessExtension();
                    $fileUploaded->move($this->getParameter('PubFiles_directory'), $nameFileUploaded);
                    $pub->setPubFileName($nameFileUploaded);
                    $pub->setPubFile(file_get_contents($this->getParameter('PubFiles_directory') . '/' . $nameFileUploaded));
                    $pub->setTypeFichier(mime_content_type($this->getParameter('PubFiles_directory') . '/' . $nameFileUploaded));
                }
                $pub->setIsPosted(0);

                $em = $this->getDoctrine()->getManager();
                // $pub->setPubDate(new \DateTime());
                $em->flush();
                return $this->redirectToRoute('displayPubClub', ['id' => $idclub]);

            }
            $currentImg = $pub->getClubImg();
            return $this->render('club_pub/updatePubClub(respo).html.twig', [
                'formPubEdit' => $formPubEdit->createView(), 'currentImg' => $currentImg
            ]);
        } else return $this->render('/403.html.twig');

    }


    /* to download files*/
    /**
     * @Route("/DisplayPubFile/{id}", name="DisplayPubFile")
     */
    public function DisplayPubFile($id, ClubPubRepository $repository)
    {
        $pub = $repository->find($id);
      
        return $this->render("club_pub/apercuFile.html.twig", ["pub" => $pub]);

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


    /**
     * @Route("/displayPub/{idpub}/{idclub}", name="displayPub")
     */
    public function displayPub($idpub, $idclub, ClubPubRepository $clubPubRepository, UserRepository $Repository, ClubRepository $ClubRepository)
    {
        $pub = $clubPubRepository->find($idpub);
        $clubPic = $ClubRepository->find($idclub)->getClubPic();

        return $this->render('club_pub/displayOnePub(etudiant).html.twig', ['c' => $pub, 'clubPic' => $clubPic, 'idclub' => $idclub
        ]);
    }


    /**
     * @Route("/allClubPubs/{clubId}", name="allPubs")
     */
    public function allPubs(NormalizerInterface $normalizer, ClubPubRepository $rep, $clubId): Response
    {
        $pubs = $rep->find_all_approved_pub_ordredByDate($clubId);
        $jsonContent = $normalizer->normalize($pubs, 'json', ['groups' => 'post:read']);
        return new Response(json_encode($jsonContent));
    }

    /**
     * @Route("/addClubPubJson/{clubId}", name="addClubPubJson")
     */
    public function addClubPubsJson(Request $request, NormalizerInterface $normalizer, ClubRepository $rep, $clubId): Response
    {
        $club = $rep->find($clubId);
        $em = $this->getDoctrine()->getManager();
        $pub = new ClubPub();
        $pub->setPubDescription($request->get('pubDesc'));
        $pub->setClubImg($request->get('pubImg'));
        $pub->setPubDate(new \DateTime());
        $pub->setClub($club);
        $pub->setIsPosted(0);
        $em->persist($pub);
        $em->flush();
        $jsonContent = $normalizer->normalize($pub, 'json', ['groups' => 'post:read']);
        return new Response(json_encode($jsonContent));
    }

    /**
     * @Route("/deleteClubPubsJson/{pubId}", name="addClubPubsJson")
     */
    public function deleteStudentJSON(Request $request, NormalizerInterface $normalizer, $pubId): Response
    {

        $em = $this->getDoctrine()->getManager();
        $pub = $em->getRepository(ClubPub::class)->find($pubId);
        $em->remove($pub);
        $em->flush();
        $jsonContent = $normalizer->normalize($pub, 'json', ['groups' => 'post:read']);
        return new Response("Student deleted successfully" . json_encode($jsonContent));
    }

    /**
     * @Route("/editPubClubJson/{pubId}", name="editPubClubJson")
     */
    public function editPubClubJson($pubId, Request $request, NormalizerInterface $normalizer, ClubPubRepository $rep): Response
    {
        $em = $this->getDoctrine()->getManager();
        $pub = $rep->find($pubId);
        $pub->setPubDescription($request->get('pubDesc'));
        $pub->setPubDate(new \DateTime());
        $pub->setIsPosted(0);
        $em->flush();
        $jsonContent = $normalizer->normalize($pub, 'json', ['groups' => 'post:read']);
        return new Response(json_encode($jsonContent));
    }


    /**
     * @Route("/uploadPubPic", name="uploadPubPic")
     */
    public function uploadPubPic(ClubPubRepository $rep)
    {
        $allowedExts = array("jpeg", "jpg", "png");
        $temp = explode(".", $_FILES["file"]["name"]);
        $extension = end($temp);

        if ((($_FILES["file"]["type"] == "image/jpeg") || ($_FILES["file"]["type"] == "image/jpg") || ($_FILES["file"]["type"] == "image/png")) && ($_FILES["file"]["size"] < 5000000) && in_array($extension, $allowedExts)) {
            if ($_FILES["file"]["error"] > 0) {
                $named_array = array("Response" => array(array("Status" => "error")));
                echo json_encode($named_array);
            } else {
                move_uploaded_file($_FILES["file"]["tmp_name"], "PubPictures/" . $_FILES["file"]["name"]);
                $named_array = array("Response" => array(array("Status" => "ok")));
                echo json_encode($named_array);
            }
        } else {
            $named_array = array("Response" => array(array("Status" => "invalid")));
            echo json_encode($named_array);
        }
    }

    /**
     * @Route("/EnAttenteClubPubs/{clubId}", name="EnAttenteClubPubs")
     */
    public function EnAttenteClubPubs(NormalizerInterface $normalizer, ClubPubRepository $rep, $clubId): Response
    {
        $pubs = $rep->find_all_hanging_pub_ordredByDate($clubId);
        $jsonContent = $normalizer->normalize($pubs, 'json', ['groups' => 'post:read']);
        return new Response(json_encode($jsonContent));
    }

    /**
     * @Route("/RefusedClubPubs/{clubId}", name="RefusedClubPubs")
     */
    public function RefusedClubPubs(NormalizerInterface $normalizer, ClubPubRepository $rep, $clubId): Response
    {
        $pubs = $rep->find_all_refused_pub_ordredByDate($clubId);
        $jsonContent = $normalizer->normalize($pubs, 'json', ['groups' => 'post:read']);
        return new Response(json_encode($jsonContent));
    }

    /**
     * @Route("/uploadClubPic/{clubId}", name="uploadClubPic")
     */
    public function uploadClubPic(ClubRepository $rep, $clubId)
    {
        $allowedExts = array("jpeg", "jpg", "png");
        $temp = explode(".", $_FILES["file"]["name"]);
        $extension = end($temp);

        if ((($_FILES["file"]["type"] == "image/jpeg") || ($_FILES["file"]["type"] == "image/jpg") || ($_FILES["file"]["type"] == "image/png")) && ($_FILES["file"]["size"] < 5000000) && in_array($extension, $allowedExts)) {
            if ($_FILES["file"]["error"] > 0) {
                $named_array = array("Response" => array(array("Status" => "error")));
                echo json_encode($named_array);
            } else {
                move_uploaded_file($_FILES["file"]["tmp_name"], "ClubPictures/" . $_FILES["file"]["name"]);
                $named_array = array("Response" => array(array("Status" => "ok")));

                $club = $rep->find($clubId);
                $club->setClubPic($_FILES["file"]["name"]);
                $em = $this->getDoctrine()->getManager();

                $em->flush();
                echo json_encode($named_array);
            }
        } else {
            $named_array = array("Response" => array(array("Status" => "invalid")));
            echo json_encode($named_array);
        }
    }


    /**
     * @Route("/acceptRefusePubJSON/{idpub}/{value}", name="acceptRefusePubJSON")
     */
    public function acceptRefusePubJSON(NormalizerInterface $normalizer, ClubPubRepository $clubPubRepository, $idpub, $value, Request $request)
    {
        $pub = $clubPubRepository->find($idpub);
        if (strtoupper($value) == 'ACCEPT') {

            $pub->setIsPosted(1);
            $this->getDoctrine()->getManager()->flush();

        } else {

            $pub->setIsPosted(-1);
            $this->getDoctrine()->getManager()->flush();

        }

        $jsonContent = $normalizer->normalize($pub, 'json', ['groups' => 'post:read']);
        return new Response(json_encode($jsonContent));
    }
}

