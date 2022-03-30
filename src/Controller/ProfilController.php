<?php

namespace App\Controller;

use App\Form\EditProfilType;
use App\Form\ItemType;
use App\Entity\User ;
use App\Entity\Classe ;
use App\Entity\Message ;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ProfilController extends AbstractController
{
    /**
     * @Route("/profil", name="app_profil")
     */
    public function index(): Response
    {
        return $this->render('profil/index.html.twig', [
            'controller_name' => 'ProfilController',
        ]);
    }
    /**
     * @Route ("/profil/show", name="show")
     */
    public function show()
    {
        $test=$this->getUser()->getId();
        $em=$this->getDoctrine()->getManager();
        $user1=$em->getRepository(User::class)->find($test);
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
            }}

        return $this->render('profil/index.html.twig',['memebers'=> $memebers,
        'user' => $user1,
        'classe'=> $classe,
        'message'=> $message,
        'mymsg' => $mymsg,
        'others' =>$othersmsg]);

}

    /**
     * @Route ("/profil/edit",name="ProfilEdit")
     */
    public function editProfile(Request $request, UserPasswordEncoderInterface $encoder){
        //$user= new User();
        $user=$this->getUser();
        $form=$this->createForm(EditProfilType::class , $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $hash=$encoder->encodePassword($user ,$user->getPassword());
            $user->setPassword($hash);
            $em=$this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('message','Profil mis a jour');
            return $this->redirectToRoute('show');
        }
        $test=$this->getUser()->getId();
        $em=$this->getDoctrine()->getManager();
        $user1=$em->getRepository(User::class)->find($test);
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
            }}

        return $this->render('profil/edit.html.twig',[
            'form'=>$form->createView(),'memebers'=> $memebers,
                'user' => $user1,
                'classe'=> $classe,
                'message'=> $message,
                'mymsg' => $mymsg,
                'others' =>$othersmsg
        ]);
    }
    /**
     * @Route("/new", name="photo", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        //$user = new User();
        $user=$this->getUser();
        $form = $this->createForm(ItemType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //$file = $user->getImage();
            $file=$form->get('image')->getData();
            $fileName =md5(uniqid()).'.'.$file->guessExtension();

            // moves the file to the directory where brochures are stored
            $file->move(
                $this->getParameter('upload_directory'),
                $fileName
            );

            $user->setImage($fileName);
            $em=$this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();


            return $this->redirectToRoute('show');
        }

        $test=$this->getUser()->getId();
        $em=$this->getDoctrine()->getManager();
        $user1=$em->getRepository(User::class)->find($test);
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
            }}


        return $this->render('profil/editPhoto.html.twig', [
            
            'form' => $form->createView(),'memebers'=> $memebers,
            'user' => $user1,
            'classe'=> $classe,
            'message'=> $message,
            'mymsg' => $mymsg,
            'others' =>$othersmsg
        ]);
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }

    /**
     * @Route("/editProfileJson" , name="app_gestion_profile")
     */
public function editUser(Request $request , UserPasswordEncoderInterface $encoder){
    $id=$request->get("id");
    $username =$request->query->get("username");
    $password=$request->query->get("password");
    $em=$this->getDoctrine()->getManager();
    $user=$em->getRepository(User::class)->find($id);
    if($request->files->get("photo")!=null){
    $file = $request->files->get("image");
    $fileName=$file->getClientOriginalName();
    $file->move($fileName);
    $user->setImage($fileName);
    }
    $user->setUsername($username);
    $user->setPassword(
        $encoder->encodePassword(
               $user,
            $password
        )
    );
       //$user->setIsVerified(true);
    try {
        $em=$this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        return new JsonResponse("success", 200); //200 yaani http result ta3 server ok
    }catch(\Exception $ex){
        return new Response("failed".$ex->getMessage());
    }
}
}
