<?php

namespace App\Controller;

use App\Entity\Driver;
// use App\Entity\User;
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
     * @Route("s/presentation", name="presentation", methods={"GET","POST"})
     */
    public function driversPresentation(DriverRepository $drivers): Response
    {
        if(isset($_POST['region']) and $_POST['region']!="all")
        {
            $region=$_POST['region'];
        }
        else{
            $region=null;
        }

        if(isset($_POST['dept']))
        {
            $dept=$_POST['dept'];
        }
        else{
            $dept=null;
        }
// dd($drivers);
        return $this->render('driver/driverspresentation.html.twig', [
            'controller_name' => 'DriverController',
            'drivers'   => $drivers->findAll(),
            'region'    => $region,
            'dept'      => $dept,
        ]);
    }

    /**
     * @Route("{id}/presentation", name="profile")
     */
    public function profiledriver(Driver $obDriver = null, DriverRepository $drivers
    ): Response
    {
        dd('NON UTILISE  -  Présentation de la fiche DU pilote au Public (DriverController)');
        // test si l'utilisateur N'est PAS encore identifié
        if(!$obDriver ){//or !$obDriver=$drivers->findOneBy(['id'=>$driver])){
            return $this->redirectToRoute('driver_presentation');
        }
        return $this->render('driver/profile.html.twig', [
            'controller_name' => 'DriverController',
            //
            'oDriver' => $obDriver,
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
