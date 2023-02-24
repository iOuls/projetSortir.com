<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RememberController extends AbstractController
{
    /**
     * Se souvenir de moi
     * @return Response
     */
    #[Route('/remember', name: 'app_remember')]
    public function index(): Response
    {
        return $this->render('remember/index.html.twig', [
            'controller_name' => 'RememberController',
        ]);
    }
}
