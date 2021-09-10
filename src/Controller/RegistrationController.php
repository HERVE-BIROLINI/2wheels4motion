<?php

namespace App\Controller;

use DateTime;
use App\Entity\Company;
use App\Entity\Driver;
use App\Entity\Picture;
use App\Entity\Picturelabel;
use App\Entity\Socialreason;
use App\Entity\User;
use App\Form\RegistrationFormType;
// use App\Twig\FrenchGeographyTwig;
use App\Security\EmailVerifier;
// use App\Security\LoginFormAuthenticator;
// use App\Repository\DriverRepository;
// use App\Repository\CompanyRepository;
// use App\Repository\PicturelabelRepository;
// use App\Tools\RegexTools;
use App\Tools\UploadPictureTools;
use App\Twig\PictureTwig;
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

    public function __construct(EmailVerifier $emailVerifier)
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
            return $this->redirectToRoute('security_login');
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
            $user->setRegistrationDate(new DateTime('now'));
            // $user->setHasagreetoterms(0);

            // Effectue les enregistrements dans la BdD
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('registration_app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('twowheelsformotion@gmail.com', '2Wheels4Motion - Annuaire Moto-taxi'))
                    ->to($user->getEmail())
                    ->subject('Merci de confirmer votre adresse électronique.')
                    ->htmlTemplate('mailer/confirmationregister_email.html.twig')
            );

            // do anything else you need here, like send an email
            // affiche la page d'information de l'envoi de l'email de confirmation
            // return $guardHandler->authenticateUserAndHandleSuccess(
            //     $user,
            //     $request,
            //     $authenticator,
            //     'main' // firewall name in security.yaml
            // );
            return $this->redirectToRoute('mailer_register');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user{id}/driver", name="driver", methods={"GET","POST"})
     */ 
    public function registerDriver(User $user, MailerInterface $mailer): Response
    {
        // Test si l'utilisateur N'est PAS encore identifié
        //  OU si l'utilisateur a déjà fait une demande
        if(!$this->getUser() or $this->getUser()!==$user
            or $this->getUser()->getDriver()
        ){
            return $this->redirectToRoute('security_login');
        }

        // Pour les accès à la BdD
        $entityManager = $this->getDoctrine()->getManager();

        // valeur par défaut des variables à transmettre à TWIG
        $error_name=null;
        $error_siren=null;
        $error_road=null;
        $error_nic=null;
        $error_city=null;
        //
        $error_vmdtrnumber=null;
        $error_vmdtrvalidity=null;
        $error_motomodel=null;
        $error_file=null;
        $error_hasconfirmedgoodstanding=null;
        $driver_existing=null;
        //
        $name=null;
        $siren=null;
        $nic=null;
        $road=null;
        $zip=null;
        $city=null;
        //
        $vmdtr_number=null;
        $vmdtr_validity=null;
        $motomodel=null;
        $hasconfirmedgoodstanding=null;
        //
        $bCreateT3PIsReady=false;
        $bUploadIsReady=true;
        // $bError=null;
        // ... important pour ne pas "parasiter" l'affichage des données de la T3P
        $obCompany=null;
        
        // ** Retour (1/2) dans le Controller suite au choix d'une Company déjà "référencée" **
        // ************************************************************************************
        if(isset($_POST['companychoosen'])){
            // instancie une nouvelle valeur à l'objet Company,
            // TWIG réassignera les valeurs dans le formulaire...
            // $obCompany=$companyRepository->findOneBy(['id'=>$_POST['companychoosen']]);
            $obCompany=$entityManager->getRepository(Company::class)->findOneBy(['id'=>$_POST['companychoosen']]);
            //
            $this->addFlash('information', "Données d'une entreprise T3P référencée récupérées. Pensez à enregistrer vos modifications pour confirmer...");
        }
        // ** Retour (2/2) dans le Controller suite au choix au Submit **
        // **************************************************************
        elseif(count($_POST)>0){
            // * Entreprise T3P *
            // ------------------
            //  => NAME
            $name=$_POST['name'];
            if($name==''){$error_name=true;}
            //  => SIREN (nombre à 9 chiffres)
            $siren=$_POST['siren'];
            if(!is_numeric($siren) || strlen($siren)>9){
                $error_siren=true;
            }
            //  => NIC
            $nic=$_POST['nic'];
            if($nic!='' and (!is_numeric($_POST['nic']) || strlen($_POST['nic'])!=5)){
                $error_nic=true;
            }
            //  ... contrôle l'existance ou non d'une entreprise T3P pour les données saisies
            //  (d'abord vérifie l'existance de la T3P,
            //      ...
            //    )
            if(!$obCompany=$entityManager->getRepository(Company::class)
                    ->findOneBy(['name'=>$name,'siren'=>$siren,'nic'=>$nic])
                )
            {
                //  ... si n'est pas une entreprise choisie parmi celles déjà référencées
                //  ... ET est en conflit avec une déjà existante
                if($entityManager->getRepository(Company::class)->findOneBy(['name'=>$name])){
                    $error_name=true;
                    $this->addFlash('danger', "Une entreprise T3P du même existe déjà référencée sous un autre n° de SIREN.");
                }
                elseif($entityManager->getRepository(Company::class)->findOneBy(['siren'=>$siren])){
                    $error_siren=true;
                    $this->addFlash('danger', "Une entreprise T3P référencée avec ce n° de SIREN existe déjà sous un autre nom.");
                }
                //  ... Sinon, prépare la création d'une nouvelle T3P, sans l'effectuer
                else{
                    $bCreateT3PIsReady=true;
                }
            }

            //  => SOCIALREASON
            $socialreason=$_POST['socialreason'];
            //  => ADDRESS
            $road=$_POST['road'];
            if($road==''){$error_road=true;}
            $city=$_POST['city'];
            if($city==''){$error_city=true;}
            $zip=$_POST['zip'];

            // * Pilote VMDTR *
            // ----------------
            //  => VMDTR_NUMBER (nombre à 11 chiffres)...
            $vmdtr_number=$_POST['vmdtr_number'];
            if(!is_numeric($vmdtr_number) || strlen($vmdtr_number)!=11){
                $error_vmdtrnumber=true;
            }
            //      ... et NON déjà référencé
            elseif($entityManager->getRepository(Driver::class)->findOneBy(['vmdtr_number'=>$vmdtr_number])){
                $error_vmdtrnumber=true;
                $this->addFlash('warning', "Il existe déjà un pilote référencé avec ce n° de carte pro. VMDTR");
            }

            //  => VMDTR_VALIDITY
            $vmdtr_validity=$_POST['vmdtr_validity'];
            if($vmdtr_validity==''){$error_vmdtrvalidity=true;}

            //  => MOTOMODEL
            $motomodel=$_POST['motomodel'];
            if($motomodel==''){$error_motomodel=true;}

            //  => FILE (d'abord vérifie/prépare le "déplacement" sans l'effectuer)
            // Cas quasi impossible de la primo-existance d'une image...
            $obPictureTwig=new PictureTwig($entityManager);
            if(!$obPicture=$obPictureTwig->getPictureOfDriverCard($user)){

                if(!(isset($_FILES['file']) and $_FILES['file']['name']!='' and $_FILES['file']['error']==0)){
                    $bUploadIsReady=false;
                    $error_file=true;
                    //
                    $this->addFlash('warning', "Vous devez impérativement télécharger une copie de votre carte pro. VMDTR");
                }

            }
            //  => CONFIRMATION DE L'EXACTITUDE DES INFO FOURNIES... *
            if(isset($_POST['hasconfirmedgoodstanding'])){
                $hasconfirmedgoodstanding=$_POST['hasconfirmedgoodstanding'];
            }
            else{
                $error_hasconfirmedgoodstanding=true;
            }

            // * SI TOUT EST CORRECT, POURSUIT L'INSCRIPTION ET ENVOIE LE COURRIEL *
            // ---------------------------------------------------------------------
            if($hasconfirmedgoodstanding
                and !$error_name
                and !$error_siren
                and !$error_road
                and !$error_city
                and !$error_vmdtrnumber
                and !$error_vmdtrvalidity
                and !$error_motomodel
                and !$error_file

                // Cas quasi impossible de la primo-existance d'une image...
                and ($obPicture or
                    //  ... upload le fichier ($bUploadIsReady=true)
                    // and 
                    $bUploadIsReady
                    and $obPicturelabel_VMDTR=$entityManager->getRepository(Picturelabel::class)->findOneBy(['label'=>'Carte pro. VMDTR - Face'])
                    and $obUploadPicture=new UploadPictureTools($this->container)
                    and $obUploadPicture=$obUploadPicture->UploadPicture($user, $obPicturelabel_VMDTR, $this->getParameter('asset_path_dev'))
                    )
                )
            {
                //  => La company T3P
                //  ... si non-référence, la créer
                if(!$obCompany and $bCreateT3PIsReady)
                {
                    $obCompany=new Company;
                    $obCompany->setName($name);
                    $obCompany->setSiren($siren);
                    if($nic != ''){$obCompany->setNic($nic);}
                    $obCompany->setSocialreason($entityManager->getRepository(Socialreason::class)->findOneBy(['id'=>$socialreason]));
                    $obCompany->setRoad($road);
                    $obCompany->setZip($zip);
                    $obCompany->setCity($city);
                    //
                    $entityManager->persist($obCompany);
                }
                
                //  => File (Picture)
                // Cas quasi impossible de la primo-existance d'une image...
                if(!$obPicture){
                    //  ... vérifie s'il existe une image DEJA enregistrée => instancie l'objet
                    $obPicturelabel_VMDTR=$entityManager->getRepository(Picturelabel::class)->findOneBy(['label'=>'Carte pro. VMDTR - Face']);
                    if($obPicture=$entityManager->getRepository(Picture::class)->findOneBy(['user'=>$user ,'picturelabel'=>$obPicturelabel_VMDTR])){
                        // ... Supprime le fichier précédent, si existe
                        if($obUploadPicture and $obUploadPicture->getDeletePreviousFile() and str_contains($obPicture->getPathname(),'user'))
                        {
                            if(file_exists($this->getParameter('asset_path_dev')
                                        .substr(strstr($obPicture->getPathname(),"/"),1)
                                    )
                                )
                            {
                                unlink($this->getParameter('asset_path_dev')
                                    .substr(strstr($obPicture->getPathname(),"/"),1)
                                );
                            }
                            if(file_exists($this->getParameter('asset_path_prod')
                                        .substr(strstr($obPicture->getPathname(),"/"),1)
                                    )
                                )
                            {
                                unlink($this->getParameter('asset_path_prod')
                                    .substr(strstr($obPicture->getPathname(),"/"),1)
                                );
                            }
                            //
                            $this->addFlash('success', "L'ancienne image a été effacée au profit de la nouvelle...");
                        }
                    }
                    //  ... si non...
                    else{
                        // ... Instancie un nouvel objet Picture
                        $obPicture=new Picture;
                    }
                    //  ... précise le nom du fichier
                    $obPicture->setPathname($obUploadPicture->getPathName());
                    //  ... précise le sujet de l'image
                    $obPicture->setPicturelabel($obPicturelabel_VMDTR);
                    //  ... précise le User propriétaire de l'image
                    $obPicture->setUser($user);
                    //
                    $entityManager->persist($obPicture);
                }

                //  => Le Driver (VMDTR)
                $obDriver=new Driver();
                $obDriver->setVmdtrNumber($vmdtr_number);
                $obDriver->setVmdtrValidity(new \DateTime($vmdtr_validity));
                $obDriver->setMotomodel($motomodel);
        // reste à développer :
        // $obDriver->setSubscriptionValidity($...);
                $obDriver->setHasconfirmedgoodstanding($hasconfirmedgoodstanding);
                $obDriver->setCompany($obCompany);
                $entityManager->persist($obDriver);                
                // ... associe le nouveau Driver à l' User
                $user->setDriver($obDriver);
                //
                $entityManager->persist($user);
                
                // "charge" la BdD
                $entityManager->flush();

                //  => L'eMail...
                //  envoi d'un courriel de confirmation de référencement...
                //  ... du Driver et de sa Company
                $email=(new TemplatedEmail());
                //
                $context = $email->getContext();
                $context['firstname']=$user->getFirstname();
                $context['vmdtrnumber']=$obDriver->getVmdtrNumber();
                $context['vmdtrvalidity']=$obDriver->getVmdtrValidity()->format('d/m/Y');
                $context['motomodel']=$obDriver->getMotomodel();
                $context['name']=$obCompany->getName();
                $context['siren']=$obCompany->getSiren();
                $context['nic']=$obCompany->getNic();
                $context['road']=$obCompany->getRoad();
                $context['zip']=$obCompany->getZip();
                $context['city']=$obCompany->getCity();
                //
                $email->context($context)
                    ->from(new Address('twowheelsformotion@gmail.com', '2Wheels4Motion - Annuaire Moto-taxi'))
                    ->to($user->getEmail())
                    ->subject("Bienvenue parmi la flotte de pilotes de l'Annuaire 2Wheels4Motion")
                    // ->text('text') ou htmlTemplate au choix !!
                    ->htmlTemplate('mailer/newdriver.html.twig')
                ;
                $mailer->send($email);

                //  Envoi d'un courriel à l'administrateur pour validation du compte Driver
                $email=(new TemplatedEmail());
                $email->from(new Address($user->getEmail(), '2Wheels4Motion - Annuaire Moto-taxi'))
                    ->to('twowheelsformotion@gmail.com')
                    ->subject("Validation du référencement d'un nouveau PILOTE !")
                    ->text("Vérifier le SIREN ".$siren." pour la demande de pilote de l'utilisateur "
                            .$user->getLastname()." ".$user->getFirstname()." (".$user->getId().")..."
                            ."Ainsi que sa carte VMDTR dont le n° est ".$obDriver->getVmdtrNumber()." !"
                        )
                ;
                $mailer->send($email);

                $this->addFlash('information', "Votre demande a été signifiée à l'administrateur par courriel");
                return $this->render('profile/user.html.twig', [
                    'error_firstname'   => false,
                    'error_lastname'    => false,
                    'error_phone'       => false,
                ]);

            }
        }
        // 1er passage, ou retour après erreur de saisie
        return $this->render('registration/driver.html.twig', [
            'controller_name'   => 'RegistrationController',
            //
            'error_name'            => $error_name,
            'error_siren'           => $error_siren,
            'error_nic'             => $error_nic,
            'error_road'            => $error_road,
            'error_city'            => $error_city,
            'error_vmdtrnumber'     => $error_vmdtrnumber,
            'error_vmdtrvalidity'   => $error_vmdtrvalidity,
            'error_motomodel'       => $error_motomodel,
            'error_file'            => $error_file,
            'error_hasconfirmedgoodstanding'=>$error_hasconfirmedgoodstanding,
            //
            'company'   => $obCompany,
            //
            'name'  => $name,
            'siren' => $siren,
            'nic'   => $nic,
            'road'  => $road,
            'zip'   => $zip,
            'city'  => $city,
            //
            'driver_existing'   => $driver_existing,
            'vmdtr_number'      => $vmdtr_number,
            'vmdtr_validity'    => $vmdtr_validity,
            'motomodel'         => $motomodel,
            'hasconfirmedgoodstanding'=>$hasconfirmedgoodstanding,
            //
            'allcompaniesknown' =>$entityManager->getRepository(Company::class)->findBy(['isconfirmed'=>true]),
        ]);
    }

    // Invoquée lors de la vérification de l'adresse email,
    // via le lien d'authentification contenu dans l'email
    /**
     * @Route("/verify_email", name="app_verify_email")
     */
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

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
        return $this->redirectToRoute('security_login');
        // return $this->redirectToRoute('app_register');
    }


    static function getIp(){
        if(!empty($_SERVER['HTTP_CLIENT_IP'])){
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
}
