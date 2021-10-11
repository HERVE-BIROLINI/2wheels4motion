<?php

namespace App\Controller;

use App\Entity\Claim;
use App\Entity\Tender;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/mailer", name="mailer_")
 */
class MailerController extends AbstractController
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @Route("/", name="register")
     */
    public function confirmationregister(): Response
    {
        return $this->render('mailer/confirmationregister.html.twig', [
            'controller_name' => 'MailerController',
        ]);
    }

    /**
     * @Route("/claim{id}", name="claim")
     */
    public function sendClaimEmails(Claim $claim)
    {
        $customer=$claim->getCustomer();

        // ... parce que l'écriture/création prend plus de temps que l'exécution du code...
        // // $user=$customer->getUser();
        // IDEE POURRIE !!!
        //------------------
        // $user=null;
        // while(!$user){$user=$customer->getUser();}
        //------------------
        $user=$this->getUser();
        if(!$user){
            $user=$customer->getUser();
        }
        
        // Instanciation de l'objet permettant l'envoi du courriel
        $claimEmail=new TemplatedEmail();
        // Transmission des deonnées personnel de Customer
        $context = $claimEmail->getContext();
        //
        $context['firstname']      = $user->getFirstname();
        $context['lastname']       = $user->getLastname();
        $context['road']           = $customer->getRoad();
        $context['zip']            = $customer->getZip();
        $context['city']           = $customer->getCity();
        $context['user_email']     = $user->getEmail();
        $context['phone']          = $user->getPhone();
        $context['claim_datetime'] = $claim->getClaimDatetime()->format('d/m/Y');
        $context['journey_date']   = $claim->getJourneyDate()->format('d/m/Y');
        // Transmission des lieux de Prise en charge et Destination
        if($claim->getRemarkableplaceFrom()){
            $context['from_place']=$claim->getRemarkableplaceFrom();
        }else{
            $context['from_road']=$claim->getFromRoad();
            $context['from_zip']=$claim->getFromZip();
            $context['from_city']=$claim->getFromCity();
        }
        //
        if($claim->getRemarkableplaceTo()){
            $context['to_place']=$claim->getRemarkableplaceTo();
        }else{
            $context['to_road']=$claim->getToRoad();
            $context['to_zip']=$claim->getToZip();
            $context['to_city']=$claim->getToCity();
        }
        // Transmission de l'heure...
        if($claim->getPriorityDeparture()){
            $context['priority_departure']=true;
        }else{
            $context['priority_departure']=null;
        }
        // (... pour ne pas réfléchir lors de l'écriture du Mail)
        $context['journey_time']=$claim->getJourneyTime()->format('H:i:s');
        // Transmission du choix d'un forfait
        if($claim->getFlatrate()){
            $context['flatrate']=$claim->getFlatrate()->getLabel();
        }
        //
        $context['comments']=$claim->getComments();

        // Envoi d'eMail individuellement à tous les pilotes concernés par la région
        foreach($claim->getDrivers() as $obDriver){
            // ... génère l'adresse URL de réponse à une Claim par la création d'un TENDER
            $url = $this->generateUrl('tender_create',
                                        array('claim'  => $claim->getId(),
                                                'user' => $obDriver->getUser()->getId(),
                                                'goto' => 'tender_create',
                                            ),
                                        UrlGeneratorInterface::ABSOLUTE_URL
                                    )
            ;
            $context['url']=$url;
            //
            // $context['driver_firstname']=$obDriver->getUser()->getFirstname();
            //
            $claimEmail->context($context)
                ->from(new Address('twowheelsformotion@gmail.com', '2Wheels4Motion - Annuaire Moto-taxi'))
                ->to($obDriver->getUser()->getEmail())
                ->subject("Propositon d'une course Moto-taxi")
                // ->text('text') //ou htmlTemplate au choix !!
                ->htmlTemplate('mailer/Claim2Drivers_email.html.twig')
            ;
            $this->mailer->send($claimEmail);
        }
        
        // puis, renvoi à la page du tableau de bord du Customer
        $this->addFlash('success', "Votre demande a bien été transmise à ".$claim->getDrivers()->count()." pilote(s) référencé(s) dans la région de votre demande...");
        return $this->redirectToRoute('profile_customer');
    }
    
    /**
     * @Route("/tender{tender}", name="tender", methods={"GET","POST"}, requirements={"tender":"\d+"})
     */
    public function sendTenderEmail(Tender $tender)
    {
        // si invoqué par le Driver à partir du tableau de bord...
        if(isset($_GET['witharchived'])){
            $bWithArchived = $_GET['witharchived'];
        }else{
            $bWithArchived = null;
        }
        if(isset($_GET['default_item'])){
            $default_item  = $_GET['default_item'];
        }else{
            $default_item  = null;
        }

        // Instanciation de l'objet permettant l'envoi du courriel
        $tenderEmail=new TemplatedEmail();
        // Transmission des deonnées personnel de Customer
        $context = $tenderEmail->getContext();
        // ... génère l'adresse URL de réponse à un Tender
        $url = $this->generateUrl('tender_read',
                                    array('tender' => $tender->getId(),
                                            'user' => $tender->getClaim()->getCustomer()->getUser()->getId(),
                                            'goto' => 'tender_read',
                                            //
                                            'controller_func' => 'profile_customer',
                                            'default_item'    => "btn--tabtype--tender",
                                        ),
                                    UrlGeneratorInterface::ABSOLUTE_URL
        );
        $context['url']      = $url;
        $context['tender']   = $tender;
        $context['claim']    = $tender->getClaim();
        $context['driver']   = $tender->getDriver();
        $context['company']  = $tender->getDriver()->getCompany();
        $context['user']     = $tender->getClaim()->getCustomer()->getUser();
        $context['customer'] = $tender->getClaim()->getCustomer();
        //
        $tenderEmail->context($context)
            ->from(new Address('twowheelsformotion@gmail.com', '2Wheels4Motion - Annuaire Moto-taxi'))
            ->to($tender->getClaim()->getCustomer()->getUser()->getEmail())
            ->subject("Devis en réponse à votre demande de course Moto-taxi")
            // ->text('text') //ou htmlTemplate au choix !!
            ->htmlTemplate('mailer/Tender2Customer_email.html.twig')
        ;
        $this->mailer->send($tenderEmail);
        // puis, renvoi à la page du tableau de bord du Driver
        $this->addFlash('success', "Votre devis a bien été transmis au client...");
        return $this->redirectToRoute('profile_driver',[
            //
            'witharchived' => $bWithArchived,
            'default_item' => 'btn--tabtype--tender',
            // 'default_item' => $default_item,
        ]);
    }
    
    /**
     * @Route("/tender{tender}/customer_response", name="customer_response", methods={"GET","POST"}, requirements={"tender":"\d+"})
     */
    public function sendCutomerResponseEmail(Tender $tender)
    {
        // si invoqué par le Driver à partir du tableau de bord...
        if(isset($_GET['witharchived'])){
            $bWithArchived = $_GET['witharchived'];
        }else{
            $bWithArchived = null;
        }
        if(isset($_GET['default_item'])){
            $default_item  = $_GET['default_item'];
        }else{
            $default_item  = null;
        }

        // puis, renvoi à la page du tableau de bord du Customer
        if($_GET['tenderacceptation']=='1'){
            $subject="Félicitation ! Un de vos devis a été accepté...";
            $this->addFlash('information', "Votre acception de ce devis a été notifié au pilote.");
        }else{
            $subject="Vous avez reçu une réponse à l'un de vos devis...";
            $this->addFlash('information', "Votre refus de ce devis a été notifié au pilote.");
        }
        
        // Instanciation de l'objet permettant l'envoi du courriel
        $tenderEmail=new TemplatedEmail();
        // Transmission des deonnées personnel de Customer...
        $context = $tenderEmail->getContext();
        // ... génère l'adresse URL de réponse à un Tender
        $url = $this->generateUrl('tender_read',
                                    array('tender' => $tender->getId(),
                                            'user' => $tender->getDriver()->getUser()->getId(),
                                            'goto' => 'tender_read',
                                            //
                                            'controller_func' => 'profile_driver',
                                            'default_item'    => "btn--tabtype--booking",
                                        ),
                                    UrlGeneratorInterface::ABSOLUTE_URL
        );
        $context['url']               = $url;
        $context['tenderacceptation'] = $_GET['tenderacceptation'];
        $context['tender']            = $tender;
        //
        $tenderEmail->context($context)
            ->from(new Address('twowheelsformotion@gmail.com', '2Wheels4Motion - Annuaire Moto-taxi'))
            ->to($tender->getDriver()->getUser()->getEmail())
            ->subject($subject)
            // ->text("Contenu du courriel ".$siren." pour l'entreprise T3P, ")
            ->htmlTemplate('mailer/CustomerResponse2Driver_email.html.twig')
            // ->htmlTemplate('mailer/DriverConfirmation2Customer_email.html.twig')
        ;
        $this->mailer->send($tenderEmail);
        return $this->redirectToRoute('profile_customer',[
            //
            'witharchived'=> $bWithArchived,
            'default_item'=> $default_item,
            // 'default_item'=> $default_item,
        ]);
    }
    
    /**
     * @Route("/tender{tender}/driver_confirm", name="driver_confirm", methods={"GET","POST"}, requirements={"tender":"\d+"})
     */
    public function sendDriverConfirmationEmail(Tender $tender)
    {
        // si invoqué par le Driver à partir du tableau de bord...
        if(isset($_GET['witharchived'])){
            $bWithArchived = $_GET['witharchived'];
        }else{
            $bWithArchived = null;
        }
        if(isset($_GET['default_item'])){
            $default_item  = $_GET['default_item'];
        }else{
            $default_item  = null;
        }

        // Instanciation de l'objet permettant l'envoi du courriel
        $tenderEmail=new TemplatedEmail();
        // Transmission des deonnées personnel de Customer
        $context = $tenderEmail->getContext();
        $context['tender'] = $tender;
        $context['driver'] = $this->getUser()->getDriver();
        //
        $tenderEmail->context($context)
            ->from(new Address('twowheelsformotion@gmail.com', '2Wheels4Motion - Annuaire Moto-taxi'))
            ->to($tender->getClaim()->getCustomer()->getUser()->getEmail())
            ->subject("Confirmation de votre commande de course Moto-taxi")
            // ->text('text') //ou htmlTemplate au choix !!
            ->htmlTemplate('mailer/DriverConfirmation2Customer_email.html.twig')
        ;
        $this->mailer->send($tenderEmail);
        // puis, renvoi à la page du tableau de bord du Driver
        $this->addFlash('success', "Le client a bien été informé de votre confirmation de sa réservation...");
        return $this->redirectToRoute('profile_driver',[
            //
            'witharchived'=> $bWithArchived,
            'default_item'=> $default_item,
        ]);
    }
    
    /**
     * @Route("/tender{tender}/driver_inform", name="driver_inform", methods={"GET","POST"}, requirements={"tender":"\d+"})
     */
    public function sendDriverInformationEmail(Tender $tender)
    {
        // si invoqué par le Driver à partir du tableau de bord...
        if(isset($_GET['witharchived'])){
            $bWithArchived = $_GET['witharchived'];
        }else{
            $bWithArchived = null;
        }
        if(isset($_GET['default_item'])){
            $default_item  = $_GET['default_item'];
        }else{
            $default_item  = null;
        }
        
        // Instanciation de l'objet permettant l'envoi du courriel
        $tenderEmail=new TemplatedEmail();
        // Transmission des deonnées personnel de Customer
        $context = $tenderEmail->getContext();
        // ... génère l'adresse URL de réponse à un Tender
        $url = $this->generateUrl('tender_read',
                                    array('tender' => $tender->getId(),
                                            'user' => $tender->getClaim()->getCustomer()->getUser()->getId(),
                                            'goto' => 'tender_read',
                                            //
                                            'controller_func' => 'profile_customer',
                                            'default_item'    => "btn--tabtype--booking",
                                        ),
                                    UrlGeneratorInterface::ABSOLUTE_URL
        );
        $context['url']      = $url;
        $context['tender']   = $tender;
        $context['driver']   = $this->getUser()->getDriver();
        //
        $tenderEmail->context($context)
            ->from(new Address('twowheelsformotion@gmail.com', '2Wheels4Motion - Annuaire Moto-taxi'))
            ->to($tender->getClaim()->getCustomer()->getUser()->getEmail())
            ->subject("Finalisation de votre course Moto-taxi")
            // ->text('text') //ou htmlTemplate au choix !!
            ->htmlTemplate('mailer/DriverInformation2Customer_email.html.twig')
        ;
        $this->mailer->send($tenderEmail);
        // puis, renvoi à la page du tableau de bord du Driver
        $this->addFlash('success', "Le client a bien été informé de la disponibilité de sa facture...");
        return $this->redirectToRoute('profile_driver',[
            //
            'witharchived'=> $bWithArchived,
            'default_item'=> "btn--tabtype--booking",
            // 'default_item'=> $default_item,
        ]);
    }
    
    // /**
    //  * @Route("/email")
    //  */
    // public function sendEmail(MailerInterface $mailer): Response
    // {
    //     $message = (new Email())
    //     ->From('zooby@zobby.fr')
    //     ->to('birolini.herve@gmail.com')
    //     ->subject('sujet')
    //     ->text('text')
    //     ->html('<h1>Titre</h1>')
    //     // you can remove the following code if you don't define a text version for your emails
    //     ;
    //     $mailer->send($message);
    //     // ...
    //     return $this->render('mailer/confirmationregister.html.twig', [
    //         'controller_name' => 'MailerController',
    //     ]);
    // }
}
