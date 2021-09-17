<?php

namespace App\Controller;

use App\Entity\Claim;
use App\Entity\ClaimStatus;
// use App\Entity\Driver;
// use App\Entity\Status;
// use App\Entity\User;
use App\Repository\DriverRepository;
use App\Twig\FrenchGeographyTwig;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/customer", name="customer_")
 */
class CustomerController extends AbstractController
{
/*
    /**
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
    /**
     * @Route("/switchclaimstatusarchived{id}", name="switchclaimstatus_archived")
     */
    public function switchClaimStatus_archived(Claim $claim){
        // Pour les lectures et enregistrements dans la BdD
        $entityManager=$this->getDoctrine()->getManager();
        // Récupère les ClaimStatus relatifs à la Claim demandée
        $arClaimStatus=$entityManager->getRepository(ClaimStatus::class)->findBy(['claim'=>$claim ]);
        // Fonctionnement type interrupteur : observe l'état actuel du 1er enregistrement
        $Isarchivedbycustomer=$arClaimStatus[0]->getIsarchivedbycustomer()==false;
        // Itère sur chaque enregistrement 
        foreach($arClaimStatus as $claimStatus){
            $claimStatus->setIsarchivedbycustomer($Isarchivedbycustomer);
            //
            $entityManager->persist($claimStatus);
        }
        // "remplissage" de la BdD
        $entityManager->flush();
        // retour à la page précédente
        return $this->redirect($_SERVER['HTTP_REFERER']);
    }
}
