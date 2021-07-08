<?php

namespace App\Controller;

use App\Entity\Claim;
use App\Entity\Customer;
use App\Entity\Flatrate;
use App\Entity\User;
use App\Form\ClaimFormType;
use App\Security\EmailVerifier;
use App\Twig\DriverTwig;
use App\Twig\FrenchGeographyTwig;
use DateInterval;
use DateTime;
use DateTimeZone;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/", name="claim_")
 */
class ClaimController extends AbstractController
{
    // private $emailVerifier;
    private $mailer;

    public function __construct(MailerInterface $mailer
                                // , EmailVerifier $emailVerifier
                                )
    {
        // $this->emailVerifier = $emailVerifier;
        $this->mailer = $mailer;
    }

    /**
     * @Route("/claim", name="create")
     */
    public function create(Request $request
                            // , MailerInterface $mailer
                            ): Response
    {
        date_default_timezone_set('Europe/Paris');
        // définition de l'état initial des variables de type booléen (drapeaux)
        $bFormIsFullyValid=false;
        $customer2Create=false;
        //
        if(isset($_POST["customer_road"])){
            $customer_road=($_POST["customer_road"]);
        }
        else{$customer_road=false;}
        if(isset($_POST["customer_city"])){
            $customer_city=($_POST["customer_city"]);
        }
        else{$customer_city=false;}
        if(isset($_POST["customer_zip"])){
            $customer_zip=($_POST["customer_zip"]);
        }
        else{$customer_zip=false;}
        $error_customer_road=false;
        $error_customer_city=false;
        // $error_customer_zip=false;

        // * vérifie/crée un Customer pour l'utilisateur *
        // test si l'utilisateur est connecté...
        if(!$user=$this->getUser()){
            // ... si non, lui propose de se connecter
            return $this->redirectToRoute('app_login');
        }
        // si connecté, vérifie que son profil est complet (adresse => compte customer)
        elseif(!$customer=$this->getUser()->getCustomer()){
            $customer2Create=true;
        }
        else{
            $customer_road=$customer->getRoad();
            $customer_city=$customer->getCity();
            $customer_zip=$customer->getZip();
        }

        //
        $claim=new Claim();
        $claimForm=$this->createForm(ClaimFormType::class, $claim);
        $claimForm->handleRequest($request);
        //
        $now=new DateTime('now');
        $date=$claim->getJourneyDate();
        $time=$claim->getArrivalatTime();
        // si une date est défini,
        // ... lève le drapeau d'erreur pour le cas de retour au formulaire
        $error_date=null;
        if(!isset($date))
        {$claim->setJourneyDate($now);}
        // si une heure est défini,
        // ... lève le drapeau d'erreur pour le cas de retour au formulaire
        $error_time=null;
        if(!isset($time))
        {$claim->setArrivalatTime($now->add(new DateInterval('PT2H')));}
        //
        $fromZip=$claim->getFromZip();
        $toZip=$claim->getToZip();
        
        // ... RE-crée l'interface pour le cas des dates et heures par défaut ...
        $claimForm=$this->createForm(ClaimFormType::class, $claim);
        $claimForm->handleRequest($request);

        //
        $entityManager = $this->getDoctrine()->getManager();
        // ** Si le formulaire est correctement rempli... **
        if ($claimForm->isSubmitted() && $claimForm->isValid()
                && ( (isset($customer_road) and $customer_road!='')
                    ||
                    (isset($_POST['customer_road']) and $_POST['customer_road']!='')
                    )
                && ( (isset($customer_city) and $customer_city!='')
                    ||
                    (isset($_POST['customer_city']) and $_POST['customer_city']!='')
                    )
                // && $_POST['customer_zip']!=''
            )
        {
            // * Si tout est bien renseigner => enregistre, envoi email au chauffeurs, etc... *
            $diffDate=$now->diff($date);
            if((($diffDate->d==0 && $diffDate->m==0 and $diffDate->y==0)
                    or $claim->getJourneyDate() > $now
                )
                // ... si délai < à 2h
                and strtotime($claim->getArrivalatTime()->format('H:i'))
                        -
                    strtotime($now->format('H:i'))
                    >= 7200
            ){
                // Pour les enregistrements dans la BdD
                $entityManager = $this->getDoctrine()->getManager();

                //
                if($customer2Create){
                    $customer= new Customer();
                }
                $customer->setRoad($customer_road);
                $customer->setCity($customer_city);
                $customer->setZip($customer_zip);
                //
                $claim->setClaimDatetime(new DateTime('now'));
                //
                $entityManager->persist($customer);
                $entityManager->flush();
                //
                $claim->setCustomer($customer);
                $entityManager->persist($claim);
                //
                $user->setCustomer($customer);
                $entityManager->persist($customer);

                $entityManager->flush();
                // c'est ici !!!!
                // envoi des emails aux chauffeurs de la région du demandeur
                var_dump('<br>... ENVOI DU COURRIEL (reste à gérer les destinataires) ??');
                $this->sendClaimEmails($claim);
                dd('courriels envoyés !!');
            }
            // ... problème d'heure (??)
            elseif(strtotime($claim->getArrivalatTime()->format('H:i'))
                        -
                    strtotime($now->format('H:i'))
                    < 7200 // 7200 = 2h en sec.
                ){
                $error_time=true;
            }
            // ... problème de date(??)
            else{$error_date=true;}
        }
        // ... problème avec l'adresse du Customer
        else{
            if(isset($_POST['customer_road']) and $_POST['customer_road']==''){$error_customer_road=true;}
            if(isset($_POST['customer_city']) and $_POST['customer_city']==''){$error_customer_city=true;}
            // if($_POST['customer_zip']==''){$error_customer_zip=true;}
        }

        return $this->render('claim/create.html.twig', [
            // 'controller_name' => 'ClaimController',
            'claimForm' => $claimForm->createView(),
            //
            'customer2Create'=>$customer2Create,
            // ... nécessaire pour l'adresse personnelle qui n'est pas prise en charge par le Formbuilder
            'customer_road'=> $customer_road,
            'customer_city'=> $customer_city,
            'customer_zip' => $customer_zip,
            'error_customer_road'=> $error_customer_road,
            'error_customer_city'=> $error_customer_city,
            // 'error_customer_zip' => $error_customer_zip,
            //
            'flatrates'  => $entityManager->getRepository(Flatrate::class)->findAll(),
            //
            'error_date' => $error_date,
            'error_time' => $error_time,
            // ... utiles au RE-affichage dans un Input disabled,
            // car si utilisation de l'élément du Formbuilder,
            // ne pourrait-être désactivé sans perdre le Submit...
            'fromZip'   => $fromZip,
            'toZip'     => $toZip,
        ]);
    }

