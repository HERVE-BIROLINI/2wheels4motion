<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Claim;
use App\Entity\ClaimStatus;
use App\Entity\Flatrate;
use App\Entity\Paymentlabel;
use App\Entity\Tender;
use App\Entity\TenderStatus;
use App\Entity\Tva;
use App\Twig\FrenchGeographyTwig;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/tender", name="tender_")
 */
class TenderController extends AbstractController
{
    // private $mailer;

    // public function __construct(MailerInterface $mailer)
    // {
    //     $this->mailer = $mailer;
    // }

    /**
     * @Route("/createfor_claim{claim}", name="create", methods={"GET","POST"}, requirements={"claim":"\d+"})
     */
    public function create(Claim $claim)
    {
        //----------------------------------------------
        // ??? AJOUTE 1H ???
        // date_default_timezone_set('Europe/Paris');
        //----------------------------------------------
        // Pour les lectures et enregistrements dans la BdD
        $entityManager=$this->getDoctrine()->getManager();
        // si l'utilisateur N'est PAS connecté...
        if(!$user=$this->getUser()){
            // ... si vient du lien de réponse à une offre par courriel, transmet les paramètres...
            if(isset($_GET["goto"]) and $_GET["goto"] == "tender_create"){
                $this->addFlash('warning', "Pour créer un devis en réponse à cette demande, vous devez d'abord vous connecter.");
                return $this->redirectToRoute('security_login',[
                    'goto'  => $_GET["goto"],
                    'claim' => $claim->getId(),
                    'user'  => $_GET['user']
                ]);
            // ... si non, lui propose simplement de se connecter ou de créer un compte
            }else{
                $this->addFlash('warning', "Vous devez d'abord vous connecter. Si vous n'en avez pas encore, prenez 1 minute pour créer votre compte...");
                return $this->redirectToRoute('security_login');
            }
        }
        // si connecté, vérifie que son profil est bien Driver
        elseif(!$driver=$user->getDriver() or (isset($_GET['user']) && $user->getId() != intval($_GET['user'])))
        {
            // si arrive d'un courriel de Demande à un pilote, mais "mauvais" Login...
            if( (isset($_GET["goto"]) and $_GET["goto"] == "tender_create")
                ||
                (isset($_GET['comefrom']) && $_GET['comefrom'] == 'login')
                // (isset($_GET['comefrom']) && $_GET['comefrom']=='claim_email')
            ){
                $this->addFlash('danger', "Ce compte n'est pas celui destinaire de la demande correspondante au lien utilisé dans le courriel.");
            // ... si non, lui propose de se connecter
            }else{
                $this->addFlash('warning', "Si vous êtes détenteur d'une carte Pro. VMDTR et n'avez pas encore créer de compte 'pilote', prenez 5 minutes pour le faire...");
            }
            return $this->redirectToRoute('security_login');
        }
        // si Driver connecté, vérifie que la Claim lui était bien destinée
        elseif(!$driver->getClaims()->contains($claim))
        {
            // si arrive d'un courriel de Demande à un pilote, mais "mauvais" Login...
            
            if(isset($_GET['comefrom']) && $_GET['comefrom']=='login'){
            // if(isset($_GET['comefrom']) && $_GET['comefrom']=='claim_email'){

                $this->addFlash('danger', "Ce compte n'est pas celui destinaire de la demande correspondante au lien utilisé dans le courriel.");
            // ... si non, l'en informe avant de le renvoyer à SA page Profil Driver
            }else{
                $this->addFlash('warning', "Erreur de Demande de course...");
            }
            return $this->redirectToRoute('profile_driver');
        }
        // si le pilote a déjà répondu à cette demande par un devis...
        elseif($claimStatus=$entityManager->getRepository(ClaimStatus::class)->findOneBy(['claim'=>$claim->getId(), 'driver'=>$driver->getId()]) and $claimStatus->getTender())
        {
            $this->addFlash('danger', "Vous avez déjà répondu à cette demande...");
            return $this->redirectToRoute('profile_driver');
        }elseif((!isset($_POST) || count($_POST) < 1) && isset($_GET["goto"]) && $_GET["goto"] == "tender_create"){
            $this->addFlash('success', "Content de vous voir ".$user->getFirstname().". Vous souhaitant la concrétisation de votre devis à venir, par la réalisation de la course...");
        }
        
        // si invoqué par le Driver à partir du tableau de bord...
        if(isset($_GET['witharchived'])){
            $bWithArchived = $_GET['witharchived'];
            $default_item  = $_GET['default_item'];
        // ... si non, invoqué par le Driver à partir du courriel de Claim
        }else{
            $bWithArchived = null;
            $default_item  = null;
        }

        $obFGTwig = new FrenchGeographyTwig($entityManager);
        // ... la Company T3P
        $company=$driver->getCompany();
        //
        $errorEncountered = false;
        // $driver_comments        = false;
        // *** API Google Maps ***
        // récupération de l'adresse de Prise en charge...
        if($claim->getRemarkableplaceFrom() != null){
            $addressFrom=$claim->getRemarkableplaceFrom()->getLabel();
        }else{
            $addressFrom=$claim->getFromRoad().' '.$claim->getFromZip().' '.$claim->getFromCity();
        }
        // récupération de l'adresse de Destination...
        if($claim->getRemarkableplaceTo() != null){
            $addressTo =$claim->getRemarkableplaceTo()->getLabel();
        }else{
            $addressTo =$claim->getToRoad().' '.$claim->getToZip().' '.$claim->getToCity();
        }

        if(isset($_POST['cancel']) && $_POST['cancel']=='true'){
            // si invoqué par le Driver à partir du tableau de bord...
            if(isset($_GET['witharchived'])){
                $bWithArchived = $_GET['witharchived'];
                $default_item  = $_GET['default_item'];
            // ... si non, invoqué par le Driver à partir du courriel de Claim
            }else{
                $bWithArchived = null;
                $default_item  = null;
            }
            // puis, renvoi à la page du tableau de bord du Driver
            $this->addFlash('information', "Création du devis interrompue...");
            return $this->redirectToRoute('profile_driver',[
                //
                'witharchived'=> $bWithArchived,
                'default_item'=> $default_item,
            ]);
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
                ){$errorEncountered=true;}
                // Pickup
                if(!(isset($_POST['driver--pickupcost']) &&
                    ($driver_pickupcost=$_POST['driver--pickupcost'] or true) &&
                    $driver_pickupcost!='')
                ){$errorEncountered=true;}

            }else{
                $driver_racedistance = false;
                $driver_pickupcost   = false;
                $driver_priceperkm   = false;
                //
                $error_racedistance = false;
            }
            // ** Si Flatrate = 'Mise à disposition, Durée donnée' **
            /* CAS d'une case à cocher (abandonnée)
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
            $claimStatus->setIsread(true);
            $claimStatus->setTender($tender);
            //
            $entityManager->persist($claimStatus);
            
            // Enregistre une ligne d'état initial pour le statut du nouveau Tender
            $tendertatus=new TenderStatus();
            $tendertatus->setTender($tender);
            //
            $entityManager->persist($tendertatus);
            // "remplissage" de la BdD
            $entityManager->flush();
            
            // envoi du courriel au Customer à l'origine de la Claim
            return $this->redirectToRoute('mailer_tender', [
                'tender' => $tender->getId(),
                //
                'witharchived' => $bWithArchived,
                'default_item' => $default_item,
            ]);
        }
        //^-----------------------------------------------------------------------^

        // interrogation de l'API Google Maps pour Distance et Temps de la course
        //  (au dernier moment, SSI affichage du formulaire, par soucis d'économie...)
        if($addressFrom && $addressTo){
            // ... pour afficher la direction sur une carte Google Maps
            $help_directionurl='https://www.google.com/maps/dir/?api=1&origin='.str_replace(" ", "+", $addressFrom).'&destination='.str_replace(" ", "+", $addressTo).'&travelmode=driving';
            // ... pour re-rooter à partir de PHP
            // attention ! à remettre en place
            $mapsResult=$obFGTwig->mapsDistancematrix($addressFrom, $addressTo);
            // $mapsResult= null;
        }else{$mapsResult=null;}

        // recherche la liste des régions concernées pour filtrer les "forfaits" (Flatrate)
        $arFlatrateByRegions=[null,''];
        foreach ($obFGTwig->getRegions4Claim($claim) as $obRegion){
            if($obRegion){
                array_push($arFlatrateByRegions,$obRegion->code);
            }
        }
        // affichage de la page du formulaire de demande de course
        return $this->render('tender/create.html.twig', [
            'controller_name' => 'TenderController',
            'controller_func' => 'createTender',
            //
            'witharchived' => $bWithArchived,
            'default_item' => $default_item,
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
            'driver_pickupcost'  => $driver_pickupcost,
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
     * @Route("/{tender}", name="read", methods={"GET","POST"}, requirements={"tender":"\d+"})
     */
    public function read(Tender $tender)
    {
        //
        if(isset($_GET['default_item'])){
            $default_item = $_GET['default_item'];
        }else{$default_item  = null;}
        // si arrive d'une page du site
        if(isset($_GET['controller_func'])){
            $controller_func = $_GET['controller_func'];
        }else{$controller_func = 'tender_read';}
        // si arrive d'un lien courriel
        if(isset($_GET['witharchived'])){
            $bWithArchived = $_GET['witharchived'];
        }else{$bWithArchived = null;}

        // si l'utilisateur N'est PAS connecté...
        if(!$user=$this->getUser()){
            // ... si vient du lien de réponse à une offre par courriel, transmet les paramètres...
            if(isset($_GET["goto"]) and $_GET["goto"] == "tender_read"){
                $this->addFlash('warning', "Pour répondre à un devis, vous devez d'abord vous connecter.");
                return $this->redirectToRoute('security_login',[
                    'tender'       => $tender->getId(),
                    'goto'         => $_GET["goto"],
                    'user'         => $_GET['user'],
                    //
                    'controller_func' => $controller_func,
                    'default_item'    => $default_item,
                ]);
            // ... si non, lui propose simplement de se connecter ou de créer un compte
            }else{
                $this->addFlash('warning', "Vous devez d'abord vous connecter. Si vous n'en avez pas encore, prenez 1 minute pour créer votre compte...");
                return $this->redirectToRoute('security_login');
            }
        }

        // Vérification User et provenance, pour re-routage et/ou message
        $customer=$user->getCustomer();
        //
        if(isset($_GET['user']) && $_GET['user']!=intval($user->getId())){
            $this->addFlash('danger', "Le lien contenu dans le courriel ne correpond pas au compte connecté...");
            //
            if($user->getDriver()){
                return $this->redirectToRoute('profile_driver');
            }elseif($customer){
                return $this->redirectToRoute('profile_customer');
            }else{
                return $this->redirectToRoute('profile_user');
            }
        }
        elseif((!isset($_POST) || count($_POST) < 1) && isset($_GET["comefrom"]) && $_GET['comefrom']=='login'){
            if($customer && $customer->getClaims()->contains($tender->getClaim())==true){
                $this->addFlash('success', "Content de vous voir ".$user->getFirstname().". En espérant que ce devis réponde à vos attentes...");
            }
        }
        
        // Pour les lectures et enregistrements dans la BdD
        $entityManager=$this->getDoctrine()->getManager();

        // ----------------------------------------
        
        // si clic sur un des boutons d'actions dédiés au Customer (Accept / Reject)
        if(isset($_POST['customer_action'])){
            if($_POST['customer_action']=="accept"){
                return $this->redirectToRoute('tender_accept',[
                    "tender"   => $tender->getId(),
                    // "comefrom" => 'tender_read',
                    //
                    'controller_func' => $controller_func,
                    'witharchived' => $bWithArchived,
                    'default_item' => $default_item,
                ]);
            }elseif($_POST['customer_action']=="reject"){
                return $this->redirectToRoute('tender_reject',[
                    "tender"   => $tender->getId(),
                    // "comefrom" => 'tender_read',
                    //
                    'controller_func' => $controller_func,
                    'witharchived' => $bWithArchived,
                    'default_item' => $default_item,
                ]);
            }
        }

        // si clic sur un des boutons d'actions dédiés au Driver (Confirm)
        $error_payment=false;
        $payment_default=false;
        if(isset($_POST['driver_action'])){
            if($_POST['driver_action']=="confirm"){
                return $this->redirectToRoute('tender_confirm',[
                    "tender"   => $tender->getId(),
                    // "comefrom" => 'tender_read',
                    //
                    'witharchived' => $bWithArchived,
                    'default_item' => $default_item,
                ]);
            }
            if($_POST['driver_action']=="bild"){
                return $this->redirectToRoute('tender_bild',[
                    "booking"   => $tender->getBooking()->getId(),
                    // "tender"   => $tender->getId(),
                    // "comefrom" => 'tender_read',
                    //
                    'witharchived' => $bWithArchived,
                    'default_item' => $default_item,
                ]);
            }
            //
            if($_POST['driver_action']=="inform"){
                //
                if(isset($_POST['select--paymentlabel'])){
                    $paymentlabel=$entityManager->getRepository(Paymentlabel::class)->findOneBy(['id'=>$_POST['select--paymentlabel']]);
                    //
                    if($paymentlabel->getLabel()=='Autre...'){
                        $paymentlabel_label=$_POST['edit--paymentlabel'];
                    }else{
                        $paymentlabel_label=$paymentlabel->getLabel();
                    }
                    // quelque soit le choix de l'utilisateur, dès qu'il y a un Label, valide la Booking...
                    if($paymentlabel_label != ''){
                        return $this->redirectToRoute('tender_inform',[
                            "booking"            => $tender->getBooking()->getId(),
                            "paymentlabel"       => $paymentlabel->getId(),
                            "paymentlabel_label" => $paymentlabel_label,
                            //
                            'witharchived' => $bWithArchived,
                            'default_item' => $default_item,
                        ]);
                    // ... si l'utilisateur a choisi 'Autre..' sans préciser,
                    //  défini par défaut et reviens à la formulation de la facture
                    }else{
                        $error_payment=true;
                        $payment_default=$paymentlabel->getId();
                    }
                }else{
                    $error_payment=true;
                }
            }
        }

        // si édition de la facture
        if(isset($_GET['booking'])){
            $booking=$entityManager->getRepository(Booking::class)->findOneBy(['id'=>$_GET['booking']]);
        }else{$booking=$tender->getBooking();}
        //
        $paymentlabels=$entityManager->getRepository(Paymentlabel::class)->findAll();
        
        // affichage de la page du formulaire de demande de course
        return $this->render('tender/read.html.twig', [
            'controller_name' => 'TenderController',
            'controller_func' => $controller_func,
            //
            'witharchived' => $bWithArchived,
            'default_item' => $default_item,
            //
            'booking'  => $booking,
            'tender'   => $tender,
            'claim'    => $tender->getClaim(),
            'driver'   => $tender->getDriver(),
            'company'  => $tender->getDriver()->getCompany(),
            // 'user'     => $tender->getClaim()->getCustomer()->getUser(),
            'customer' => $tender->getClaim()->getCustomer(),
            //
            'paymentlabels'   => $paymentlabels,
            'error_payment'   => $error_payment,
            'payment_default' => $payment_default,
        ]);
    }
    //

