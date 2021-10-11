<?php

namespace App\Twig;

use App\Entity\Claim;
use App\Entity\ClaimStatus;
use App\Entity\Customer;
use App\Entity\Driver;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\Criteria;

class ClaimstatusTwig extends AbstractExtension
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Déclaration des extensions de functions TWIG
    // (... utilisées essentiellement dans le Template/Profile/Driver...)
    public function getFunctions(){
        return [
            // fonctions des objets Claim et Claim_Status associées au Driver
            new TwigFunction('getclaimstatus4driver', [$this, 'getclaimstatus4driver']),
            new TwigFunction('getstatus4claimanddriver', [$this, 'getStatus4ClaimAndDriver']),
            // fonctions des objets Claim et Claim_Status associées au Customer
            new TwigFunction('getclaimstatus4customer', [$this, 'getclaimstatus4customer']),
            new TwigFunction('getstatus4claimofcustomer', [$this, 'getStatus4ClaimOfCustomer']),
        ];
    }

    // Déclaration des filters TWIG
    public function getFilters(){
        return [
            // new TwigFilter('cast_to_array', array($this, 'castClassToArray')),
        ];
    }

    // *************************************************************

    // ** Les méthodes liées aux extensions de fonctions **
    // fonctions des objets Claim et Claim_Status associées au Driver
    public function getclaimstatus4driver(Driver $driver, $bWithArchived){
        $arAllClaims=$this->entityManager->getRepository(ClaimStatus::class)->findBy(['driver'=>$driver]);
        $arResult=[];
        foreach($arAllClaims as $claimStatus){
            if($bWithArchived || !$claimStatus->getIsarchivedbydriver() || $claimStatus->getIsarchivedbydriver()==false || $claimStatus->getIsarchivedbydriver()==0){
                array_push($arResult, $claimStatus->getClaim());
            }
        }
        return $arResult;
    }
    public function getStatus4ClaimAndDriver(Claim $claim, $driver){
        return $this->entityManager->getRepository(ClaimStatus::class)->findOneBy(['claim'=>$claim,'driver'=>$driver]);
    }
    
    // fonctions des objets Claim et Claim_Status associées au Customer
    public function getclaimstatus4customer(Customer $customer, $bWithArchived){
        // commence par rechercher les ID de TOUTES les Claims créer par le Customer en demande
        $arAllClaims=$customer->getClaims();
        $arId=[];
        foreach($arAllClaims as $claimStatus){
            array_push($arId, $claimStatus->getId());
        }
        
        //
        $arClaimStatus=$this->entityManager->getRepository(ClaimStatus::class)->findBy(['claim'=>$arId], array('id' => 'ASC'));
        
        // selon la demande avec ou sans les "archives"...
        if(!$bWithArchived){
            $arArchivedClaimStatus=$this->entityManager->getRepository(ClaimStatus::class)->findBy(['claim'=>$arId, 'isarchivedbycustomer'=>true], array('id' => 'ASC'));
            //
            foreach($arArchivedClaimStatus as $archivedClaimStatus){
                unset($arClaimStatus[array_search($archivedClaimStatus, $arClaimStatus)]);
            }
            
        }

        // récupère les Claims à partir des ClaimStatus trouvé(s)
        $arResult=[];
        foreach($arClaimStatus as $claimStatus){
            // supprime les doublons
            if(!in_array($claimStatus->getClaim(), $arResult)){
                array_push($arResult, $claimStatus->getClaim());
            }
        }
        //
        return $arResult;
    }
    public function getStatus4ClaimOfCustomer(Claim $claim, Customer $customer){
        // commence par rechercher les ID de TOUTES les Claims créer par le Customer en demande
        $arAllClaims=$customer->getClaims();
        if($arAllClaims->contains($claim)){
            return $this->entityManager->getRepository(ClaimStatus::class)->findBy(['claim'=>$claim ]);
        }
    }

    // fonctions des objets Claim et Claim_Status associées au Cutomer




    // *************************************************************

    // ** Les méthodes liées aux filtres **
    //
    // public function castClassToArray($stdClassObject) {
    //     $response = array();
    //     foreach ($stdClassObject as $key => $value) {
    //         $response[] = array($key, $value);
    //     }
    //     return $response;
    // }

}

?>