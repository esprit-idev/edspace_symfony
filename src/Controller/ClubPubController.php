<?php

namespace App\Controller;

use App\Entity\ClubPub;
use App\Form\ClubPubType;
use App\Repository\ClubPubRepository;
use App\Repository\ClubRepository;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClubPubController extends AbstractController
{
    private $respo=0;
    private $etud=0;
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
    public function displayPubClub($id,Request $request,ClubPubRepository $repPub,ClubRepository $repClub): Response
    {


        $club=$repClub->find($id);
        $desc=$club->getClubDescription();
        $pubdisplay=$repPub->findby(['club' => $id],['pubDate'=>'DESC']);
        $pubadd = new ClubPub();
        $form = $this->createForm(ClubPubType::class, $pubadd);
        $form->add('Ajouter', SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $pubadd->setPubDate(new \DateTime());
            $pubadd->setClub($club);
            $em->persist($pubadd);
            $em->flush();
            return $this->redirectToRoute('displayPubClub',['id' => $id]);
        }
        if ($this->etud){return $this->render('club_pub/displayPubClub(etudiant).html.twig', [
            'pubs'=> $pubdisplay, 'formPub' => $form->createView(),'nom'=>$club,'idclub'=>$id,'descClub'=>$desc
        ]);}
        if($this->respo){return $this->render('club_pub/displayPubClub(responsable).html.twig', [
            'pubs'=> $pubdisplay, 'formPub' => $form->createView(),'nom'=>$club,'idclub'=>$id,'descClub'=>$desc
        ]);}
        return $this->render('club_pub/displayPubClub(admin).html.twig', [
            'pubs'=> $pubdisplay, 'formPub' => $form->createView(),'nom'=>$club,'idclub'=>$id
        ]);

    }

    /**
     * @Route("/deletePubClub/{id}/{idclub}", name="deletePubClub")
     */
    public function deletePubClub($idclub,$id,ClubPubRepository $rep): Response
    {
        $pub = $rep->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($pub);
        $entityManager->flush();
        return $this->redirectToRoute('displayPubClub',['id' => $idclub]);
    }


    /**
     * @Route("/updatePubClub/{idpub}/{idclub}", name="updatePubClub")
     */

    public function updatePubClub($idclub,$idpub,Request  $request,ClubPubRepository $rep): Response
    {   $desc=$request->get('desc');
        $pub = $rep->find($idpub);
        $pub->setPubDescription($desc);
        $pub->setPubDate(new \DateTime());
        $em = $this->getDoctrine()->getManager();
        $em->flush();
        return $this->redirectToRoute('displayPubClub',['id' => $idclub]);
    }


}