    /**
     * @Route("{tender}/accept", name="accept", methods={"GET","POST"}, requirements={"tender":"\d+"})
     */
    public function accept(Tender $tender)
    {
        // ** Si toutes les conditions sont réunies, enregistre l'acceptation du Tender par le Customer
        //  * Enregistrements dans la BdD
        $entityManager=$this->getDoctrine()->getManager();
        //
        $tenderStatus=$entityManager->getRepository(TenderStatus::class)->findOneBy(['tender'=>$tender->getId()]);
        $tenderStatus->setIsacceptedbycustomer(1);
        //
        $entityManager->persist($tenderStatus);
        // "remplissage" de la BdD
        $entityManager->flush();

        if(isset($_GET['controller_func'])){
            $controller_func = $_GET['controller_func'];
        }else{$controller_func = null;}
        // si invoqué par le Driver à partir du tableau de bord...
        if(isset($_GET['witharchived'])){
            $bWithArchived = $_GET['witharchived'];
        }else{
            $bWithArchived = null;
        }
        if(isset($_GET['default_item'])){
            $default_item = $_GET['default_item'];
        }else{
            $default_item  = null;
        }
        
        // envoi du courriel au Driver à l'origine du Tender
        return $this->redirectToRoute('mailer_customer_response', [
            'tender' => $tender->getId(),
            //
            'tenderacceptation' => true,
            //
            'controller_func' => $controller_func,
            'witharchived' => $bWithArchived,
            'default_item' => $default_item,
        ]);
    }

