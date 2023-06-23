<?php

namespace App\Controller;

use DateTime;
use App\Entity\Task;
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
    /**
     * @Route("project/task/{task_id}/show", name="task_show")
     */
    public function show($task_id, TaskRepository $taskRepository)
    {
        $task = $taskRepository->find($task_id);

        return $this->render('task/show.html.twig', [
            'task' => $task
        ]);
    }

    /**
     * @Route("project/{project_id}/task/create", name="task_create")
     */
    public function create($project_id, Request $request, EntityManagerInterface $em, ProjectRepository $projectRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException("Veillez vous connecter");
        }
        $task = new Task;

        $today = new DateTime();
        $task->setCreationDate($today);

        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setOwner($user);

            $project = $projectRepository->find($project_id);
            $task->setProject($project);

            $em->persist($task);
            $em->flush();
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
     * @Route("/project/{project_id}task/{task_id}/edit", name="task_edit")
     */
    public function edit(TaskRepository $taskRepository, $task_id, $project_id, Request $request, EntityManagerInterface $em)
    {
        $task = $taskRepository->find($task_id);

        if (!$task) {
            throw $this->createNotFoundException("Cette tache n'existe pas");
        }

        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

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
     * @Route("/project/{project_id}task/{task_id}/finished", name="task_finished")
     */
    public function finished($task_id, $project_id, TaskRepository $taskRepository, EntityManagerInterface $em): Response
    {
        $task = $taskRepository->find($task_id);

        /**
         * @var Task $task
         */
        $task->isFinished() ? $task->setFinished(false) : $task->setFinished(true);

        $em->persist($task);
        $em->flush();

        return $this->redirectToRoute('project_show', [
            'project_id' => $project_id
        ]);
    }

    /**
     * @Route("/project/{project_id}task/{task_id}/delete", name="task_delete")
     */
    public function delete($project_id, $task_id, TaskRepository $taskRepository, EntityManagerInterface $em):Response
    {
        $user = $this->getUser();

        $task = $taskRepository->find($task_id);

        if ($user === $task->getOwner()) {
            $em->remove($task);
            $em->flush();
        } else {
            throw $this->createAccessDeniedException("Vous n'êtes pas le créateur de la tache");
        }



        return $this->redirectToRoute("project_show", [
            'project_id' => $project_id,
            'task_id' => $task_id
        ]);

    }
}
