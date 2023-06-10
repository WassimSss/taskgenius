<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register ', name: 'register')]

    public function index(): Response
    {
        return $this->render('register/register.html.twig', [
            'controller_name' => 'RegistrationController',
        ]);
    }
}
