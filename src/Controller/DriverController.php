<?php

namespace App\Controller;

use App\Entity\ClaimStatus;
use App\Entity\TenderStatus;
// use App\Entity\Driver;
// use App\Entity\Status;
// use App\Entity\User;
use App\Repository\DriverRepository;
use App\Twig\FrenchGeographyTwig;
use Doctrine\ORM\EntityManager;
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
            $region=$obFGTwig->getRegionByCode($_POST['region']);
        }
        else{
            $region=null;
        }
        //
        if(isset($_POST['dept'])
            and ($dept=$obFGTwig->getDepartmentByCode($_POST['dept']) or true)
            and $dept
        ){
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

/*
    / **
     * @Route("/switchclaimstatusviewed{id}", name="switchclaimstatus_viewed")
     * /
    public function switchClaimStatus_viewed(ClaimStatus $claimStatus){
        
        // Pour les lectures et enregistrements dans la BdD
        $entityManager=$this->getDoctrine()->getManager();

        if($claimStatus->getIsread()){
            $claimStatus->setIsread(false);
        }else{
            $claimStatus->setIsread(true);
        }
        //
        $entityManager->persist($claimStatus);
        // "remplissage" de la BdD
        $entityManager->flush();
        // retour à la page
        return $this->redirect($_SERVER['HTTP_REFERER']);
    }
*/

/*
    / **
     * @Route("/switchclaimstatusarchived{id}", name="switchclaimstatus_archived")
     * /
    public function switchClaimStatus_archived(ClaimStatus $claimStatus){
        
        // Pour les lectures et enregistrements dans la BdD
        $entityManager=$this->getDoctrine()->getManager();

        // if($claimStatus=$entityManager->getRepository(ClaimStatus::class)->findOneBy(['id'=>$claimStatus_ID])){
            if($claimStatus->getIsarchivedbydriver()){
                $claimStatus->setIsarchivedbydriver(false);
            }else{
                $claimStatus->setIsarchivedbydriver(true);
            }
            //
            $entityManager->persist($claimStatus);
            // "remplissage" de la BdD
            $entityManager->flush();
        // }
        // retour à la page précédente
        return $this->redirect($_SERVER['HTTP_REFERER']);
    }
*/

/*
    / **
     * @Route("/switchtendertatusarchived{id}", name="switchtenderstatus_archived")
     * /
    public function switchTenderStatus_archived(TenderStatus $tenderStatus){
        
        // Pour les lectures et enregistrements dans la BdD
        $entityManager=$this->getDoctrine()->getManager();

        if($tenderStatus->getIsarchivedbydriver()){
            $tenderStatus->setIsarchivedbydriver(false);
        }else{
            $tenderStatus->setIsarchivedbydriver(true);
        }
        //
        $entityManager->persist($tenderStatus);
        // "remplissage" de la BdD
        $entityManager->flush();
        // retour à la page précédente
        return $this->redirect($_SERVER['HTTP_REFERER']);
    }
*/

}
