<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GeographyController extends AbstractController
{
    /**
     * @Route("geography", name="geography")
     */
    public function index(): Response
    {
        return $this->render('geography/index.html.twig', [
            'controller_name' => 'GeographyController',
        ]);
    }
}
