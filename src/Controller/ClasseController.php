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

class ClasseController extends AbstractController
{

    /**
     * @Route("/classe", name="Classe")
     */
    public function index(): Response
    {

            
        $em1=$this->getDoctrine()->getRepository(Niveau::class);
        $niveau=$em1->findAll(Niveau::class);
        

        

        
       

        $em=$this->getDoctrine()->getRepository(Classe::class);
        $classes=$em->findAll(Classe::class);

 

        return $this->render('classe/classes.html.twig', [
            'classes' => $classes,
            'niveau'=> $niveau,
        ]);
    }
    



       /**
     * @Route("/listc", name="listc")
     */
    public function listeClasse(): Response
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



    //l'ajout de l'etudiant
     /**
     * @Route ("/classe/{id}",name="classtoetudiant")
     * 
     * @param Request $request
     */

    public function addEt($id,Request $request): Response
    {

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
                if($i->getRoles()=="ROLE_STUDENT"){
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



           /**
     * @Route("/listet/{id}", name="listet")
     */
    public function listet($id): Response
    {
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
        



}
