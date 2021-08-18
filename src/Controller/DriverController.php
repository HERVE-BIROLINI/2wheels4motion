<?php

namespace App\Controller;

use App\Entity\Driver;
// use App\Entity\User;
use App\Repository\DriverRepository;
use App\Twig\FrenchGeographyTwig;
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
        // Pour les lectures et enregistrements dans la BdD
        $entityManager=$this->getDoctrine()->getManager();
        
        $obFGTwig=new FrenchGeographyTwig($entityManager);
        if(isset($_POST['region']) and $_POST['region']!="all")
        {
            // $region=$_POST['region'];
            $region=$obFGTwig->getRegionByCode($_POST['region']);
        }
        else{
            $region=null;
        }
        //
        if(isset($_POST['dept'])
            and ($dept=$obFGTwig->getDepartmentByCode($_POST['dept']) or true)
            and $dept
            )
        {
            $region=$obFGTwig->getRegionByCode($dept->region_code);
        }
        else{
            $dept=null;
        }
        
        return $this->render('driver/driverspresentation.html.twig', [
            'controller_name' => 'DriverController',
            //
            'drivers'   => $drivers->findBy(['is_verified'=>true]),
            'region'    => $region,
            'dept'      => $dept,
        ]);
    }

    /**NON UTILISE
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
