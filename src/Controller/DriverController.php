<?php

namespace App\Controller;

use App\Repository\DriverRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/driver", name="driver_")
 */
class DriverController extends AbstractController
{
    /**
     * @Route("s/presentation", name="presentation")
     */
    public function driversPresentation(DriverRepository $drivers): Response
    {
        return $this->render('driver/driverspresentation.html.twig', [
            'controller_name' => 'DriverController',
            'drivers' => $drivers->findAll(),
        ]);
    }


    /**
     * @Route("s", name="index")
     */
    public function index(DriverRepository $drivers): Response
    {
        return $this->render('driver/index.html.twig', [
            'controller_name' => 'DriverController'
        ]);
    }
}
