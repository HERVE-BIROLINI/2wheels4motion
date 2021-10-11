<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePwdFormType;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
// use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

/**
 * @Route("/security", name="security_")
 */
class SecurityController extends AbstractController
{
    private $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si "RETOUR" après connexion...
        $user=$this->getUser();
        if($user){
            // ... pour un compte qui n'a pas été vérifié, renvoi un nouvel email
            if($user->isVerified()==null) {
                // generate a signed url and email it to the user
                $this->emailVerifier->sendEmailConfirmation('registration_verify_email',
                                        $user,
                                        (new TemplatedEmail())
                                            ->from(new Address('twowheelsformotion@gmail.com', '2Wheels4Motion - Annuaire Moto-taxi'))
                                            ->to($user->getEmail())
                                            ->subject('Merci de confirmer votre adresse électronique.')
                                            ->htmlTemplate('mailer/confirmationregister_email.html.twig')
                );

                // do anything else you need here, like send an email
                // affiche la page d'information de l'envoi de l'email de confirmation
                return $this->redirectToRoute('mailer_register');

            }
            // astuce de l'argument passé dans le RedirecToRoute qui nous a amené ici..
            elseif(isset($_GET['goto']) && $user->isVerified()){
                if($_GET['goto'] == 'claim_create'){
                    return $this->redirectToRoute($_GET['goto'], [
                        'comefrom' => 'login',
                    ]);
                }elseif($_GET["goto"] == "tender_create"){
                    return $this->redirectToRoute($_GET['goto'], [
                        'claim' => $_GET['claim'],
                        'user'  => $_GET['user'],
                        'goto'  => $_GET['goto'],
                    ]);
                }elseif($_GET['goto'] == 'tender_read'){
                    if(isset($_GET['controller_func'])){
                        $controller_func=$_GET['controller_func'];
                    }else{
                        $controller_func=null;
                    }
                    if(isset($_GET['default_item'])){
                        $default_item=$_GET['default_item'];
                    }else{
                        $default_item=null;
                    }
                    return $this->redirectToRoute($_GET['goto'], [
                        'tender'   => $_GET['tender'],
                        'user'     => $_GET['user'],
                        'comefrom' => 'login',
                        //
                        'controller_func' => $controller_func,
                        'default_item'    => $default_item,
                    ]);
                }
            }
            // ... si est déjà validé, et a un compte Pilote...
            elseif($user->getDriver()){
                return $this->redirectToRoute('profile_driver');
            }
            // ... si est déjà validé, et a un compte Client...
            elseif($user->getCustomer()){
                return $this->redirectToRoute('profile_customer');
            }
            // ... si est déjà validé, et a un compte Client...
            else{
                return $this->redirectToRoute('profile_user', ['id'=>$user->getID(),]);
            }
        }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // lance la page login
        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(){
        dd("Passe dans LOGOUT (src/controller)");

        // tu peux écrire ce que tu veux là-dedans,
        // c'est ce qui est défini dans Config/Packages/SECURITY.YAML
        // qui sera lu et exécuté !...
        // Actuellement, renvoi vers 'security_login'
        //
        // throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("{id}/pwd", name="pwd", methods={"GET","POST"}, requirements={"id":"\d+"})
     */
    public function changePwd(Request $request
                            , User $user
                            // , UserRepository $repository
                            , UserPasswordEncoderInterface $passwordEncoder
                            // , GuardAuthenticatorHandler $guardHandler
                            // , LoginFormAuthenticator $authenticator
                            // , AuthenticationUtils $authenticationUtils
    ): Response
    {

        // test si l'utilisateur N'est PAS encore identifié
        if(!$this->getUser() or $this->getUser()!==$user){
            return $this->redirectToRoute('security_login');
        }

        $verify_password_error=false;
        // Crée et envoi le formulaire
        $form = $this->createForm(ChangePwdFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            if(isset($_POST['password-old']) 
                // && preg_match("@^[a-z0-9]+@i", $_POST['password-old']==1)
                && password_verify ($_POST['password-old'] , $user->getPassword() )
            ){

                // encode the plain password
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
                //
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('security_logout');
            }
            else{
                $verify_password_error=true;
            }
        }

        return $this->render('security/changepwd.html.twig', [
            'changePwdForm'         => $form->createView(),
            'controller_func'       => 'ChangePwd',
            'verify_password_error' => $verify_password_error
        ]);
    }


    /**
     * @Route("/forgottenpwd", name="forgottenpwd")
     */
    public function forgotten_password(AuthenticationUtils $authenticationUtils, MailerInterface $mailer)
    {
        if(isset($_POST['email']) && $_POST['email']!==''){
            $entityManager = $this->getDoctrine()->getManager();
            $user=$entityManager->getRepository(User::class)->findOneBy(['email'=>$_POST['email']]);
            // Vérifie l'existance de l'utilisateur
            if ($user === null) {
                // On envoie une alerte disant que l'adresse e-mail est inconnue
                $this->addFlash('danger', 'Cette adresse e-mail est inconnue');
                // On retourne sur la page de connexion
                return $this->redirectToRoute('security_login');
            }
            // S'il existe bien...
            // ... génère l'adresse URL de redéfinition du Mot de passe
            $url = $this->generateUrl('security_resetpwd',
                                        array('token'  => $user->getPassword(),
                                                'user' => $user->getId(),
                                        ),
                                        UrlGeneratorInterface::ABSOLUTE_URL
                                    )
            ;
            // ... envoie le lien par courriel
            $email=(new TemplatedEmail());
            //
            $context = $email->getContext();
            $context['firstname']=$user->getFirstname();
            $context['url']=$url;
            //
            $email->context($context)
                ->from(new Address('twowheelsformotion@gmail.com', '2Wheels4Motion - Annuaire Moto-taxi'))
                ->to($user->getEmail())
                ->subject("Demande de réinitialisation de mot de passe")
                ->htmlTemplate('mailer/resetpwd_email.html.twig')
            ;
            $mailer->send($email);
            // On crée le message flash de confirmation
            $this->addFlash('information', 'E-mail de réinitialisation du mot de passe envoyé !');
            // On redirige vers la page de login
            return $this->redirectToRoute('security_login');
        }elseif(isset($_POST['email'])){
            $error_email=true;
        }else{$error_email=null;}

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/forgotten_password.html.twig', [
                                'last_username' => $lastUsername,
                                'error'         => $error,
                                'error_email'   => $error_email,
        ]);
    }

    /**
     * @Route("/resetpwd", name="resetpwd")
     */
    public function reset_password(Request $request, UserPasswordEncoderInterface $passwordEncoder){

        if(isset($_GET['token']) && isset($_GET['user'])){
            $entityManager = $this->getDoctrine()->getManager();
            $user=$entityManager->getRepository(User::class)->findOneBy(['id'=>$_GET['user']]);
        }
        //
        if(isset($form) || ($user && $user->getPassword()===$_GET['token'])){
            // Crée et envoi le formulaire
            $form = $this->createForm(ChangePwdFormType::class, $user);
            $form->handleRequest($request);
            //
            if(isset($form) && $form->isSubmitted() && $form->isValid()){
                // encode the plain password
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
                //
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
                //
                $this->addFlash('success', 'Mot de passe réinitialisé...');
                return $this->redirectToRoute('security_login');
            }
            return $this->render('security/resetpwd.html.twig', [
                'changePwdForm' => $form->createView(),
            ]);
        }
        //
        $this->addFlash('warning', 'Lien de réinitialisation de mot de passe invalide...');
        return $this->redirectToRoute('homepage_index');
    }
}
