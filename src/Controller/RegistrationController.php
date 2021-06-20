<?php

namespace App\Controller;

use DateTime;
use App\Entity\Company;
use App\Entity\Driver;
use App\Entity\Picture;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Twig\FrenchGeographyTwig;
use App\Security\EmailVerifier;
// use App\Security\LoginFormAuthenticator;
use App\Repository\DriverRepository;
use App\Repository\CompanyRepository;
use App\Repository\PicturelabelRepository;
use App\Tools\UploadPictureTools;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
// use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
// use Symfony\Component\Validator\Constraints\Date;
// use Symfony\Component\Validator\Validator\ValidatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

/**
 * @Route("/register", name="registration_")
 */
class RegistrationController extends AbstractController
{
    private $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier
                                )
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * @Route("/", name="user", methods={"GET","POST"})
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder
                            // , GuardAuthenticatorHandler $guardHandler
                            // , LoginFormAuthenticator $authenticator
    ): Response
    {
        // test si l'utilisateur N'est PAS DEJA identifié...
        if($this->getUser()){
            // ... si oui, lui propose de se reconnecter
            // (qui renverra à l'accueil...)
            return $this->redirectToRoute('app_login');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Défini les propriétés de l'objet "nouvel utilisateur"
            // (pour les propriétés non disponibles dans le formaulaire)
            // (encode the plain password)
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            //
            $user->setRegistrationDate(new DateTime('NOW'));
            // $user->setHasagreetoterms(0);

            // Effectue les enregistrements dans la BdD
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('twowheelsformotion@gmail.com', 'Annuaire Moto-taxi'))
                    ->to($user->getEmail())
                    ->subject('Merci de confirmer votre adresse électronique.')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            // do anything else you need here, like send an email
            // affiche la page d'information de l'envoi de l'email de confirmation
            // return $guardHandler->authenticateUserAndHandleSuccess(
            //     $user,
            //     $request,
            //     $authenticator,
            //     'main' // firewall name in security.yaml
            // );
            return $this->redirectToRoute('mailer');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user{id}/driver", name="driver", methods={"GET","POST"})
     */
    public function registerDriver(User $obUser
                                , DriverRepository $drivers
                                , CompanyRepository $companies
                                , PicturelabelRepository $picturelabel
                                , MailerInterface $mailer
    ): Response
    {

        // test si l'utilisateur N'est PAS encore identifié
        if(!$this->getUser() or $this->getUser()!==$obUser){
            return $this->redirectToRoute('app_login');
        }
        // test si l'utilisateur aurait déjà fait une demande
        // dd($this->getUser());
        if($this->getUser()->getDriver()){
            return $this->redirectToRoute('app_login');
        }

        $obFrenchGeographyTwig=new FrenchGeographyTwig;
        $error_files=null;
        $driver_exist=null;
        $bError=null;
        // Définition des variables, nécessaire si 1er passage...
        $vmdtr_number=null;
        $vmdtr_validity=null;
        $motomodel=null;
        $name=null;
        $siren=null;
        $nic=null;
        $road=null;
        $zip=null;
        $city=null;
        $hasconfirmedgoodstanding=null;
        // ... mais si 2nd passage, récupère les saisies de l'interface
        if(isset($_POST['vmdtr_number'])){
            $vmdtr_number=$_POST['vmdtr_number'];
        }
        if(isset($_POST['vmdtr_validity'])){
            $vmdtr_validity=$_POST['vmdtr_validity'];
        }
        if(isset($_POST['motomodel'])){
            $motomodel=$_POST['motomodel'];
        }
        if(isset($_POST['name'])){
            $name=$_POST['name'];
        }
        if(isset($_POST['siren'])){
            $siren=$_POST['siren'];
        }
        if(isset($_POST['nic'])){
            $nic=$_POST['nic'];
        }
        if(isset($_POST['road'])){
            $road=$_POST['road'];
        }
        if(isset($_POST['zip'])){
            $zip=$_POST['zip'];
            //
            $city=$obFrenchGeographyTwig->getCityByZip($zip)->name;
        }
        if(isset($_POST['hasconfirmedgoodstanding'])){
            $hasconfirmedgoodstanding=$_POST['hasconfirmedgoodstanding'];
        }

        //  File => un fichier choisi et upload réussi
        $obUploadPicture = new UploadPictureTools;
        $obPicturelabel_VMDTR=$picturelabel->findOneBy(['label'=>'Carte pro. VMDTR - Face']);
        if(isset($_FILES['file']) and $_FILES['file']['error']==0){
            // ----------v- A analyser lors de la publication -v----------
            $bError=$obUploadPicture->UploadPicture($obUser, $obPicturelabel_VMDTR
                , $this->getParameter('asset_path_dev')
            );
            // ----------^- A analyser lors de la publication -^----------
        }

        // Cas de RETOUR dans le formulaire...
        if($hasconfirmedgoodstanding
            and (is_null($bError) or !isset($bError) or $bError==false) and !is_null($obUploadPicture)
            and $vmdtr_number!==null and !$driver_exist=$drivers->findBy(['vmdtr_number'=>$vmdtr_number])
        ){
            // Pour les enregistrements dans la BdD
            $entityManager = $this->getDoctrine()->getManager();

            // si tout est bon, il faudra :
            // - créer la Company (sauf si choix d'une existante)
            // - créer le Driver
            // - ajouter la Company au Driver
            // - créer la Picture
            // - ajouter le User à la Picture
            // (- ajouter le [rôle Driver] à User)
            // (- ajouter le Driver à User)
            //
            // ... crée la Company
            if(!$obCompany=$companies->findOneBy(['siren'=>$siren])){
                $obCompany=new Company;
                $obCompany->setName($name);
                $obCompany->setSiren($siren);
                if($nic != ''){
                    $obCompany->setNic($nic);
                }
                $obCompany->setRoad($road);
                $obCompany->setZip($zip);
                $obCompany->setCity($city);
                //
                $entityManager->persist($obCompany);
            }
            // ... company existante => choisie dans la liste des companies connues
            else{
                // dd("Choix d'une compagnie existante, une erreur ?");
            }
            // ... crée le Driver
            $obDriver=new Driver;
            $obDriver->setVmdtrNumber($vmdtr_number);
            $obDriver->setVmdtrValidity(new \DateTime($vmdtr_validity));
            $obDriver->setMotomodel($motomodel);
            $obDriver->setHasconfirmedgoodstanding($hasconfirmedgoodstanding);
            $obDriver->setCompany($obCompany);
            $entityManager->persist($obDriver);
            // ... crée la Picture
            $obPicture=new Picture;
            if(isset($picture_PathName)){
                $obPicture->setPathname($picture_PathName);
            }
            elseif(!is_null($obUploadPicture)){
                $obPicture->setPathname($obUploadPicture->getPathName());
            }
            $obPicture->setPicturelabel($obPicturelabel_VMDTR);
            $obPicture->setUser($obUser);
            $entityManager->persist($obPicture);
            // ... associe le nouveau Driver à l' User
            $obUser->setDriver($obDriver);
            $entityManager->persist($obUser);
            // "charge" la BdD
            $entityManager->flush();
            // envoi d'un courriel de confirmation de référencement...
            // ... du Driver et de sa Company
            $email=(new TemplatedEmail());
            //
            $context = $email->getContext();
            $context['firstname']=$obUser->getFirstname();
            $context['vmdtrnumber']=$obDriver->getVmdtrNumber();
            $context['vmdtrvalidity']=$obDriver->getVmdtrValidity()->format('d/m/Y');
            $context['motomodel']=$obDriver->getMotomodel();
            $context['name']=$obCompany->getName();
            $context['siren']=$obCompany->getSiren();
            $context['nic']=$obCompany->getNic();
            $context['road']=$obCompany->getRoad();
            $context['zip']=$obCompany->getZip();
            $context['city']=$obCompany->getCity();
            $email->context($context)
                ->from(new Address('twowheelsformotion@gmail.com', '2Wheels4Motion - Annuaire Moto-taxi'))
                ->to($obUser->getEmail())
                ->subject("Bienvenue parmi la flotte de pilotes de l'Annuaire 2Wheels4Motion")
                // ->text('text') ou htmlTemplate au choix !!
                ->htmlTemplate('registration/newdriver_email.html.twig')
            ;
            $mailer->send($email);

            // envoi d'un courriel à l'administrateur pour validation du compte Driver
            $email=(new TemplatedEmail());
            $email->from(new Address($obUser->getEmail(), '2Wheels4Motion - Annuaire Moto-taxi'))
                ->to('twowheelsformotion@gmail.com')
                ->subject("Validation du référencement d'un nouveau PILOTE !")
                ->text("Vérifier le SIREN ".$siren." pour la demande de pilote de l'utilisateur "
                    .$obUser->getLastname()." ".$obUser->getFirstname()." (".$obUser->getId().")..."
                    ."Ainsi que sa carte VMDTR dont le n° est ".$obDriver->getVmdtrNumber()." !"
                )
            ;
            $mailer->send($email);

            return $this->render('profile/user.html.twig', [
                'error_firstname'   => false,
                'error_lastname'    => false,
                'error_phone'       => false,
                //
                'msg_info'   => "Votre demande a été signifiée à l'administrateur par courriel",
            ]);
        }

        // ... suite au choix d'une Company déjà "référencée"
        elseif(isset($_POST['companychoosen'])){
            $obCompany=$companies->findOneBy(['id'=>$_POST['companychoosen']]);
            $name=$obCompany->getName();
            $siren=$obCompany->getSiren();
            $nic=$obCompany->getNic();
            $road=$obCompany->getRoad();
            $zip=$obCompany->getZip();
            $city=$obCompany->getCity();
        }

        // ... autres cas, sources de conflit
        elseif(!is_null($driver_exist)){
            $driver_exist="Un pilote utilise déjà ce numéro de carte professionnelle VMDTR.";
        }
        elseif(isset($_POST['hasconfirmedgoodstanding'])
                and isset($_FILES) and $_FILES['file']['error']==4
        ){
            $error_files="Vous devez télécharger une copie de votre carte VMDTR.";
        }
        elseif(isset($_POST['hasconfirmedgoodstanding'])
                and isset($_FILES) and $_FILES['file']['error']==1
        ){
            $error_files="Le fichier image choisi dépasse la limite autorisée de 2Mo.";
        }

        // 1er passage, ou retour après erreur de saisie
        return $this->render('registration/driver.html.twig', [
            'error_files'   => $error_files,
            'driver_exist'  => $driver_exist,
            //
            'vmdtr_number'  => $vmdtr_number,
            'vmdtr_validity'=> $vmdtr_validity,
            'motomodel'     => $motomodel,
            'name'          => $name,
            'siren'         => $siren,
            'nic'           => $nic,
            'road'          => $road,
            'zip'           => $zip,
            'city'          => $city,
            'hasconfirmedgoodstanding'=>$hasconfirmedgoodstanding,
            //
            'allcompaniesknown'=>$companies->findBy(['isconfirmed'=>true]),
        ]);
    }

    // Invoquée lors de la vérification de l'adresse email,
    // via le lien d'authentification par mail
    /**
     * @Route("/verify/email", name="app_verify_email")
     */
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // dd(/*"VerifyUserEmail (91)"*/$request);
        dd('Envoie un email à partir de la page verifyUserEmail (? REGISTER ?)...');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('registration_user');
            // return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        // $this->addFlash('verify_email_error', 'Votre adresse électronique a été vérifiée.');
        $this->addFlash('success', 'Votre adresse électronique a été vérifiée.');
        return $this->redirectToRoute('app_login');
        // return $this->redirectToRoute('app_register');
    }
}
