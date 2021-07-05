<?php

namespace App\Controller;

use App\Entity\Claim;
use App\Entity\Flatrate;
use App\Form\ClaimFormType;
use App\Security\EmailVerifier;
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
     * @Route("/claim", name="index")
     */
    public function index(Request $request
                        // , MailerInterface $mailer
                        ): Response
    {
// echo("<pre>");
        // définition de l'état initial des variables de type booléen (drapeaux)
        $customer2Create=false;
$customer_road=false;
$customer_zip=false;
$customer_city=false;

        // * vérifie/crée un Customer pour l'utilisateur *
        // test si l'utilisateur est connecté...
        if(!$user=$this->getUser()){
            // ... si non, lui propose de se connecter
            return $this->redirectToRoute('app_login');
        }
        // si connecté, vérifie que son profil est complet (adresse => compte customer)
        elseif(!$customer=$this->getUser()->getCustomer()){
// var_dump('déjà connecté, MAIS pas de compte Customer...');
            $customer2Create=true;
        }
// else{var_dump('déjà connecté, profil complet... Nickel !');}


        
        //
        $claim=new Claim();
        $claimForm=$this->createForm(ClaimFormType::class, $claim);
        $claimForm->handleRequest($request);
        //
        date_default_timezone_set('Europe/Paris');
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
        // $error_time=null;
        if(!isset($time))
        {$claim->setArrivalatTime($now->add(new DateInterval('PT1H')));}
        //
        $fromZip=$claim->getFromZip();
        $toZip=$claim->getToZip();

        // ... RE-crée l'interface pour le cas des dates et heures par défaut ...
        $claimForm=$this->createForm(ClaimFormType::class, $claim);
        $claimForm->handleRequest($request);
        // ** Si le formulaire est correctement rempli... **
        if ($claimForm->isSubmitted() && $claimForm->isValid()){
// dd($_POST);
            // * Si tout est bien renseigner => enregistre, envoi email au chauffeurs, etc... *
            $diffDate=$now->diff($date);
            if(($diffDate->d==0 && $diffDate->m==0 and $diffDate->y==0)
                or $claim->getJourneyDate() > new DateTime('now')
            ){
                
                // Pour les enregistrements dans la BdD
                $entityManager = $this->getDoctrine()->getManager();

// var_dump('tout est bon ??');

$claim->setClaimDatetime(new DateTime('now'));
$claim->setCustomer($customer);

                    //

                    // "charge" la BdD
                    // $entityManager->persist($claim);
                    // $entityManager->flush();

// c'est ici !!!!
                // envoi des emails aux chauffeurs de la région du demandeur
                $this->sendClaimEmails($claim);
            }
            // ... problème de date et/ou heure (??)
            else{
                $error_date=true;
            }
            // return $this->redirectToRoute('mailer');
        }

        $entityManager = $this->getDoctrine()->getManager();
// echo("</pre>");
        return $this->render('claim/index.html.twig', [
            // 'controller_name' => 'ClaimController',
            'claimForm' => $claimForm->createView(),
            //
            'customer2Create'=>$customer2Create,
            // ... nécessaire pour l'adresse personnelle qui n'est pas prise en charge par le Formbuilder
            'customer_road'=> "road", //$customer_road,
            'customer_zip' => "00000",//$customer_zip,
            'customer_city'=> "city", //$customer_city,
            //
            'flatrates'  => $entityManager->getRepository(Flatrate::class)->findAll(),
            //
            'error_date' => $error_date,
            // 'error_time' => $error_time,
            // ... utiles à l'affichage dans un Input disabled,
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
        // dd($obClaim);
        // envoi des courriels de consultation à tous les chauffeurs de la région
        $claimEmail=new TemplatedEmail();
        //
        $context = $claimEmail->getContext();
        $context['firstname']="Hervé";//$obClaim->getFirstname();
        $context['lastname']="Birolini";//$obClaim->getLastname();
        $context['road']="4 avenue clément ader";//$obClaim->getFirstname();
        $context['zip']="78190";//$obClaim->getFirstname();
        $context['city']="TRAPPES";//$obClaim->getFirstname();
        $context['user_email']="Birolini.Herve@gmail.com";//$obClaim->getFirstname();
        $context['phone']="0671100299";//$obClaim->getFirstname();
        $context['claim_datetime']=$obClaim->getClaimDatetime()->format('d/m/Y');
        $context['journey_date']=$obClaim->getJourneyDate()->format('d/m/Y');
        $context['from_road']=$obClaim->getFromRoad();
        $context['from_zip']=$obClaim->getFromZip();
        $context['from_city']=$obClaim->getFromCity();
        $context['to_road']=$obClaim->getToRoad();
        $context['to_zip']=$obClaim->getToZip();
        $context['to_city']=$obClaim->getToCity();
        $context['arrivalat_time']=$obClaim->getArrivalatTime()->format('H:i:s');
        $context['comments']=$obClaim->getComments();
        //
        $claimEmail->context($context)
            ->from(new Address('twowheelsformotion@gmail.com', '2Wheels4Motion - Annuaire Moto-taxi'))
->to("birolini.herve@gmail.com")
            ->subject("Propositon d'une course Moto-taxi")
            // ->text('text') //ou htmlTemplate au choix !!
            ->htmlTemplate('claim/Claim2Drivers_email.html.twig')
        ;
        // $this->mailer->send($claimEmail);
// dd($obClaim);
    }


    /**
     * @Route("/claim/test", name="test")
     */
    public function test(){
        date_default_timezone_set('Europe/Paris');
        return $this->render('claim/Claim2Drivers_email.html.twig', [
            'lastname'=>"Birolini",
            'firstname'=>"Hervé",
            'road'=>"4 avenue clément ader",
            'zip'=>"78190",
            'city'=>"TRAPPES",
            'email'=>"Birolini.Herve@gmail.com",
            'phone'=>"06.71.10.02.99",
            'claim_datetime'=>new DateTime('now'),
            //
            'journey_date'=>new DateTime('+7 days'),
            'from_road'=>"7 avenue du Paradis",
            'from_zip'=>"75010",
            'from_city'=>"Paris",
            //
            'to_road'=>"666 impasse de l'enfer",
            'to_zip'=>"75020",
            'to_city'=>"Paris",
            'arrivalat_time'=>new DateTime('now'),
            //
            'comments'=>"Blabli blabla\nBagage cabine et petit chien..."
        ]);
    }
}