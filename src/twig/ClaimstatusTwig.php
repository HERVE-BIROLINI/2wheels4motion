<?php

namespace App\Twig;

use App\Entity\ClaimStatus;
use App\Entity\Driver;
use App\Entity\Company;
use App\Entity\Socialreason;
use App\Entity\Tva;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
// use App\Repository\DriverRepository;
// use App\Repository\CompanyRepository;
// use App\Repository\PicturelabelRepository;
// use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
// use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
// use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
// use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class ClaimstatusTwig extends AbstractExtension
{

    private $entityManager;
    // private $urlGenerator;
    // private $passwordEncoder;
    // private $csrfTokenManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Déclaration des extensions de functions TWIG
    // (... utilisées essentiellement dans le Template/Profile/Driver...)
    public function getFunctions(){
        return [
            // fonctions des objets Claim et Claim_Status associées au Driver
            new TwigFunction('getclaims4driver', [$this, 'getClaims4Driver']),
            new TwigFunction('getstatus4claimanddriver', [$this, 'getStatus4ClaimAndDriver']),
            // fonctions des objets Claim et Claim_Status associées au Driver
            new TwigFunction('getclaims4customer', [$this, 'getClaims4Customer']),
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
    public function getClaims4Driver($driver, $bWithArchived){
        $arAllClaims=$this->entityManager->getRepository(ClaimStatus::class)->findBy(['driver'=>$driver]);
        $arResult=[];
        foreach($arAllClaims as $claimStatus){
            if($bWithArchived || !$claimStatus->getIsarchivedbydriver() || $claimStatus->getIsarchivedbydriver()==false || $claimStatus->getIsarchivedbydriver()==0){
                array_push($arResult, $claimStatus->getClaim());
            }
        }
        return $arResult;
    }
    public function getStatus4ClaimAndDriver($claim, $driver){
        return $this->entityManager->getRepository(ClaimStatus::class)->findOneBy(['claim'=>$claim,'driver'=>$driver]);
    }
    
    // fonctions des objets Claim et Claim_Status associées au Customer
    public function getClaims4Customer($customer, $bWithArchived){
        // commence par rechercher les ID de TOUTES les Claims créer par le Customer en demande
        $arAllClaims=$customer->getClaims();
        $arId=[];
        foreach($arAllClaims as $claimStatus){
            array_push($arId, $claimStatus->getId());
        }
        // selon la demande avec ou sans les "archives"...
        if($bWithArchived){
            $arClaimStatus=$this->entityManager->getRepository(ClaimStatus::class)->findBy(['claim'=>$arId]);
        }else{
            $arClaimStatus=array_merge(
                                    $this->entityManager->getRepository(ClaimStatus::class)
                                                        ->findBy(['claim'=>$arId, 'isarchivedbycustomer'=>null])
                                    ,
                                    $this->entityManager->getRepository(ClaimStatus::class)
                                                        ->findBy(['claim'=>$arId, 'isarchivedbycustomer'=>false])
            );
        }
        // 
        $arResult=[];
        foreach($arClaimStatus as $claimStatus){
            if(!in_array($claimStatus->getClaim(), $arResult)){
                array_push($arResult, $claimStatus->getClaim());
            }
        }
        return $arResult;
    }
    public function getStatus4ClaimOfCustomer($claim, $customer){
        // commence par rechercher les ID de TOUTES les Claims créer par le Customer en demande
        $arAllClaims=$customer->getClaims();
        if(in_array($claim, $arAllClaims->toArray())){
            return $this->entityManager->getRepository(ClaimStatus::class)->findBy(['claim'=>$claim ]);
        }else{
            return null;
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