<?php

namespace App\Controller;

use App\Entity\Invitation;
use DateTime;
use App\Entity\User;
use App\Entity\Project;
use App\Form\InvitationType;
use App\Form\ProjectInvitationType;
use App\Form\ProjectType;
use App\Repository\InvitationRepository;
use App\Repository\UserRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Constraints\Date;

class ProjectController extends AbstractController
{
    /**
     * @Route("project/{user_id}", name="project", priority=-1)
     */
    public function showAll($user_id, UserRepository $userRepository): Response
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
        $user = $userRepository->find($user_id);

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
            $project->setCreator($user);
            $em->persist($project);
            $em->flush();

            return $this->redirectToRoute('project_show', [
                'id' => $project->getId(),
                'project_id' => $project->getId()
            ]);
        }

        return $this->render('project/create.html.twig', [
            'formView' => $form->createView()
        ]);
    }

    /**
     * @Route("/project/{project_id}/show", name="project_show")
     */
    public function show($project_id, ProjectRepository $projectRepository, Request $request, UserRepository $userRepository, EntityManagerInterface $em, InvitationRepository $invitationRepository): Response
    {
        $project = $projectRepository->find($project_id);
        $user = $this->getUser();

        $formInvitation = $this->createForm(InvitationType::class);

        if (!$project) {
            throw $this->createNotFoundException("Le projet n'a pas été trouvé");
        }

        $contributors = ($project->getUsers()->toArray());
        $contributorsEmail = [];
        foreach ($contributors as $contributor) {
            $contributorsEmail[] = $contributor->getEmail();
        }

        $formInvitation->handleRequest($request);

        if ($formInvitation->isSubmitted() && $formInvitation->isValid()) {
            if ($user === $project->getCreator()) {
                $recipient = $request->request->get('invitation')['email'];
                $recipient = $userRepository->findOneBy(['email' => $recipient]);

                // dd($invitationRepository->findOneBy(['recipient' => $recipient]), in_array($recipient, $contributorsEmail) );
                // On vérifie si l'utilisateur a déjà été invité ou s'il fait déjà partie du projet
                if ($invitationRepository->findOneBy(['recipient' => $recipient]) || in_array($recipient->getEmail(), $contributorsEmail)) {
                    throw $this->createAccessDeniedException("L'utilisateur entré fait déjà partie du projet ou a déjà été invité");
                } else {
                    $invitation = new Invitation;
                    $invitation->setSender($user)
                        ->setRecipient($recipient)
                        ->setProject($project)
                        ->setStatus("En attente")
                        ->setCreationDate(new DateTime());

                    $em->persist($invitation);
                    $em->flush();

                    return $this->render('project/show.html.twig', [
                        'project' => $project,
                        'contributors' => $contributors,
                        'formView' => $formInvitation->createView(),
                        'projectInvitation' => $project->getInvitations()->toArray()
                    ]);
                }
            } else {
                throw $this->createAccessDeniedException("Vous n'êtes pas le créateur de la tache");
            }
        }



        if ($formInvitation->isSubmitted() && $formInvitation->isValid()) {
        }

        return $this->render('project/show.html.twig', [
            'project' => $project,
            'contributors' => $contributors,
            'formView' => $formInvitation->createView()
        ]);
    }
}
