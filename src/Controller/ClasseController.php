<?php

namespace App\Controller;

use App\Entity\Classe;
use App\Entity\Niveau;
use App\Entity\User;
use App\Form\ClasseType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Repository\ClasseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;
use mysqli;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;

class ClasseController extends AbstractController
{

    /**
     * @Route("/classe", name="Classe")
     */
    public function index(): Response
    {



        $hasAccessStudent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessStudent){
            $em1=$this->getDoctrine()->getRepository(Niveau::class);
            $niveau=$em1->findAll(Niveau::class);
            
    
            
    
            
           
    
            $em=$this->getDoctrine()->getRepository(Classe::class);
            $classes=$em->findAll(Classe::class);
    
            
    
            return $this->render('classe/classes.html.twig', [
                'classes' => $classes,
                'niveau'=> $niveau,
            ]);}
     
            return $this->render('/403.html.twig');
    }

 
    



    /**
     * @Route("/listc", name="listc")
     */
    public function addett(): Response
    {

        
        
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        $em=$this->getDoctrine()->getRepository(Classe::class);
        $classes=$em->findAll(Classe::class);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('classe/listclasse.html.twig', [
            'classes' => $classes,
        ]);
        
        // Load HTML to Dompdf
        $dompdf->loadHtml($html);
        
        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("classes.pdf", [
            "Attachment" => true
        ]);
    }
        
    
    






    /**
     * @Route ("/suppclaase/{id}",name="suppClasse")
     */

    public function suppClasse($id): Response
    {
        $hasAccessStudent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessStudent){
        $em2=$this->getDoctrine()->getManager();
        $classe=$em2->getRepository(Classe::class)->find($id);
        $em=$this->getDoctrine()->getManager();
        $user=$em->getRepository(User::class)->findBy(['classe'=> $id]);
        foreach($user as $i){
        $i->setClasse(NULL);
        $em->flush($i);
        }
        
        $em2->remove($classe);
        $em2->flush();

        return $this->redirectToRoute('Classe');
    }
    return $this->render('/403.html.twig');

    }





     /**
     * @Route ("/searchclasse",name="searchclasse")
     * @param Request $request
     */

    public function searchclasse(Request $request)
    {
        
        $em=$this->getDoctrine()->getManager();


    $conn = mysqli_connect("localhost", "root", "", "edspace");

        
$sql = "SELECT * FROM classe where classe like '%".$request->request->get('name')."%'";
        
$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result)>0){
while ($row=mysqli_fetch_assoc($result)){
    print_r(' <tr class="a-item">
    <td></td>
    <td class="id">'.$row['id'].'</td>
    <td class="niveau">'.$row['niveau_id'].'</td>
    <td class="classe">'.$row['classe'].'</td>
    <td id="'.$row['id'].'" class="c-item"><a href="#">
         <i class="fas fa-list"></i> </a></td>
    <td class=""><a href="../classe/'.$row['id'].'">
        <i class="fas fa-user-plus"></i> </a></td>
    <td class="t-item" id="'.$row['id'].'"><a href="../suppclaase/'.$row['id'].'">
          <i  class="fas fa-trash-alt"></i> </a></td>
          </tr>
');

}
}
else{
    echo "<tr><td> 0 result found</td></tr>";
}
return new Response('success');
    }








    /**
     * @Route ("/addclaase",name="addClasse")
     * @param Request $request
     */

    public function addClasse(Request $request): Response
    {
           $hasAccessStudent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessStudent){
        if($request->request->count() > 0){
            $em1=$this->getDoctrine()->getRepository(Niveau::class);

            $em2=$this->getDoctrine()->getManager();
            $classe=new Classe();
            $classe->setClasse($request->request->get('classe'))
            ->setNiveau($em1->findOneBy(['id'=> $request->request->get('niveau')]));
            $em2->persist($classe);
            $em2->flush();
                

        }
        return $this->redirectToRoute('Classe');

    }
    return $this->render('/403.html.twig');

    }



    //l'ajout de l'etudiant
     /**
     * @Route ("/classe/{id}",name="classtoetudiant")
     * 
     * @param Request $request
     */

    public function addEt($id,Request $request): Response
    {
          $hasAccessStudent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessStudent){

        $em2=$this->getDoctrine()->getRepository(Classe::class);
        $classe=$em2->find($id);
        
        if($request->request->count() > 0){
            
            $em=$this->getDoctrine()->getManager();
            $user=$em->getRepository(User::class)->find($request->request->get('Etudiant'));
            $user->setClasse($em2->findOneBy(['id'=> $id]));
                $em->flush($user);
                
        }

        $em1=$this->getDoctrine()->getRepository(User::class);
        $etudiant=$em1->findBy(['classe'=> $id]);



        $etudiants=$em1->findAll();
         
        $et=[];
        if(count($etudiants)>0){
        foreach($etudiants as $i){
            if(is_null($i->getClasse()) ){
                if($i->getRoles()[0]=="ROLE_STUDENT"){
                $et[]=$i;
                }
            }
            else{
            if($i->getClasse()->getId() != $id){
                $et[]=$i;
            }}
        }}



        return $this->render('classe/classe.html.twig', [
            'classe'=>$classe,
            'etudiant' => $etudiant,
            'etudiants' => $et,
         
           
        ]);
    }
    return $this->render('/403.html.twig');


    }



           /**
     * @Route("/listet/{id}", name="listet")
     */
    public function listet($id): Response
    {
          $hasAccessStudent = $this->isGranted('ROLE_ADMIN');
        if($hasAccessStudent){
        $em2=$this->getDoctrine()->getRepository(Classe::class);
        $classe=$em2->find($id);
        
        $em1=$this->getDoctrine()->getRepository(User::class);
        $etudiant=$em1->findBy(['classe'=> $id]);
        
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        $em=$this->getDoctrine()->getRepository(Classe::class);
        $classes=$em->findAll(Classe::class);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('classe/listet.html.twig', [
            'etudiant' => $etudiant,
        ]);
        
        // Load HTML to Dompdf
        $dompdf->loadHtml($html);
        
        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("etudiants.pdf", [
            "Attachment" => true
        ]);
    }
    return $this->render('/403.html.twig');   

}



    /**
     * @return Response
     * @Route ("/classejson",name="classejson")
     */
    function JsonClasse(Request $request,ClasseRepository $repository,NormalizerInterface $normalizer){
       
        $datafinal = [];
        $data = $this->getDoctrine()->getRepository(Classe::class)->findAll();
        foreach ($data as $x) {
            $datafinal[]    = [
                'id' => $x->getId(),
                'niveau_id' => $x->getNiveau()->getId(),
                'classe' => $x->getClasse()
            ];
        }
        
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($datafinal);

        return new JsonResponse($formatted);
}


 /**
     * @return Response
     * @Route ("/suppclasse",name="suppclasse")
     * @param Request $request
     */
    function Suppc(Request $request, ClasseRepository $repository){

        $em2=$this->getDoctrine()->getManager();
        $classe=$em2->getRepository(Classe::class)->find($request->query->get("id"));
        $em=$this->getDoctrine()->getManager();
        $user=$em->getRepository(User::class)->findBy(['classe'=> $request->query->get("id")]);
        foreach($user as $i){
        $i->setClasse(NULL);
        $em->flush($i);
        }
        
        $em2->remove($classe);
        $em2->flush();

        return $this->json('Done');

    }





    /**
     * @return Response
     * @Route ("/addclasse",name="addclasse")
     * @param Request $request
     */
    function addc(Request $request, ClasseRepository $repository){

        $em1=$this->getDoctrine()->getRepository(Niveau::class);

        $em2=$this->getDoctrine()->getManager();
        $classe=new Classe();
        $classe->setClasse($request->get('classe'))
        ->setNiveau($em1->findOneBy(['id'=>$request->get('niveau')]));
        $em2->persist($classe);
        $em2->flush();

        return $this->json('Done');

    }


        /**
     * @return Response
     * @Route ("/updateclasse",name="updateclasse")
     * @param Request $request
     */
    function updatec(Request $request, ClasseRepository $repository){
        $em=$this->getDoctrine()->getManager();
  $em1=$this->getDoctrine()->getRepository(Niveau::class);
  $em2=$this->getDoctrine()->getRepository(Classe::class);
  $classe=$em2->find($request->get('id'));
  $classe->setClasse($request->get('classe'));
  $classe->setNiveau($em1->findOneBy(['id'=>$request->get('niveau')]));
  $em->persist($classe);
  $em->flush();
  return $this->json('Done');

    }

     /**
     * @return Response
     * @Route ("/listeclasse",name="listeclasse")
     * @param Request $request
     */
    function listec(Request $request, ClasseRepository $repository){
        $em1=$this->getDoctrine()->getRepository(User::class);
        $data=$em1->findBy(['classe'=> $request->get('id')]);


        $datafinal = [];
        
        foreach ($data as $x) {
            $datafinal[]    = [
                'id' => $x->getId(),
                'username' => $x->getUsername(),
                'prenom' => $x->getPrenom(),
                'email'=>$x->getEmail(),
            ];
        }
        
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($datafinal);

        return new JsonResponse($formatted);

  return $this->json('Done');

    }




    /**
     * @return Response
     * @Route ("/addtoclasse",name="addtoclasse")
     * @param Request $request
     */
    function addsjson(Request $request, ClasseRepository $repository){


        $em1=$this->getDoctrine()->getRepository(User::class);
        $etudiants=$em1->findAll();
        $test=0;
        foreach($etudiants as $i){
            if($i->GetEmail()==$request->get('email')){
                $test=1;
                
            }
        }
        if($test==1){
            
       $em2=$this->getDoctrine()->getRepository(Classe::class);
        $em=$this->getDoctrine()->getManager();
        $user=$em->getRepository(User::class)->findOneBy(['email' => $request->get('email')]);
        $user->setClasse($em2->findOneBy(['id'=> $request->get('id')]));
            $em->flush($user);

            $datafinal[]    = [
                'done' => "true",
            ];

            $serializer = new Serializer([new ObjectNormalizer()]);
            $formatted = $serializer->normalize($datafinal);
            return new JsonResponse($formatted);
            
        }
        $datafinal[]    = [
            'done' => "false",
        ];

        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($datafinal);
        return new JsonResponse($formatted);

    }




     /**
     * @return Response
     * @Route ("/etudiantsuppclasse",name="adsclasse")
     * @param Request $request
     */
    function suppetjson(Request $request, ClasseRepository $repository){


        
            
       $em2=$this->getDoctrine()->getRepository(Classe::class);
        $em=$this->getDoctrine()->getManager();
        $user=$em->getRepository(User::class)->findOneBy(['email' => $request->get('email')]);
        $user->setClasse(NULL);
            $em->flush($user);
            

            
            return $this->json('Done');
            

    }


     /**
     * @return Response
     * @Route ("/classefromuid",name="classefromuid")
     * @param Request $request
     */
    function classefromuid(Request $request, ClasseRepository $repository){


        
            
        $em2=$this->getDoctrine()->getRepository(Classe::class);
         $em=$this->getDoctrine()->getManager();
         $user=$em->getRepository(User::class)->findOneBy(['id' => $request->get('uid')]);
             $classe=$user->getClasse()->getId();
             $datafinal[]    = [
                'classe' => $classe,
            ];
 
            $serializer = new Serializer([new ObjectNormalizer()]);
            $formatted = $serializer->normalize($datafinal);
            return new JsonResponse($formatted);
 
     }





}
