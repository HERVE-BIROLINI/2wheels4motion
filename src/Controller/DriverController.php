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
        
        return $this->render('driver/driverspresentation.html.twig', [
            'controller_name' => 'DriverController',

            // 'drivers'   => $drivers->findAll(),
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

    /**
     * @Route("{driver}/confirm", name="confirm", methods={"GET","POST"}, requirements={"company":"\d+"})
     */
    public function driverConfirm(Driver $driver): Response
    {
        dd("coder la vérification du Driver par l'administrateur...");
        // // test si l'utilisateur N'est PAS encore identifié...
        // if(!$this->getUser() or !in_array("ROLE_ADMIN",$this->getUser()->getRoles())){
        //     // ... renvoi vers la page de connection
        //     return $this->redirectToRoute('app_login');
        // }

        // $entityManager = $this->getDoctrine()->getManager();
        // if($entityManager->getRepository(Driver::class)->findOneBy(['id'=>$driver->getId()])) {
        //     $driver->setIsconfirmed(True);
        //     $entityManager->flush();
        // }

        // return $this->redirectToRoute('admin_index', [
        //     'allcompaniesunknown'=>$entityManager->getRepository(Company::class)->findBy(['isconfirmed'=>null]),
        // ]);
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
