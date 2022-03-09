<?php

namespace App\Controller;

use App\Repository\CategorieEmploiRepository;
use App\Repository\CategorieNewsRepository;
use App\Repository\ClasseRepository;
use App\Repository\ClubRepository;
use App\Repository\DocumentRepository;
use App\Repository\EmploiRepository;
use App\Repository\MatiereRepository;
use App\Repository\NiveauRepository;
use App\Repository\PublicationNewsRepository;
use App\Repository\UserRepository;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/index", name="app_index")
     */
    public function index(): Response
    {
        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }
     /**
     * @Route("/publication", name="publication")
     */
    public function indexChart(PublicationNewsRepository $repo, ChartBuilderInterface $chartBuilder,CategorieEmploiRepository $Erepo, CategorieNewsRepository $catRepo, EmploiRepository $E_Repo, NiveauRepository $nRepo, MatiereRepository $mRepo, DocumentRepository $dRepo, ClasseRepository $classeRepo, UserRepository $userRepo, ClubRepository $clubRepo): Response
    {
        $hasAccessStudent = $this->isGranted('ROLE_ADMIN');
        $template='';
        //publications by category data
        $categories = $catRepo->findProductsOfCategory();
        $labels = array();
        $dataset = array();
        Foreach($categories as $category){
            array_push($labels,$category->getCategoryName());
            array_push($dataset, count($repo->findProductsOfCategory($category->getId())));
        }
        $chart = $chartBuilder->createChart(Chart::TYPE_PIE);
        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'N Publications Par Categorie',
                    'backgroundColor' => [
                        'rgb(255, 05, 86)',
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(255, 5, 86)',
                    ],
                    'borderColor' => 'rgb(255, 255, 255)',
                    'data' => $dataset,
                ],
            ],
        ]);
        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 50,
                ],
            ],
        ]);

        //line chart 
        // number of likes of each publication
        $publications = $repo->limitPublications();
        $pubData = array();
        $likess = array();
        ForEach($publications as $publication){
            array_push($pubData, $publication->getTitle());
            array_push($likess,$repo->findAllLikesByPublication($publication->getId()));
        }
        $chartLine = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chartLine->setData([
            'labels' => $pubData,
            'datasets' => [
                [
                    'label' => 'Nombre des Intéractions Par Publication',
                    'backgroundColor' => [
                            'rgb(54, 162, 235)',
                 ],
                    'borderColor' => 'rgb(255, 255, 255)',
                    'data' => $likess,
                ],
            ],
        ]);
        $chartLine->setOptions([
            'scales' => [
                'y' => [
                   'suggestedMin' => 0,
                   'suggestedMax' => 40,
                ],
         ],
     ]);
     //doughnut
     $catEmplois = $Erepo->findEmploisOfCategory();
     $labels_emploi = array();
     $EmploiData = array();
     foreach($catEmplois as $category){
         array_push($labels_emploi, $category->getCategoryName());
         array_push($EmploiData, count($E_Repo->ListEmploiByCategory($category->getId())));
     }
     $chartEmploi = $chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $chartEmploi->setData([
            'labels' => $labels_emploi,
            'datasets' => [
                [
                    'label' => 'N Publications Par Categorie',
                    'backgroundColor' => [
                        'rgb(255, 05, 86)',
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(255, 5, 86)',
                    ],
                    'borderColor' => 'rgb(255, 255, 255)',
                    'data' => $EmploiData,
                ],
            ],
        ]);
        $chartEmploi->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 50,
                ],
            ],
        ]);
        if(!$hasAccessStudent){
            $template = '/home.html.twig';
            }else{
                $template = '/403.html.twig';
            }

        return $this->render($template, [
            'controller_name' => 'PublicationNewsController',
            'publicationCount' => $repo->CountPublications(),
            'emplois' => $E_Repo->CountEmploi(),
            'chart' => $chart,
            'chartLine' => $chartLine,
            'test' => $dataset,
            'chartEmploi' => $chartEmploi,
            'publications' => $publications,
            'niveaux' => $nRepo->CountNiveaux(),
            'matieres' => $mRepo->CountMatieres(),
            'documents' =>$dRepo->CountDocuments(),
            'classes' => $classeRepo->CountClasse(),
            'Users' => $userRepo->CountUsers('ROLE_STUDENT','ROLE_RESPONSABLE'),
            'Clubs' => $clubRepo->CountClubs(),
        ]);
    }
}
