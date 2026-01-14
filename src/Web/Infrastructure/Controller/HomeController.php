<?php

declare(strict_types=1);

namespace App\Web\Infrastructure\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('plus_ui/index.html.twig');
    }

    #[Route('/components', name: 'components', methods: ['GET'])]
    public function components(): Response
    {
        return $this->render('plus_ui/components.html.twig');
    }
}
