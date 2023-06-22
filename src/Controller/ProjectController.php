<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\UserRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProjectController extends AbstractController
{
    /**
     * @Route("project/{id}", name="project", priority=-1)
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

    /**
     * @Route("/project/create", name="project_create")
     */
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $project = new Project;

        $today = new DateTime();
        $project->setCreationDate($today);

        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException("Veuillez vous connecter");
        }

        $form = $this->createForm(ProjectType::class, $project);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $project->addUser($user);
            $em->persist($project);
            $em->flush();

            return $this->redirectToRoute('project_show', [
                'id' => $project->getId()
            ]);
        }
        
        return $this->render('project/create.html.twig', [
            'formView' => $form->createView()
        ]);
    }

    /**
     * @Route("/project/{id}/show", name="project_show")
     */
    public function show($id, ProjectRepository $projectRepository): Response
    {
        $project = $projectRepository->find($id);

        if (!$project) {
            throw $this->createNotFoundException("Le projet n'a pas été trouvé");

        }
        return $this->render('project/show.html.twig', [
            'project' => $project
        ]);
    }
}
