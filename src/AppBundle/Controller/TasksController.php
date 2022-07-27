<?php

declare(strict_types=1);

namespace AppBundle\Controller;

use AppBundle\{Model\Task,Form\TaskType};
use FOS\RestBundle\{Controller\Annotations as Rest, Controller\FOSRestController, View\View};
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{Request,Response};

class TasksController extends FOSRestController
{
    /**
     * @Rest\Get("/tasks/{task}")
     * @Cache(maxage="60")
     */
    public function getTaskAction(Request $request, Task $task) : View
    {
       return new View($task, Response::HTTP_OK);
       //return $this->processTask($task, $request, 'GET', Response::HTTP_OK);
    }

    /**
     * Removes the Task resource
     * @Rest\Delete("/tasks/{task}")
     */
    public function deleteTaskAction(Task $task) : View
    {
        $this->get('task_repository')->delete($task);
        return new View([], Response::HTTP_OK);
    }

    /**
     * @Rest\Get("/tasks")
     * @Cache(maxage="30", public=false)
     */
    public function getTasksAction() : View
    {
        return new View($this->get('task_repository')->findAll(), Response::HTTP_OK);
    }

    /**
     * Replaces Task resource
     * @Rest\Put("/tasks/{task}")
     */
    public function updateTaskAction(Request $request, Task $task): View
    {
        return $this->putTaskAction($task, $request);
    }

    /**
     * Creates an Task resource
     * @Rest\Post("/tasks/post")
     */
    public function postTaskAction(Request $request): View
    {
        return $this->postTasksAction($request);
    }


    public function postTasksAction(Request $request) : View
    {
        return $this->processTask(new Task(), $request, 'POST', Response::HTTP_CREATED);
    }

    public function putTaskAction(Task $task, Request $request) : View
    {
        return $this->processTask($task, $request, 'PUT', Response::HTTP_OK);
    }

    private function processTask(Task $task, Request $request, string $verb, int $successResponseCode) : View
    {
        /** @var FormFactoryInterface $formFactory */
        $formFactory = $this->container->get('form.factory');
        $form = $formFactory->createNamed('', TaskType::class, $task, ['method' => $verb]);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            return new View($task, Response::HTTP_UNPROCESSABLE_ENTITY);
        };

        $this->get('task_repository')->save($task);

        return new View($task, $successResponseCode);
    }
}
