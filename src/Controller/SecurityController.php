<?php

namespace App\Controller;

use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si "RETOUR" après connexion...
        $user=$this->getUser();
        if ($user) {
            // ... pour un compte qui n'a pas été vérifié, renvoi un email
            if ($user->isVerified()==null) {
dd('RE-envoie un email à partir de la page LOGIN...');
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
        return $this->redirectToRoute('mailer');

            }
            // ... si au contraire est déjà validé, retourne à l'accueil
            else{
                return $this->redirectToRoute('homepage');
            }
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // lance la page login,
        // ce qu'il se passe à la sortie du formulaire... ????
        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        dd("Passe dans LOGOUT (src/controller)");

        // tu peux écrire ce que tu veux là-dedans,
        // c'est ce qui est défini dans SECURITY.YAML 
        // qui sera lu et exécuté !...
        //
        // throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
