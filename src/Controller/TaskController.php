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
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TaskController extends AbstractController
{
    /**
     * @Route("/admin/task/create", "task_create")
     */
    public function create(FormFactoryInterface $factory, Request $request, EntityManagerInterface $em): Response
    {
        $task = new Task;
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);


        if($form->isSubmitted()){
            $today = new DateTime();
            $task->setCreationDate($today->format('d-m-Y'));
            
            $em->persist($task);
            $em->flush();
        }

        $formView = $form->createView();

        return $this->render('task/create.html.twig', [
            'formView' => $formView,
        ]);
    }

    /**
     * @Route("/admin/task/edit/{id}", "task_edit")
     */
    public function edit(TaskRepository $taskRepository, $id, Request $request, EntityManagerInterface $em)
    {
        $task = $taskRepository->find($id);

        $form = $this->createForm(TaskType::class, $task);

        $formView = $form->createView();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $em->flush();
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'formView' => $formView
        ]);
    }
}
