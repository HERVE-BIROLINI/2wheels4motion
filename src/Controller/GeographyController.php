<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/localisation", name="loc_")
 */
class GeographyController extends AbstractController
{
    /**
     * @Route("_geographic", name="index")
     */
    public function index(): Response
    {
        return $this->render('geography/index.html.twig', [
            'controller_name' => 'GeographyController',
        ]);
    }
}
