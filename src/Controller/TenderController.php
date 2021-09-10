<?php

namespace App\Controller;

use App\Entity\Claim;
use App\Entity\ClaimStatus;
use App\Entity\Flatrate;
use App\Entity\Status;
use App\Entity\Tender;
use App\Entity\Tva;
use App\Twig\FlatrateTwig;
use App\Twig\FrenchGeographyTwig;
use DateInterval;
use DateTime;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/tender", name="tender_")
 */
class TenderController extends AbstractController
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @Route("/claim_{claim}", name="create", methods={"GET","POST"}, requirements={"claim":"\d+"})
     */
    public function create(Claim $claim)
    {
        // ??? AJOUTE 1H ???
        // date_default_timezone_set('Europe/Paris');
        
        // * vérifie/crée un Customer pour l'utilisateur *
        // test si l'utilisateur est connecté...
        if(!$user=$this->getUser()){
            // ... si non, lui propose de se connecter
            $this->addFlash('warning', "Vous devez d'abord vous connecter. Si vous n'en avez pas encore, prenez 1 minute pour créer votre compte...");
            return $this->redirectToRoute('security_login');
        }
        // ... si connecté, vérifie que son profil est bien Driver
        elseif(!$driver=$this->getUser()->getDriver()){
            // ... si non, lui propose de se connecter
            $this->addFlash('warning', "Si vous détenteur d'une carte Pro. VMDTR et n'avez pas encore créer de compte 'pilote', prenez 5 minutes pour le faire...");
            return $this->redirectToRoute('security_login');
        }
        // ... si Driver connecté, vérifie que la Claim lui était bien destinée
        elseif($driver->getClaims()->contains($claim)){
            // ... si non, l'en informe avant de le renvoyer à SA page Profil Driver
            $this->addFlash('warning', "Erreur de Demande de course...");
            return $this->redirectToRoute('profile_driver');
        }

        // Pour les lectures et enregistrements dans la BdD
        $entityManager=$this->getDoctrine()->getManager();
        $obFGTwig = new FrenchGeographyTwig($entityManager);
        $obFlatrateTwig = new FlatrateTwig($entityManager);
        // ... la Company T3P
        $company=$driver->getCompany();
        //
        $errorEncountered = false;
        // $driver_comments        = false;
        // *** API Google Maps ***
        // récupération de l'adresse de Prise en charge...
        if($claim->getRemarkableplaceFrom() != null){
            $addressFrom =$claim->getRemarkableplaceFrom()->getLabel();
        }else{
            $addressFrom =$claim->getFromRoad().' '.$claim->getFromZip().' '.$claim->getFromCity();
        }
        // récupération de l'adresse de Destination...
        if($claim->getRemarkableplaceTo() != null){
            $addressTo =$claim->getRemarkableplaceTo()->getLabel();
        }else{
            $addressTo =$claim->getToRoad().' '.$claim->getToZip().' '.$claim->getToCity();
        }

        //V-------------------------------------------------------------------V
        // *** Si retour du formulaire, analyse TOUTES les données POSTEES ***
        //V-------------------------------------------------------------------V
        if(isset($_POST['flatratechoosen'])){
            //  ** Le forfait sélection et les données "communes" **
            $driver_flatratechoosen=$entityManager->getRepository(Flatrate::class)->findOneBy(['id'=>$_POST['flatratechoosen']]);
            // ----------------------------------------------------
            // | Données communes à tous les choix de Flatrate... |
            // V--------------------------------------------------V
            // Le descriptif de la course
            // if(!(isset($_POST['driver--comments']) && ($driver_comments=$_POST['driver--comments'] or true) && $driver_comments!='')){
            //     $errorEncountered=true;
            // }
            // Le prix
            if(isset($_POST['driver--raceprice']) && ($driver_raceprice=$_POST['driver--raceprice'] or true) && $driver_raceprice!=''){
                $error_raceprice=false;
            }else{
                $error_raceprice=true;
                $errorEncountered=true;
            }
            // La tva
            if(!(isset($_POST['driver--racetva']) &&
                ($driver_racetva=$entityManager->getRepository(Tva::class)->findOneBy(['id'=>$_POST['driver--racetva']]) or true)
            )){
                $errorEncountered=true;
            }
            // --------------------------------------------------------
            // | Analyse selon les attentes du Forfait sélectionné... |
            // V------------------------------------------------------V
            // ** Si Flatrate 'Au kilomètre' **
            //  (=> vérification Distance, Prix/km, Prise en charge)
            if(isset($driver_flatratechoosen) && $driver_flatratechoosen != null &&
                stristr($driver_flatratechoosen->getLabel(), 'par km')
            ){
                // $flatrate_type='driver';
                // Distance
                if(isset($_POST['driver--racedistance']) &&
                    ($driver_racedistance=$_POST['driver--racedistance'] or true) &&
                    $driver_racedistance!=''
                ){
                    $error_racedistance=false;
                }else{
                    $driver_racedistance=false;
                    $error_racedistance=true;
                    $errorEncountered=true;
                }
                // Prix/Km
                if(!(isset($_POST['driver--priceperkm']) &&
                    ($driver_priceperkm=$_POST['driver--priceperkm'] or true) &&
                    $driver_priceperkm!='')
                ){
                    $errorEncountered=true;
                }
                // Pickup
                if(!(isset($_POST['driver--pickupcost']) &&
                    ($driver_pickupcost=$_POST['driver--pickupcost'] or true) &&
                    $driver_pickupcost!='')
                ){
                    $errorEncountered=true;
                }

            }else{
                $driver_racedistance        = false;
                $driver_pickupcost          = false;
                $driver_priceperkm   = false;
                //
                $error_racedistance = false;
            }
            // ** Si Flatrate = 'Mise à disposition, Durée donnée' **
            /*
                //  (=> vérification des Horaires... plus loin)
                if(isset($driver_flatratechoosen) && $driver_flatratechoosen != null &&
                    stristr($driver_flatratechoosen->getLabel(), 'disposition')
                ){
                    // $flatrate_type='disposition';

                }
                // ** Si Flatrate = 'Mise à disposition, Durée donnée' **
                //  (=> vérification des Horaires... plus loin)
                if(isset($driver_flatratechoosen) && $driver_flatratechoosen != null &&
                    $driver_flatratechoosen->getPickupIncluded()
                ){
                    // $flatrate_type='flatrate';

                }
            */
            // -----------------------
            //  ** Les horaires... **
            // -----------------------
            if((//-- Horaire de départ
                    ($claim->getPriorityDeparture() &&
                        $claim->getDepartureatTime() &&
                        ($driver_departureattime=$claim->getDepartureatTime() or true)
                    )
                    ||
                    ($claim->getPriorityDeparture() &&
                        $claim->getJourneyTime() &&
                        ($driver_departureattime=$claim->getJourneyTime() or true)
                    )
                    ||
                    ($claim->getPriorityDeparture() == null &&
                        isset($_POST['driver--departureattime']) &&
                        ($driver_departureattime=$_POST['driver--departureattime'] or true)
                    )
                )
                && $driver_departureattime!=''
            ){
                $error_departureattime=false;
            }else{
                $driver_departureattime=false;
                $error_departureattime=true;
                $errorEncountered=true;
            }
            //
            if(//-- Horaire d'arrivée
                isset($_POST['driver--arrivalattime']) &&
                // ... horaire d'arrivée => STRING
                ($driver_arrivalattime=$_POST['driver--arrivalattime'] or true) &&
                // strtotime($driver_arrivalattime) &&
                // ... heures de différence avec départ
                // ($diff=(strtotime($driver_arrivalattime)-strtotime($driver_departureattime->format('H:i')))/3600 or true) &&
                // ... soit déjà = au forfait, soit doit-être recalculée
                // (
                    // (// si forfait "Dispo", le nombre d'heure apparait dans le label, sinon => 0
                    //     ($dispo=intval($obFlatrateTwig->getHoursInLabel($driver_flatratechoosen)) or true) &&
                    //     $dispo>0 &&
                        // $diff == $dispo &&
                        // $driver_arrivalattime=new DateTime($driver_arrivalattime)
                        // &&(var_dump($driver_arrivalattime) or true ) &&
                        // (var_dump("--------------------------------") or true) 
                    // )
                    // ||
                    // (// si pas forfait "Dispo"
                    //     (var_dump("--------------------------------") or true) &&
                    //     (var_dump($driver_arrivalattime) or true ) &&
                    //     (var_dump("--------------------------------") or true) &&
                    //     // (($driver_arrivalattime=$driver_departureattime->format('H:i') or true) &&
                    //         $driver_arrivalattime=new DateTime($driver_arrivalattime) 
                    //     // )
                    //     // Vilaine astuce pour contourner une erreur bizarre : 
                    //     //      "Call to a member function add() on string"
                    //     &&
                    //     $driver_arrivalattime->add(new DateInterval('PT'.$dispo.'H'))
                    // )

                // ) &&
                $driver_arrivalattime!=''
            ){
                $error_arrivalattime=false;
            }else{
                if($claim->getPriorityDeparture()){
                    $driver_arrivalattime=false;
                    $error_arrivalattime=true;
                    $errorEncountered=true;
                }else{
                    $driver_arrivalattime=$claim->getArrivalatTime();
                    $error_arrivalattime=false;
                }
            }
            // les commentaires du Driver
            if(!isset($_POST['driver--comments']) ||
                (($driver_comments=$_POST['driver--comments'] or true) && $driver_comments=='')
            ){
                $driver_comments = false;
            }
        }else{// /!\ Initialisation à false à l'entrée du Controller (1er passage) /!\
            // ... les saisies Driver
            $driver_departureattime = false;
            $driver_arrivalattime   = false;
            $driver_flatratechoosen = false;
            $driver_racetva         = false;
            $driver_raceprice       = false;
            $driver_comments        = false;
            $driver_racedistance    = false;
            $driver_pickupcost      = false;
            $driver_priceperkm      = false;
            // ... les erreurs ()
            $error_raceprice        = false;
            $error_departureattime  = false;
            $error_arrivalattime    = false;
            $error_racedistance     = false;
        }

        
        //V-----------------------------------------------------------------------V
        //|*** Si formulaire correctement rempli, envoie le Tender au Customer ***|
        //V-----------------------------------------------------------------------V
        if(isset($_POST['flatratechoosen']) && $errorEncountered==false){
            // $tender=$entityManager
            //     ->getRepository(Tender::class)
            //     ->findAll()[0];
            $tender=new Tender();
            $now=new DateTime('now');
            // Associe la Demande d'origine (Claim)
            $tender->setClaim($claim);
            // Associe le Pilote (Driver)
            $tender->setDriver($driver);
            // Enregistre un n° unique pour le devis (Tender)
            $tender->setNumber($now->format('Ym') . '/' . $company->getSiren() . '#' . count($driver->getTenders()));
            // Attribut l'heure actuelle pour création
            $tender->setTenderDatetime($now);
            // l'heure de rendez-vous (Prise en charge)
            if(gettype($driver_departureattime)=='string'){
                $tender->setRdvatTime(new DateTime($driver_departureattime));
            }else{
                $tender->setRdvatTime($driver_departureattime);
            }
            // l'heure d'arrivée (Destination)
            if(gettype($driver_arrivalattime)=='string'){
                $tender->setArrivalatDatetime(new DateTime($driver_arrivalattime));
            }else{
                $tender->setArrivalatDatetime($driver_arrivalattime);
            }
            // Associe le forfait (Flatrate)
            $tender->setFlatrate($driver_flatratechoosen);
            // les commentaires (du Driver)
            $tender->setComments($driver_comments);
            // la distance
            if($driver_racedistance==null){
                $tender->setDistance(0);
            }else{
                $tender->setDistance($driver_racedistance);
            }
            // le prix au kilomètre
            if($driver_priceperkm==null){
                $tender->setPriceperkm(0);
            }else{
                $tender->setPriceperkm($driver_priceperkm);
            }
            // le coût de la prise en charge
            if($driver_pickupcost==null){
                $tender->setPickupcost(0);
            }else{
                $tender->setPickupcost($driver_pickupcost);
            }
            // Associe la tva (Tva)
            $tender->setTva($driver_racetva);
            // le prix de la course proposé
            $tender->setPrice($driver_raceprice);
            //
            $entityManager->persist($tender);

            // Changement d'état pour la demande (Claim) dans le process
            // de traitement fait par CE pilote (Driver)...
            $claimStatus=$entityManager->getRepository(ClaimStatus::class)->findOneBy(['claim'=>$claim,'driver'=>$driver,]);

            // ABANDONNE
            $claimStatus->setIsread(true);
            //---------------
            // $claimStatus->setStatus($entityManager->getRepository(Status::class)->findOneBy(['label'=>'Réponse envoyée']));
            //---------------

            $claimStatus->setTender($tender);
            //
            $entityManager->persist($claimStatus);
            
            // "remplissage" de la BdD
            $entityManager->flush();
            
            // envoi des emails aux chauffeurs de la région du demandeur
            return $this->redirectToRoute('mailer_tender', ['id' => $tender->getId()]);
        }
        //^-----------------------------------------------------------------------^

        
        // interrogation de l'API Google Maps pour Distance et Temps de la course
        //  (au dernier moment, SSI affichage du formulaire, par soucis d'économie...)
        if($addressFrom && $addressTo){
            // ... pour afficher la direction sur une carte Google Maps
            $help_directionurl='https://www.google.com/maps/dir/?api=1&origin='.str_replace(" ", "+", $addressFrom).'&destination='.str_replace(" ", "+", $addressTo).'&travelmode=driving';
            // ... pour re-rooter à partir de PHP
            // return $this->redirect($driver_raceurl);
            $mapsResult= $obFGTwig->mapsDistancematrix($addressFrom, $addressTo);
        }else{$mapsResult=null;}

        // recherche la liste des régions concernées pour filtrer les "forfaits" (Flatrate)
        $arFlatrateByRegions=[null,''];
        foreach ($obFGTwig->getRegions4Claim($claim) as $obRegion){
            array_push($arFlatrateByRegions,$obRegion->code);
        }
        // affichage de la page du formulaire de demande de course
        return $this->render('tender/create.html.twig', [
            'controller_name'   => 'TenderController',
            // 'controller_func'   => 'createTender',
            //
            'claim'     => $claim,
            //
            'user'      => $user,
            'driver'    => $driver,
            'company'   => $company,
            //
            'flatrates' => $entityManager
                                ->getRepository(Flatrate::class)
                                ->findBy(array('region_code'=>$arFlatrateByRegions),
                                        array('region_code'=>'asc','price'=>'asc','label'=>'asc')
                                ),
            //
            'mapsResult'        => $mapsResult,
            'help_directionurl' => $help_directionurl,
            //
            'driver_departureattime'    => $driver_departureattime,
            'driver_arrivalattime'      => $driver_arrivalattime,
            'driver_racetva'            => $driver_racetva,
            'driver_raceprice'          => $driver_raceprice,
            'driver_flatratechoosen'    => $driver_flatratechoosen,
            'driver_comments'           => $driver_comments,
            'driver_racedistance'       => $driver_racedistance,
            'pickupcost'                => $entityManager
                                                ->getRepository(Flatrate::class)
                                                ->findOneBy(['label'=>'prise en charge (hors forfait)']),
            'driver_pickupcost'         => $driver_pickupcost,
            'driver_priceperkm'  => $driver_priceperkm,
            // 'driver_racetime'           => $driver_racetime,
            //
            'error_raceprice'       => $error_raceprice,
            'error_departureattime' => $error_departureattime,
            'error_arrivalattime'   => $error_arrivalattime,
            'error_racedistance'    => $error_racedistance,
        ]);
    }

    /**
     * @Route("/{id}", name="read", methods={"GET","POST"}, requirements={"id":"\d+"})
     */
    public function read(Tender $obTender)
    {

        // affichage de la page du formulaire de demande de course
        return $this->render('mailer/Tender2Customer_email.html.twig', [
            'tender'  =>$obTender,
            'claim'   =>$obTender->getClaim(),
            'driver'  =>$obTender->getDriver(),
            'company' =>$obTender->getDriver()->getCompany(),
            'user'    =>$obTender->getClaim()->getCustomer()->getUser(),
            'customer'=>$obTender->getClaim()->getCustomer(),
        ]);
    }
}