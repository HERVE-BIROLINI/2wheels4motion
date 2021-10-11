<?php

namespace App\Twig;

use App\Entity\Customer;
use App\Entity\Driver;
use App\Entity\Tender;
use App\Entity\TenderStatus;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Doctrine\ORM\EntityManagerInterface;

class TenderstatusTwig extends AbstractExtension
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
            // fonctions des objets Tender et Tender_Status associées au Driver
            new TwigFunction('gettenderstatusofdriver', [$this, 'getTenderStatusOfDriver']),
            // new TwigFunction('gettenders4driver', [$this, 'getTenders4Driver']),
            // fonctions des objets Tender et Tender_Status associées au Customer
            new TwigFunction('gettenderstatus4customer', [$this, 'getTenderStatus4Customer']),
            // new TwigFunction('getstatus4tenderofcustomer', [$this, 'getStatus4TenderOfCustomer']),
            // fonctions des objets Tender et Tender_Status "communes" à tous les rôles
            new TwigFunction('getstatus4tender', [$this, 'getStatus4Tender']),
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
    // fonctions des objets Tender et Tender_Status associées au Driver

    public function getTenderStatusOfDriver(Driver $driver, $bWithArchived){
        $arAllTenders=$driver->getTenders();
        //
        $arId=[];
        foreach($arAllTenders as $tender){
            array_push($arId, $tender->getId());
        }
        // selon la demande avec ou sans les "archives"...
        if($bWithArchived){
            $arTenderStatus=$this->entityManager->getRepository(TenderStatus::class)->findBy(['tender'=>$arId]);
        }else{
            $arTenderStatus=array_merge(
                                    $this->entityManager->getRepository(TenderStatus::class)
                                                        ->findBy(['tender'=>$arId, 'isarchivedbydriver'=>null])
                                    ,
                                    $this->entityManager->getRepository(TenderStatus::class)
                                                        ->findBy(['tender'=>$arId, 'isarchivedbydriver'=>false])
            );
        }
        // 
        $arResult=[];
        foreach($arTenderStatus as $tenderStatus){
            if(!in_array($tenderStatus->getTender(), $arResult)){
                array_push($arResult, $tenderStatus->getTender());
            }
        }
        return $arResult;
    }

    
    // fonctions des objets Tender et Tender_Status associées au Customer
    public function getTenderStatus4Customer($customer, $bWithArchived){
        // commence par rechercher les ID de TOUS les Tenders en réponses aux Claims créées par le Customer
        $arAllClaims=$customer->getClaims();
        $arId=[];
        foreach($arAllClaims as $claim){
            foreach($claim->getTenders() as $tender){
                array_push($arId, $tender->getId());
            }
        }
        // selon la demande avec ou sans les "archives"...
        if($bWithArchived){
            $arTenderStatus=$this->entityManager->getRepository(TenderStatus::class)->findBy(['tender'=>$arId]);
        }else{
            $arTenderStatus=array_merge(
                                    $this->entityManager->getRepository(TenderStatus::class)
                                                        ->findBy(['tender'=>$arId, 'isarchivedbycustomer'=>null])
                                    ,
                                    $this->entityManager->getRepository(TenderStatus::class)
                                                        ->findBy(['tender'=>$arId, 'isarchivedbycustomer'=>false])
            );
        }
        // 
        $arResult=[];
        foreach($arTenderStatus as $tenderStatus){
            if(!in_array($tenderStatus->getTender(), $arResult)){
                array_push($arResult, $tenderStatus->getTender());
            }
        }
        return $arResult;
    }

/*
    public function getStatus4TenderOfCustomer($tender, $customer){
        // commence par rechercher les ID de TOUTES les Tenders créer par le Customer en demande
        $arAllTenders=$customer->getTenders();
        if(in_array($tender, $arAllTenders->toArray())){
            return $this->entityManager->getRepository(TenderStatus::class)->findBy(['tender'=>$tender ]);
        }else{
            return null;
        }
    }
*/

    // fonctions des objets Tender et Tender_Status associées au Cutomer

    public function getStatus4Tender(Tender $tender){
        return $this->entityManager->getRepository(TenderStatus::class)->findOneBy(['tender'=>$tender]);
    }

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