<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
    * @Route("/", name="index")
    */
    public function index()
    {
        $priorityArray = ["Haute", "Moyenne", "Basse", "Optionnel"];
        $priority = $priorityArray[mt_rand(0,3)];
        
        return $this->render('index.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }
}
