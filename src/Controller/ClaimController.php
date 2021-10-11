<?php

namespace App\Controller;

use App\Entity\Claim;
use App\Entity\ClaimStatus;
use App\Entity\Customer;
use App\Entity\Flatrate;
use App\Entity\Remarkableplace;
use App\Form\ClaimFormType;
use App\Twig\DriverTwig;
use App\Twig\FrenchGeographyTwig;
use DateInterval;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/claim", name="claim_")
 */
class ClaimController extends AbstractController
{

    /**
     * @Route("/", name="create")
     */
    public function create(Request $request): Response
    {
        date_default_timezone_set('Europe/Paris');
        // définition de l'état initial des variables de type booléen (drapeaux)
        $customer2Create=false;
        // * vérifie/crée un Customer pour l'utilisateur *
        // test si l'utilisateur est connecté...
        if(!$user=$this->getUser()){
            // ... si non, lui propose de se connecter
            $this->addFlash('warning', "Vous devez être connecté pour formuler une Commande de course. Si vous n'en avez pas encore, prenez 1 minute pour créer votre compte...");
            return $this->redirectToRoute('security_login',['goto'=>'claim_create']);
        }
        // ... si connecté, vérifie que son profil est complet (adresse => compte customer)
        elseif(!$customer=$this->getUser()->getCustomer()){
            $customer2Create=true;
        }
        // ... si non, affichera les éléments de formulaire qui s'imposent
        else{
            $customer_road=$customer->getRoad();
            $customer_city=$customer->getCity();
            $customer_zip=$customer->getZip();
        }

        //
        if((!isset($_POST) || count($_POST) < 1) && isset($_GET['comefrom']) && $_GET['comefrom']=='login'){
        // if(isset($_GET['comefrom']) && $_GET['comefrom']=='claim_create'){
            $this->addFlash('success', "Content de vous voir ".$user->getFirstname().". Quel trajet auriez-vous besoin d'effectuer ?");
            // $_GET['comefrom']=null; // NE PERMET PAS D'EVITER LE DOUBLON DU MESSAGE
        }

        // Pour les lectures et enregistrements dans la BdD
        $entityManager=$this->getDoctrine()->getManager();

        // Dans le cas du besoin de créer un nouveau compte Customer
        if(isset($_POST["customer_road"])){
            $customer_road=($_POST["customer_road"]);
        }else{$customer_road=false;}
        //
        if(isset($_POST["customer_city"])){
            $customer_city=($_POST["customer_city"]);
        }else{$customer_city=false;}
        //
        if(isset($_POST["customer_zip"])){
            $customer_zip=($_POST["customer_zip"]);
        }else{$customer_zip=false;}
        //
        $error_customer_road=false;
        $error_customer_city=false;

        // Instancie un nouvel objet pour la demande, et son formulaire
        $claim=new Claim();
        $claimForm=$this->createForm(ClaimFormType::class, $claim);
        $claimForm->handleRequest($request);

        // Défini le moment "présent"
        $now=new DateTime('now');
        $date=$claim->getJourneyDate();
        $time=$claim->getJourneyTime();
        
        // si une date est définie
        // $error_date=null;
        if(!isset($date))
        {$claim->setJourneyDate($now);}
        // si une heure est définie
        $error_time=null;
        if(!isset($time)){
            $claim->setJourneyTime($now->add(new DateInterval('PT2H')));
        }
        // si un Flatrate a été choisi (cas de 2nd passage)
        if(isset($_POST['flatrate'])){
            $claim->setFlatrate($entityManager->getRepository(Flatrate::class)->findOneBy(['id'=>$_POST['flatrate']]));
        }

        //
        // $error_priority=null;
        $fromZip=$claim->getFromZip();
        $toZip=$claim->getToZip();
        $remarkableplace_from=null;
        $remarkableplace_to=null;
        //
        $error_tozip=null;
        $error_fromzip=null;
        
        // ... (RE-)crée l'interface pour le cas des dates et heures par défaut ...
        $claimForm=$this->createForm(ClaimFormType::class, $claim);
        $claimForm->handleRequest($request);

        // ** Si le formulaire est correctement rempli... **
        if($claimForm->isSubmitted() && $claimForm->isValid()
                // * Test sur tous les critères n'appartenant pas au formulaire *
                // * ou étant facultatifs (les uns autorisant NULL aux autres)  *
                // ... sur l'adresse Customer
                && (!$customer2Create
                    || (isset($customer_road) and $customer_road!='')
                    || (isset($_POST['customer_road']) and $_POST['customer_road']!='')
                    )
                && (!$customer2Create
                    || (isset($customer_city) and $customer_city!='')
                    || (isset($_POST['customer_city']) and $_POST['customer_city']!='')
                    )
                    // ... sur le lieu de prise en charge
                && ((isset($_POST['remarkableplace--from'])
                    && ($remarkableplace_from=$_POST['remarkableplace--from'] or true)
                    && $remarkableplace_from!=''
                    )
                    || $claim->getFromZip()
                    )
                    // ... sur le lieu de prise en charge
                && ((isset($_POST['remarkableplace--to'])
                        && ($remarkableplace_to=$_POST['remarkableplace--to'] or true)
                        && $remarkableplace_to!=''
                    )
                    || $claim->getToZip()
                    )
            )
        {
            // * Si tout est bien renseigner => enregistre, envoi email au chauffeurs, etc... *
            $diffDate=$now->diff($date);
            if((($diffDate->d==0 && $diffDate->m==0 and $diffDate->y==0)
                    or $claim->getJourneyDate() > $now
                )
                // ... si délai < à 2h
                and
                (
                    strtotime($claim->getJourneyTime()->format('H:i'))
                        -
                    strtotime($now->format('H:i'))
                    >= 7200
                    or
                    $claim->getJourneyDate() > $now
                )
            ){
                // Se "débarrase" maintenant de l'inscription du Customer,
                // quelque soit la disponibilité de Driver(s)...
                if($customer2Create){
                    $customer= new Customer();
                    $customer->setRoad($customer_road);
                    $customer->setCity($customer_city);
                    $customer->setZip($customer_zip);
                    $entityManager->persist($customer);
                    //
                    $user->setCustomer($customer);
                    $entityManager->persist($customer);
                    // "remplissage" de la BdD
                    $entityManager->flush();
                }

                // Recherche les régions concernées par la deamnade (Claim)...
                $obFGTwig = new FrenchGeographyTwig($entityManager);
                $arZip=[];
                // ... une adresse particulière a été choisi comme lieu de Prise en charge
                if($remarkableplace_from){
                    $oRemarkable=$entityManager->getRepository(Remarkableplace::class)->findOneBy(['id'=>$remarkableplace_from]);
                    $arZip[]=$oRemarkable->getDeptCode()."000";
                    $claim->setRemarkableplaceFrom($oRemarkable);
                }elseif($claim->getFromZip()){
                    $arZip[]=$claim->getFromZip();
                }
                // ... une adresse particulière a été choisi comme lieu de Destination
                if($remarkableplace_to){
                    $oRemarkable=$entityManager->getRepository(Remarkableplace::class)->findOneBy(['id'=>$remarkableplace_to]);
                    $arZip[]=$oRemarkable->getDeptCode()."000";
                    $claim->setRemarkableplaceTo($oRemarkable);
                }elseif($claim->getToZip()){
                    $arZip[]=$claim->getToZip();
                }
                // crée la liste des régions concernées par les Zip
                // celle(s) concernant l'(es) adresse(s) particulière(s) choisie(s)
                $arRegions=[];
                foreach($arZip as $zip){
                    $obRegion=$obFGTwig->getRegionByZip($zip);
                    //
                    if (!in_array($obRegion,$arRegions)) {
                        array_push($arRegions,$obRegion);
                    }
                }

                // Puis, recherche la liste des chauffeurs concernés...
                // ... pour les régions de la "prise en charge" et de la "destination"
                $obDriverTwig = new DriverTwig($this->getDoctrine()->getManager());                // identifie les pilotes et entreprises T3P concernés
                $arDrivers=[];
                foreach($arRegions as $obRegion){
                    $arDrivers4Region=$obDriverTwig->getDriversByRegionOrZip($obRegion);
                    // exclu les pilotes non-vérifiés, ou associés à une T3P non-reconnue
                    foreach($arDrivers4Region as $obDriver){
                        if($obDriver->getUser()!==$user
                            and $obDriver->getIsVerified()
                            and $obDriver->getCompany()->getIsconfirmed()
                        ){
                            array_push($arDrivers,$obDriver);
                        }
                    }
                }
                
                // Si au mmoins un  pilote référencé dans la région de la demande,
                // enregistre la Claim, après avoir récupérer TOUTES les Entrées,
                // n'appartenant pas à la Form, et envoie les mails...
                if(count($arDrivers)>0){
                    // Enregistre l'horaire le plus "important"
                    if($claim->getPriorityDeparture()){
                        $claim->setDepartureatTime($claim->getJourneyTime());
                    }else{
                        $claim->setArrivalatTime($claim->getJourneyTime());
                    }
                    // Récupère le Flatrate (forfait Mise à disposition), si demandé
                    if(isset($_POST['flatrate'])){
                        $claim->setFlatrate($entityManager->getRepository(Flatrate::class)->findOneBy(['id'=>$_POST['flatrate']]));
                    }
                    // Attribut l'heure actuelle pour création de la Claim
                    $claim->setClaimDatetime(new DateTime('now'));
                    // Associe le Customer à la Claim
                    $claim->setCustomer($customer);
                    //
                    $entityManager->persist($claim);

                    // -- /!\ Plus utilisé /!\ --
                    // --------------------------
                    // Associe la Demande et tous les pilotes à qui elle sera envoyée
                    // $obStatus=$entityManager->getRepository(Status::class)->findOneBy(['value'=>0]);
                    // --------------------------
                    
                    // mais aussi, initialise Status pour le suivi par chaque destinataire
                    foreach($arDrivers as $obDriver){
                        $claim->addDriver($obDriver);
                        //
                        $claimStatus=new ClaimStatus();
                        $claimStatus->setClaim($claim);
                        $claimStatus->setDriver($obDriver);
                        //
                        $entityManager->persist($claimStatus);
                    }
                    
                    // "remplissage" de la BdD
                    $entityManager->flush();
                    
                    // envoi des emails aux chauffeurs de la région du demandeur
                    return $this->redirectToRoute('mailer_claim', ['id' => $claim->getId()]);
                }
                //... si aucun pilote référencé... en informe le client
                else{$this->addFlash('danger', "Actuellement aucun pilote et/ou entreprise T3P n'opérant dans la région de votre demande n'est encore référencé dans notre communauté. Malheureusement votre demande ne peut être satisfaite...");}
            }
            // ... problème d'heure (??)
            elseif(strtotime($claim->getJourneyTime()->format('H:i'))
                        -
                    strtotime($now->format('H:i'))
                    < 7200 // 7200 = 2h en sec.
                ){
                $error_time=true;
            }
            // ... problème de date(??)
            else{
                var_dump('<br><br>---   PROBLEME DE DATE (???) ---<br>');
                // $error_date=true;
            }
        }
        // ** si formulaire soumis, mais problème... **
        elseif($claimForm->isSubmitted()){
            // Problème(s) avec l'adresse Customer
            if(isset($_POST['customer_road']) and $_POST['customer_road']==''){$error_customer_road=true;}
            if(isset($_POST['customer_city']) and $_POST['customer_city']==''){$error_customer_city=true;}
            // Problème(s) avec l'adresse de prise en charge
            if((!isset($_POST['remarkableplace--from'])
                ||  (isset($_POST['remarkableplace--from'])
                        && ($remarkableplace_from=$_POST['remarkableplace--from'] or true)
                        && $remarkableplace_from==''
                    )
                )
                && !$claim->getFromZip()
            ){
                $error_fromzip=true;
            }
            // if((!isset($remarkableplace_from) || $remarkableplace_from=='')
            //     && !$claim->getFromZip()
            // ){
            //     $error_fromzip=true;
            // }
            // Problème(s) avec l'adresse destination
            if((!isset($_POST['remarkableplace--to'])
                ||  (isset($_POST['remarkableplace--to'])
                        && ($remarkableplace_to=$_POST['remarkableplace--to'] or true)
                        && $remarkableplace_to==''
                    )
                )
                && !$claim->getToZip()
            ){
                $error_tozip=true;
            }
        }
        
        // affichage de la page du formulaire de demande de course
        return $this->render('claim/create.html.twig', [
            'controller_name'   => 'ClaimController',
            'claimForm'         => $claimForm->createView(),
            'claim'             => $claim,
            'flatrates'         => $entityManager->getRepository(Flatrate::class)->findAll(),
            //
            'customer2Create'=>$customer2Create,
            // Utiles au RE-affichage dans un Input disabled,
            // car si utilisation de l'élément du Formbuilder,
            // ne pourrait-être désactivé sans perdre le Submit...
            // ... nécessaire pour l'adresse personnelle qui n'est pas prise en charge par le Formbuilder

            'customer_road'         => $customer_road,
            'customer_city'         => $customer_city,
            'customer_zip'          => $customer_zip,
            'error_customer_road'   => $error_customer_road,
            'error_customer_city'   => $error_customer_city,
            // 'error_customer_zip' => $error_customer_zip,
            //
            // 'error_date'    => $error_date,
            'error_time'    => $error_time,
            // 'error_priority'=> $error_priority,
            //
            'remarkableplace_from'  => $remarkableplace_from,
            'remarkableplace_to'    => $remarkableplace_to,
            //
            'fromZip'       => $fromZip,
            'toZip'         => $toZip,
            'error_fromzip' => $error_fromzip,
            'error_tozip'   => $error_tozip,
        ]);
    }

/*
    / **
     * @Route("/test", name="test")
     * /
    /// Test pour affichage du template du mail, dans le navigateur...
    public function test(){
        $now=new DateTime('now');
        $now_7d=new DateTime('+7 days');
        date_default_timezone_set('Europe/Paris');
        return $this->render('claim/Claim2Drivers_email.html.twig', [
            // 'driver_firstname'=>"Mr PILOTE",
            'lastname'=>"Birolini",
            'firstname'=>"Hervé",
            'user_email'=>"Birolini.Herve@gmail.com",
            'road'=>"4 avenue clément ader",
            'zip'=>"78190",
            'city'=>"TRAPPES",
            'email'=>"Birolini.Herve@gmail.com",
            'phone'=>"06.71.10.02.99",
            'claim_datetime'=>$now->format('d/m/Y'),
            //
            'journey_date'=>$now_7d->format('d/m/Y'),
            'from_road'=>"7 avenue du Paradis",
            'from_zip'=>"75010",
            'from_city'=>"Paris",
            //
            'to_road'=>"666 impasse de l'enfer",
            'to_zip'=>"75020",
            'to_city'=>"Paris",
            'arrivalat_time'=>$now->format('H:i:s'),
            //
            'comments'=>"Blabli blabla\nBagage cabine et petit chien..."
        ]);
    }
*/
}