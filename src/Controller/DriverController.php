<?php

namespace App\Controller;

use App\Entity\Driver;
use App\Entity\User;
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
     * @Route("{id}/presentation", name="profile")
     */
    public function profiledriver(Driver $driver = null, DriverRepository $drivers
    ): Response
    {
        // test si l'utilisateur N'est PAS encore identifié
        if(!$driver ){//or !$obDriver=$drivers->findOneBy(['id'=>$driver])){
            return $this->redirectToRoute('driver_presentation');
        }
        return $this->render('driver/profile.html.twig', [
            'controller_name' => 'DriverController',
            //
            'oDriver' => $driver,
            // 'oDriver' => $obDriver,
        ]);
    }


    // /**
    //  * @Route("s", name="index")
    //  */
    // public function index(DriverRepository $drivers): Response
    // {
    //     return $this->render('driver/index.html.twig', [
    //         'controller_name' => 'DriverController'
    //     ]);
    // }
}
