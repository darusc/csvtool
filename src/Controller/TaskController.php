<?php

namespace Csvtool\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TaskController extends AbstractController
{
    #[Route('/env', name: 'task_env', methods: ['GET'])]
    public function env(Request $request): Response
    {
        return new JsonResponse([
            'method' => $request->getMethod(),
            'query' => $request->query->all(),
            'request' => $request->request->all(),
            'headers' => $request->headers->all(),
            'cookies' => $request->cookies->all(),
            'server' => $request->server->all(),
        ]);
    }

    #[Route('/redirect/{timeout}', name: 'tasks', methods: ['GET'])]
    public function redirectTimeout(Request $request, int $timeout): Response
    {
        sleep($timeout);
        return new RedirectResponse('/redirected');
    }

    #[Route('attributes', name: 'task_attributes', methods: ['GET'])]
    public function attributes(Request $request): Response
    {
        // Dump attributes to see attribute added by the request listener
        var_dump($request->attributes->all());
        return new Response();
    }
}