    /**
     * @Route("{tender}/reject", name="reject", methods={"GET","POST"}, requirements={"tender":"\d+"})
     */
    public function reject(Tender $tender)
    {
        // ** Si toutes les conditions sont réunies, enregistre l'acceptation du Tender par le Customer
        //  * Enregistrements dans la BdD
        $entityManager=$this->getDoctrine()->getManager();
        //
        $tenderStatus=$entityManager->getRepository(TenderStatus::class)->findOneBy(['tender'=>$tender->getId()]);
        $tenderStatus->setIsacceptedbycustomer(-1);
        //
        $entityManager->persist($tenderStatus);
        // "remplissage" de la BdD
        $entityManager->flush();

        if(isset($_GET['controller_func'])){
            $controller_func = $_GET['controller_func'];
        }else{$controller_func = null;}
        // si invoqué par le Driver à partir du tableau de bord...
        if(isset($_GET['witharchived'])){
            $bWithArchived = $_GET['witharchived'];
        }else{
            $bWithArchived = null;
        }
        if(isset($_GET['default_item'])){
            $default_item = $_GET['default_item'];
        }else{
            $default_item  = null;
        }
        
        // envoi du courriel au Driver à l'origine du Tender
        return $this->redirectToRoute('mailer_customer_response', [
            'tender' => $tender->getId(),
            //
            'tenderacceptation' => -1,
            //
            'controller_func' => $controller_func,
            'witharchived' => $bWithArchived,
            'default_item' => $default_item,
        ]);
    }

