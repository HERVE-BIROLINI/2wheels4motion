<?php

namespace App\Controller;

use DateTime;
use App\Entity\Company;
use App\Entity\Driver;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Twig\FrenchGeographyTwig;
use App\Security\EmailVerifier;
use App\Security\LoginFormAuthenticator;
use App\Repository\DriverRepository;
use App\Repository\CompanyRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Validator\ValidatorInterface;
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
    public function register(Request $request
                            , UserPasswordEncoderInterface $passwordEncoder
                            , GuardAuthenticatorHandler $guardHandler
                            , LoginFormAuthenticator $authenticator
    ): Response
    {
        // test si l'utilisateur N'est PAS encore identifié
        if($this->getUser()){
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
    public function registerDriver(DriverRepository $drivers
                                , CompanyRepository $companies
                                , User $user
                                , MailerInterface $mailer
                                , ValidatorInterface $validator
    ): Response
    {

        // test si l'utilisateur N'est PAS encore identifié
        if(!$this->getUser() or $this->getUser()!==$user){
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

        if(isset($_POST['hasconfirmedgoodstanding'])
            and isset($_FILES) and $_FILES['file']['error']==0
            and $vmdtr_number!==null and !$driver_exist=$drivers->findBy(['vmdtr_number'=>$vmdtr_number])
        ){
            // Pour les enregistrements dans la BdD
            $entityManager = $this->getDoctrine()->getManager();

            // si tout est bon, il faudra :
            // - créer la company
            // - créer le driver
            // - ajouter la Company au Driver
            // - ajouter le rôle Driver à User
            // - ajouter le Driver à User
            //
            if(!$company=$companies->findOneBy(['siren'=>$siren])){
                $company=new Company;
                $company->setName($name);
                $company->setSiren($siren);
                if($nic != ''){
                    $company->setNic($nic);
                }
                $company->setRoad($road);
                $company->setZip($zip);
                $company->setCity($city);
                //
                // $error = $validator->validate($user);
                // if(count($error)){
                    // Do stuff
                // }else{
                    $entityManager->persist($company);
                    $entityManager->flush();
                // }
                echo('Company existante...');
            }

            //
            $driver=new Driver;
            $driver->setVmdtrNumber($vmdtr_number);
            $driver->setVmdtrValidity(new \DateTime($vmdtr_validity));
            $driver->setMotomodel($motomodel);
            $driver->setHasconfirmedgoodstanding($hasconfirmedgoodstanding);
            $driver->setCompany($company);
            $entityManager->persist($driver);
            $entityManager->flush();
            //
            $roles=$user->getRoles();
            $roles[]="ROLE_DRIVER";
            $user->setRoles($roles);
            $user->setDriver($driver);
            $entityManager->persist($user);
            $entityManager->flush();

            // envoi d'un courriel de confirmation de référencement
            // du Driver et de sa Company
            $email=(new TemplatedEmail());
            //
            $context = $email->getContext();
            $context['firstname']=$user->getFirstname();
            $context['vmdtrnumber']=$driver->getVmdtrNumber();
            $context['vmdtrvalidity']=$driver->getVmdtrValidity()->format('d/m/Y');
            $context['motomodel']=$driver->getMotomodel();
            $context['name']=$company->getName();
            $context['siren']=$company->getSiren();
            $context['nic']=$company->getNic();
            $context['road']=$company->getRoad();
            $context['zip']=$company->getZip();
            $context['city']=$company->getCity();
            $email->context($context)
                ->from(new Address('twowheelsformotion@gmail.com', '2Wheels4Motion - Annuaire Moto-taxi'))
                ->to($user->getEmail())
                ->subject("Bienvenue parmi la flotte de pilotes de l'Annuaire 2Wheels4Motion")
                // ->text('text') ou htmlTemplate au choix !!
                ->htmlTemplate('registration/newdriver_email.html.twig')
            ;
            $mailer->send($email);

            // envoi d'un courriel à l'administrateur pour validation du compte Driver
            $email=(new TemplatedEmail());
            $email->from(new Address($user->getEmail(), '2Wheels4Motion - Annuaire Moto-taxi'))
                ->to('twowheelsformotion@gmail.com')
                ->subject("Validation du référencement d'un nouveau PILOTE !")
                ->text("Vérifier le SIREN ".$siren." pour la demande de pilote de l'utilisateur "
                    .$user->getLastname()." ".$user->getFirstname()." (".$user->getId().")..."
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

        // Choix d'une Company "référencée"
        elseif(isset($_POST['companychoosen'])){
            $company=$companies->findOneBy(['id'=>$_POST['companychoosen']]);
            $name=$company->getName();
            $siren=$company->getSiren();
            $nic=$company->getNic();
            $road=$company->getRoad();
            $zip=$company->getZip();
            $city=$company->getCity();
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
