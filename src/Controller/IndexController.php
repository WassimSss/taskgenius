<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(UserRepository $userRepository)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        // Si l'utilisateur est connecté
        if ($user) {
            // On récupère ses projets créés
            $userProjectsQuery = $userRepository->findUserWithProjects($user->getId());
            $userProjects = $userProjectsQuery->getProjects();

            // Conversion sous forme d'array
            $projects = $userProjects->toArray();

            // On affichera seulement les 3 premiers projets, on récupère le nombre de projets qui ne sont pas encore été affichés
            $otherProjectsNumber = count($projects) - 3;

            // Puis qu'on coupe pour garder seulement les 3 premiers
            $projects = array_slice($projects, 0, 3);

            return $this->render('index.html.twig', [
                'projects' => $projects,
                'otherProjectsNumber' => $otherProjectsNumber
            ]);
        } else {
            throw $this->createAccessDeniedException("Veuillez vous connecter");
        }


        return $this->render('index.html.twig', [
            
        ]);
    }
}