    /**
     * @Route("{tender}/confirm", name="confirm", methods={"GET","POST"}, requirements={"tender":"\d+"})
     */
    public function confirm(Tender $tender)
    {
        // Pour les lectures et enregistrements dans la BdD
        $entityManager=$this->getDoctrine()->getManager();
        // lève le drapeau dans la table TenderStatus
        if($tenderStatus=$entityManager->getRepository(TenderStatus::class)->findOneBy(['tender'=>$tender])){
            $tenderStatus->setIsbookingconfirmedbydriver(true);
            //
            $entityManager->persist($tenderStatus);
        }
        // crée un nouvel enregistrement dans la table Booking
        $booking=new Booking;
        $booking->setTender($entityManager->getRepository(Tender::class)->findOneBy(['id'=>$tender]));
        //
        $entityManager->persist($booking);

        // "remplissage" de la BdD
        $entityManager->flush();

        if(isset($_GET['controller_func'])){
            $controller_func = $_GET['controller_func'];
        }else{$controller_func = null;}
        // si invoqué par le Driver à partir du tableau de bord...
        if(isset($_GET['default_item'])){
            $default_item = $_GET['default_item'];
        }else{$default_item  = null;}
        if(isset($_GET['witharchived'])){
            $bWithArchived = $_GET['witharchived'];
        }else{$bWithArchived = null;}
        
        // envoi du courriel au Customer à l'origine de la Claim,
        // pour laquelle il a accepté le Tender
        return $this->redirectToRoute('mailer_driver_confirm', [
            'tender' => $tender->getId(),
            //
            'controller_func' => $controller_func,
            'witharchived' => $bWithArchived,
            'default_item' => $default_item,
        ]);
    }

