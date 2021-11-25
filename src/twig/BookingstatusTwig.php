<?php

namespace App\Twig;

use App\Entity\Booking;
use App\Entity\Customer;
use App\Entity\Driver;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Doctrine\ORM\EntityManagerInterface;

class BookingstatusTwig extends AbstractExtension
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
            // fonctions des objets Booking associées au Driver
            new TwigFunction('getbookings4driver', [$this, 'getBookings4Driver']),
            // new TwigFunction('gettenders4driver', [$this, 'getTenders4Driver']),
            // fonctions des objets Booking associées au Customer
            new TwigFunction('getbookingsofcustomer', [$this, 'getBookingsOfCustomer']),
            // new TwigFunction('gettenderstatus4customer', [$this, 'getTenderStatus4Customer']),
            // new TwigFunction('getstatus4tenderofcustomer', [$this, 'getStatus4TenderOfCustomer']),
            // fonctions des objets Booking "communes" à tous les rôles
            // new TwigFunction('getstatus4tender', [$this, 'getStatus4Tender']),
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
    // fonctions des objets Booking associées au Driver

    public function getBookings4Driver(Driver $driver, $bWithArchived){
        $arAllTenders=$driver->getTenders();
        //
        $arId=[];
        foreach($arAllTenders as $tender){
            array_push($arId, $tender->getId());
        }
        //
        if($bWithArchived){
            $arBooking=$this->entityManager->getRepository(Booking::class)->findBy(['tender'=>$arId]);
        }else{
            $arBooking=array_merge(
                                    $this->entityManager->getRepository(Booking::class)
                                                        ->findBy(['tender'=>$arId, 'isarchivedbydriver'=>null])
                                    ,
                                    $this->entityManager->getRepository(Booking::class)
                                                        ->findBy(['tender'=>$arId, 'isarchivedbydriver'=>false])
            );
        }
        // 
        $arResult=[];
        foreach($arBooking as $booking){
            if(!in_array($booking->getTender(), $arResult)){
                array_push($arResult, $booking->getTender());
            }
        }
        return $arResult;
    }

    public function getBookingsOfCustomer(Customer $customer, $bWithArchived){
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
            $arBooking=$this->entityManager->getRepository(Booking::class)->findBy(['tender'=>$arId]);
        }else{
            $arBooking=array_merge(
                                    $this->entityManager->getRepository(Booking::class)
                                                        ->findBy(['tender'=>$arId, 'isarchivedbycustomer'=>null])
                                    ,
                                    $this->entityManager->getRepository(Booking::class)
                                                        ->findBy(['tender'=>$arId, 'isarchivedbycustomer'=>false])
            );
        }
        // 
        $arResult=[];
        foreach($arBooking as $booking){
            if(!in_array($booking->getTender(), $arResult)){
                array_push($arResult, $booking->getTender());
            }
        }
        return $arResult;
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