<?php

namespace App\Controller;

use App\Entity\Project;
use DateTime;
use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TaskController extends AbstractController
{

    protected $taskRepository;
    protected $projectRepository;
    protected $em;

    public function __construct(TaskRepository $taskRepository, ProjectRepository $projectRepository, EntityManagerInterface $em)
    {
        $this->taskRepository = $taskRepository;
        $this->projectRepository = $projectRepository;
        $this->em = $em;
    }

    public function permissionTest(Task $task = null, Project $project = null, User $user = null)
    {
        if ($task != null) {
            if (!$task) {
                throw $this->createNotFoundException("Cette tache n'existe pas");
            }
        }

        if ($project != null) {
            if (!$project) {
                throw $this->createNotFoundException("Ce projet n'existe pas");
            }
        }

        if (!$user) {
            throw $this->createAccessDeniedException("Veillez vous connecter");
        }

        if ($user != null && $project != null) {
            if ($user !== $project->getCreator()) {
                dd($user, $project->getCreator());
                throw $this->createAccessDeniedException("Vous n'êtes pas le créateur du projet");
            }
        }
    }
    /**
     * @Route("project/task/{task_id}/show", name="task_show")
     */
    public function show($task_id)
    {
        $task = $this->taskRepository->find($task_id);

        $this->permissionTest($task);
        // if(!$task)
        // {
        //     throw $this->createNotFoundException("Cette tache n'existe pas");
        // }

        return $this->render('task/show.html.twig', [
            'task' => $task
        ]);
    }

    /**
     * @Route("project/{project_id}/task/create", name="task_create")
     */
    public function create($project_id, Request $request): Response
    {
        $task = new Task;
        $project = $this->projectRepository->find($project_id);
        $user = $this->getUser();

        $this->permissionTest($task, $project, $user);



        $today = new DateTime();
        $task->setCreationDate($today);

        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setOwner($user);

            $task->setProject($project);

            $this->em->persist($task);
            $this->em->flush();
            /**
             * @var User $user
             */
            return $this->redirectToRoute('project_show', [
                'project_id' => $project_id
            ]);
        }


        return $this->render('task/create.html.twig', [
            'formView' => $form->createView(),
        ]);
    }

    /**
     * @Route("/project/{project_id}/task/{task_id}/edit", name="task_edit")
     */
    public function edit($task_id, $project_id, Request $request)
    {
        $task = $this->taskRepository->find($task_id);
        $project = $this->projectRepository->find($project_id);
        $user = $this->getUser();

        $this->permissionTest($task, $project, $user);

        // if (!$task) {
        //     throw $this->createNotFoundException("Cette tache n'existe pas");
        // }

        // if (!$project) {
        //     throw $this->createNotFoundException("Ce projet n'existe pas");
        // }

        // if (!$user) {
        //     throw $this->createAccessDeniedException("Veillez vous connecter");
        // }

        // /**
        //  * @var User $user
        //  */
        // if (!$user->getId() !== $project->getCreator()) {
        //     throw $this->createAccessDeniedException("Vous n'êtes pas le créateur du projet");
        // }

        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            return $this->redirectToRoute('project_show', [
                'project_id' => $project_id,
                'task_id' => $task->getId()
            ]);
        }

        $formView = $form->createView();

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'formView' => $formView
        ]);
    }

    /**
     * @Route("/project/{project_id}/task/{task_id}/finished", name="task_finished")
     */
    public function finished($task_id, $project_id): Response
    {
        $user = $this->getUser();
        $project = $this->projectRepository->find($project_id);
        $task = $this->taskRepository->find($task_id);

        $this->permissionTest($task, $project, $user);

        // if(!$task)
        // {
        //     throw $this->createNotFoundException("Cette tache n'existe pas");
        // }

        // /**
        //  * @var User $user
        //  */
        // if (!$user->getId() !== $project->getCreator()) {
        //     throw $this->createAccessDeniedException("Vous n'êtes pas le créateur du projet");
        // }

        /**
         * @var Task $task
         */
        $task->isFinished() ? $task->setFinished(false) : $task->setFinished(true);

        $this->em->persist($task);
        $this->em->flush();

        return $this->redirectToRoute('project_show', [
            'project_id' => $project_id
        ]);
    }

    /**
     * @Route("/project/{project_id}/task/{task_id}/delete", name="task_delete")
     */
    public function delete($project_id, $task_id): Response
    {
        $user = $this->getUser();
        $task = $this->taskRepository->find($task_id);

        $this->permissionTest($task, $project = null, $user);

        if ($user === $task->getOwner()) {
            $this->em->remove($task);
            $this->em->flush();
        } else {
            throw $this->createAccessDeniedException("Vous n'êtes pas le créateur de la tache");
        }



        return $this->redirectToRoute("project_show", [
            'project_id' => $project_id,
            'task_id' => $task_id
        ]);
    }
}