    /**
     * @Route("/bild{booking}", name="bild", methods={"GET","POST"}, requirements={"booking":"\d+"})
     */
    public function bild(Booking $booking)
    {
        // Pour les lectures et enregistrements dans la BdD
        $entityManager=$this->getDoctrine()->getManager();
        // lève le drapeau dans la table Booking
        $booking->setWasexecuted(true);
        //
        $entityManager->persist($booking);
        // "remplissage" de la BdD
        $entityManager->flush();

        if(isset($_GET['controller_func'])){
            $controller_func = $_GET['controller_func'];
        }else{$controller_func = null;}
        // si invoqué par le Driver à partir du tableau de bord...
        if(isset($_GET['witharchived'])){
            $bWithArchived = $_GET['witharchived'];
            $default_item  = $_GET['default_item'];
        // ... si non, invoqué par le Driver à partir du courriel de Claim
        }else{
            $bWithArchived = null;
            $default_item  = null;
        }

        return $this->redirectToRoute('tender_read', [
            'tender' => $booking->getTender()->getId(),
            'booking' => $booking->getId(),
            //
            'controller_func' => $controller_func,
            'witharchived' => $bWithArchived,
            'default_item' => $default_item,
        ]);
    }

    /**
     * @Route("/inform{booking}", name="inform", methods={"GET","POST"})
     */
    public function inform(Booking $booking){
        // Pour les lectures et enregistrements dans la BdD
        $entityManager=$this->getDoctrine()->getManager();
        // lève le drapeau dans la table Booking
        $booking->setPaidby($entityManager->getRepository(Paymentlabel::class)->findOneBy(['id'=>$_GET['paymentlabel']]));
        $booking->setPaidbyLabel($_GET['paymentlabel_label']);
        //
        $entityManager->persist($booking);
        // "remplissage" de la BdD
        $entityManager->flush();

        if(isset($_GET['controller_func'])){
            $controller_func = $_GET['controller_func'];
        }else{$controller_func = null;}
        // si invoqué par le Driver à partir du tableau de bord...
        if(isset($_GET['witharchived'])){
            $bWithArchived = $_GET['witharchived'];
            $default_item  = $_GET['default_item'];
        // ... si non, invoqué par le Driver à partir du courriel de Claim
        }else{
            $bWithArchived = null;
            $default_item  = null;
        }
        
        // envoi du courriel au Customer à l'origine de la Claim,
        // pour laquelle il a accepté le Tender
        return $this->redirectToRoute('mailer_driver_inform', [
            'tender' => $booking->getTender()->getId(),
            //
            'controller_func' => $controller_func,
            'witharchived' => $bWithArchived,
            'default_item' => $default_item,
        ]);
    }
}