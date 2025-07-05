<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController {
    #[Route('/', name: 'homepage')]
    public function index(): Response {
        return $this->render('home/index.html.twig');
    }

    #[Route('/{jobId}', name: 'job-detail', requirements: ['jobId' => '\d+'])]
    public function jobDetail(int $jobId): Response {
        return $this->render('home/job.html.twig', [
            'jobId' => $jobId,
        ]);
    }
}
