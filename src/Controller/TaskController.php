<?php

namespace Csvtool\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TaskController extends AbstractController
{
    #[Route('/env', name: 'task_env', methods: ['GET'])]
    public function env(Request $request): REsponse
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
}
