<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserIdController extends AbstractController
{
    /**
     * @Route("/user/id", name="user_id")
     */
    public function index(): Response
    {
        return $this->render('user_id/index.html.twig', [
            'controller_name' => 'UserIdController',
        ]);
    }


}
