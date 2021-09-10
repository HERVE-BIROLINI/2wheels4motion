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
    public function sendClaimEmails(Claim $obClaim)
    {
        $obCustomer=$obClaim->getCustomer();

        // ... parce que l'écriture/création prend plus de temps que l'exécution du code...
        // // $obUser=$obCustomer->getUser();
        // IDEE POURRIE !!!
        //------------------
        // $obUser=null;
        // while(!$obUser){$obUser=$obCustomer->getUser();}
        //------------------
        $obUser=$this->getUser();
        if(!$obUser){
            $obUser=$obCustomer->getUser();
        }
        
        // Instanciation de l'objet permettant l'envoi du courriel
        $claimEmail=new TemplatedEmail();
        // Transmission des deonnées personnel de Customer
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
        // Transmission des lieux de Prise en charge et Destination
        if($obClaim->getRemarkableplaceFrom()){
            $context['from_place']=$obClaim->getRemarkableplaceFrom();
        }
        else{
            $context['from_road']=$obClaim->getFromRoad();
            $context['from_zip']=$obClaim->getFromZip();
            $context['from_city']=$obClaim->getFromCity();
        }
        if($obClaim->getRemarkableplaceTo()){
            $context['to_place']=$obClaim->getRemarkableplaceTo();
        }
        else{
            $context['to_road']=$obClaim->getToRoad();
            $context['to_zip']=$obClaim->getToZip();
            $context['to_city']=$obClaim->getToCity();
        }
        // Transmission de l'heure...
        if($obClaim->getPriorityDeparture()){
            $context['priority_departure']=true;
        }
        else{
            $context['priority_departure']=null;
        }
        // (... pour ne pas réfléchir lors de l'écriture du Mail)
        $context['journey_time']=$obClaim->getJourneyTime()->format('H:i:s');
        // Transmission du choix d'un forfait
        if($obClaim->getFlatrate()){
            $context['flatrate']=$obClaim->getFlatrate()->getLabel();
        }

        //
        $context['comments']=$obClaim->getComments();

        // Envoi d'eMail individuellement à tous les pilotes concernés par la région
        foreach($obClaim->getDrivers() as $obDriver){
            $context['driver_firstname']=$obDriver->getUser()->getFirstname();
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
        $this->addFlash('success', "Votre demande a bien été transmise à ".$obClaim->getDrivers()->count()." pilote(s) référencé(s) dans la région de votre demande...");
        return $this->redirectToRoute('profile_customer');
    }
    
    /**
     * @Route("/tender{id}", name="tender")
     */
    public function sendTenderEmails(Tender $obTender)
    {
        
        // Instanciation de l'objet permettant l'envoi du courriel
        $tenderEmail=new TemplatedEmail();
        // Transmission des deonnées personnel de Customer
        $context = $tenderEmail->getContext();
        $context['tender']  = $obTender;
        $context['claim']   = $obTender->getClaim();
        $context['driver']  = $obTender->getDriver();
        $context['company'] = $obTender->getDriver()->getCompany();
        $context['user']    = $obTender->getClaim()->getCustomer()->getUser();
        $context['customer']= $obTender->getClaim()->getCustomer();
        
        //
        $tenderEmail->context($context)
            ->from(new Address('twowheelsformotion@gmail.com', '2Wheels4Motion - Annuaire Moto-taxi'))
            ->to($obTender->getClaim()->getCustomer()->getUser()->getEmail())
            ->subject("Devis en réponse à votre demande de course Moto-taxi")
            // ->text('text') //ou htmlTemplate au choix !!
            ->htmlTemplate('mailer/Tender2Customer_email.html.twig')
        ;
        $this->mailer->send($tenderEmail);

        // puis, renvoi à la page du tableau de bord du Driver
        $this->addFlash('success', "Votre devis a bien été transmis au client...");
        return $this->redirectToRoute('profile_driver');
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
