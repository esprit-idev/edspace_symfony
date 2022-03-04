<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Form\ResetPassType;
use App\Repository\UserRepository;
use Doctrine\Persistence\ObjectManager;
use phpDocumentor\Reflection\Types\Null_;
use Symfony\Component\HttpFoundation\Request;
use App\Form\StudentType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/security", name="security")
     */
    public function index(): Response
    {
        return $this->render('security/index.html.twig', [
            'controller_name' => 'SecurityController',
        ]);

        /**
         * @Route ("/inscription",name="security_registration")
         */
    }
    public function registration(Request $request , ObjectManager $manager, UserPasswordEncoderInterface $encoder){
        $user=new User();
        $form=$this->createForm(RegistrationType::class,$user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $hash=$encoder->encodePassword($user ,$user->getPassword());
            $user->setPassword($hash);
            $em=$this->getDoctrine()->getManager();
            $manager->persist($user);
            $manager->flush();
        }
        return $this->render('security/registration.html.twig',[
            'form'=>$form->createView()
        ]);
    }
    /**
     * @Route("/login", name="app_login")
     */
    // public function login(){
    // return $this->render('security/login.html.twig');

    // }
    public function login(AuthenticationUtils $authenticationUtils,Session $session): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        $return = ['last_Username' => $lastUsername, 'error' => $error];
        if ($session->has('message')) {
            $message = $session->get('message');
            $session->remove('message');
            $return['message'] = $message;
        }
        return $this->render('security/login3.html.twig', $return);
    }
    /**
     * @Route ("/Activation/{token}",name="activation")
     */
    public function activationT($token , UserRepository $repository){
        $user=$repository->findby(['activation_token'=>$token]);
         if(!$user){
    throw $this->createNotFoundException('existe pas');
}
  $user->setActivationToken(null);
  $em=$this->getDoctrine()->getManager();
  $em->persist($user);
  $em->flush();
  $this->addFlash('message','vous avez bien acrivé votre compte');
  return $this->redirectToRoute('affiche');
}



    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
    /**
     * @Route ("/oubli-pass", name="forgot-password")
     */
    public function forgotPass(Request $request , UserRepository $repository , \Swift_Mailer  $mailer ,TokenGeneratorInterface $tokenGenerator){
     $form=$this->createForm(ResetPassType::class);
     $form->handleRequest($request);
     //si le form est valide
        if($form->isSubmitted() && $form->isValid()){
            //$donnees=$form->get('email')->getData();
            $donnees=$form->getData();
           // dump($donnees);

            $rep=$this->getDoctrine()->getRepository(User::class);
            $user=$repository->findOneBy(['email' => $donnees['email']]);
             //$user=$repository->findOneByEmail($donnees['email']);
            if (!$user){
                $this->addFlash('danger','cette adresse n\'existe pas');
                return $this->redirectToRoute('app_login');
            }
            $token=$tokenGenerator->generateToken();
            try{
                $user->setResetToken($token);
                $entityManager=$this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
            }catch (\Exception $e){
                $this->addFlash('warning','une erreur est survenue:', $e->getMessage());
                return $this->redirectToRoute('app_login');
            }
            //generation url de reinitialisation du mot de passe
            $url=$this->generateUrl('app_reset_password',['token'=>$token], UrlGeneratorInterface::ABSOLUTE_URL);
            // on envoie le message

            $message=(new \Swift_Message('mot de passe oublié'))
                     ->setFrom('oudengel@gmail.com')
                   ->setTo($user->getEmail())
                    ->setBody("<p>Bonjour,</p><p>Une demande de reinitialisation de mot de passe a été effectuée pour le site EdSpace . Veuillez cliquer sur le lien suivant : " . $url .'</p>','test/html'
              );
            // on envoie l'email
            $mailer->send($message);
            //on cree le message flash
            $this->addFlash('messge','une email de reinitialisation de mot de passe vous a été envoyé');
            return $this->redirectToRoute('app_login');
        }
        // on envoie vers la page de demande de l'email
        return  $this->render('security/forgot_password.html.twig',['emailForm'=>$form->createView()]);
    }

    /**
     * @Route ("/reset_pass/{token}",name="app_reset_password")
     */
    public function resetPassword($token ,Request $request,UserPasswordEncoderInterface $encoder){
$user=$this->getDoctrine()->getRepository(User::class)->findOneBy(['reset_token'=>$token]);
if (!$user){
    $this->addFlash('danger','token inconnu');
    return $this->redirectToRoute('app_login');
}
if ($request->isMethod('POST')) {
    $user->setResetToken(Null);
    $user->setPassword($encoder->encodePassword($user, $request->request->get('password')));
    $entityManager = $this->getDoctrine()->getManager();
    $entityManager->persist($user);
    $entityManager->flush();
    $this->addFlash('messsage', 'mot de passe modifié avec succés');
    return $this->redirectToRoute('app_login');
}
else{
    return $this->render('security/reset_password.html.twig',['token'=>$token]);
}

    }
}
