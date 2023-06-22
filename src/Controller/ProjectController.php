<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends AbstractController
{
    /**
     * @Route("project/{id}", name="project")
     */
    public function showAll($id, UserRepository $userRepository): Response
    {
        /**
         * @var User $user
         */
        // On récupère l'utilisateur connecté
        $loggedUser = $this->getUser();

        if (!$loggedUser) {
            throw $this->createAccessDeniedException("Veuillez vous connecter pour pouvoir voir vos projets");
        }

        // On cherche l'utilisateur avec l'id passé dans l'url
        $user = $userRepository->find($id);

        // Si l'utilisateur connecté et l'utilisateur passé dans l'url sont les memes
        if ($loggedUser === $user) {
            // On récupère ses projets créés
            $userProjectsQuery = $userRepository->findUserWithProjects($user->getId());
            $userProjects = $userProjectsQuery->getProjects();

            // Conversion sous forme d'array
            $projects = $userProjects->toArray();

            return $this->render('project/index.html.twig', [
                'projects' => $projects,
            ]);
        } else {
            throw $this->createAccessDeniedException("Vous n'êtes pas l'utilisateur recherché");
        }


        return $this->render('project/index.html.twig', [
            // 'projects' => $projects,
        ]);
    }
}
