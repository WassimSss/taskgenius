<?php

namespace App\Controller;

use DateTime;
use App\Entity\Task;
use App\Form\TaskType;
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
     * @Route("/admin/task/show/{id}", name="task_show")
     */
    public function show($id, TaskRepository $taskRepository)
    {
        $task = $taskRepository->find($id);

        return $this->render('task/show.html.twig', [
            'task' => $task
        ]);
    }

    /**
     * @Route("/admin/task/create", name="task_create")
     */
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $task = new Task;

        $today = new DateTime();
        $task->setCreationDate($today);

        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        

        if($form->isSubmitted() && $form->isValid()){

            $em->persist($task);
            $em->flush();

            return $this->redirectToRoute('task_show', [
                'id' => $task->getId()
            ]);
            
        }


        return $this->render('task/create.html.twig', [
            'formView' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/task/edit/{id}", name="task_edit")
     */
    public function edit(TaskRepository $taskRepository, $id, Request $request, EntityManagerInterface $em)
    {
        $task = $taskRepository->find($id);

        // if (!$task) {
        //     throw $this->createNotFoundException("Cette tache n'existe pas");
        // }



        // if($user === $task->getOwner()){
        //     throw $this->createAccessDeniedException("Vous n'avez pas accès a cette page");
        // }
        $this->denyAccessUnlessGranted('edit', $task);

        $form = $this->createForm(TaskType::class, $task);

        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            
            return $this->redirectToRoute('task_show', [
                'id' => $task->getId()
            ]);
        }
        
        $formView = $form->createView();
        
        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'formView' => $formView
        ]);
    }


}