    public function sendClaimEmails(Claim $obClaim
                                    //, MailerInterface $mailer
                                    )
    {
        // recherche la liste des chauffeurs concernés...
        // ... pour les régions de prise la "prise en charge" et de la "destination"
        $obFGTwig = new FrenchGeographyTwig;
        $obDriverTwig = new DriverTwig($this->getDoctrine()->getManager());
        $arRegions=[];
        $arDrivers=[];
        foreach([$obClaim->getFromZip(), $obClaim->getToZip()] as $zip){
            $obRegion=$obFGTwig->getRegionByZip($zip);
            //
            if (!in_array($obRegion,$arRegions)) {
                array_push($arRegions,$obRegion);
            }
        }
        // ... en localisant les entreprises T3P
        foreach($arRegions as $obRegion){
            $arDrivers=array_merge($arDrivers,$obDriverTwig->getDriversByRegionOrZip($obRegion));
        }
        //
        $obCustomer=$obClaim->getCustomer();
        $obUser=$obCustomer->getUser();

        // envoi des courriels de consultation à tous les chauffeurs de la région
        $claimEmail=new TemplatedEmail();
        $entityManager = $this->getDoctrine()->getManager();
        //
        $context = $claimEmail->getContext();
        $context['firstname']=$obUser->getFirstname();
        $context['lastname']=$obUser->getLastname();
        $context['road']=$obCustomer->getRoad();
        $context['zip']=$obCustomer->getZip();
        $context['city']=$obCustomer->getCity();
        $context['user_email']=$obUser->getEmail();
        $context['phone']=$obUser->getPhone();
        $context['claim_datetime']=$obClaim->getClaimDatetime()->format('d/m/Y');
        $context['journey_date']=$obClaim->getJourneyDate()->format('d/m/Y');
        //
        if(isset($_POST["flatratechoosen"])){
            $context['flatrate']= $entityManager->getRepository(Flatrate::class)->findOneBy(['id'=>$_POST["flatratechoosen"]]);
        }
        $context['from_road']=$obClaim->getFromRoad();
        $context['from_zip']=$obClaim->getFromZip();
        $context['from_city']=$obClaim->getFromCity();
        $context['to_road']=$obClaim->getToRoad();
        $context['to_zip']=$obClaim->getToZip();
        $context['to_city']=$obClaim->getToCity();
        $context['arrivalat_time']=$obClaim->getArrivalatTime()->format('H:i:s');
        $context['comments']=$obClaim->getComments();

        // Envoi d'eMail individuellement à tous les pilotes concernés par la région
        foreach($arDrivers as $obDriver){
            $context['driver_firstname']=$obDriver->getUser()->getFirstname();
            //
            $claimEmail->context($context)
                ->from(new Address('twowheelsformotion@gmail.com', '2Wheels4Motion - Annuaire Moto-taxi'))
                ->to($obDriver->getUser()->getEmail())
                ->subject("Propositon d'une course Moto-taxi")
                // ->text('text') //ou htmlTemplate au choix !!
                ->htmlTemplate('claim/Claim2Drivers_email.html.twig')
            ;
            $this->mailer->send($claimEmail);
        }
    }


    /**
     * @Route("/claim/test", name="test")
     */
    /// Test pour affichage du template du mail, dans le navigateur...
    public function test(){
        $now=new DateTime('now');
        $now_7d=new DateTime('+7 days');
        date_default_timezone_set('Europe/Paris');
        return $this->render('claim/Claim2Drivers_email.html.twig', [
            'driver_firstname'=>"Mr PILOTE",
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
}