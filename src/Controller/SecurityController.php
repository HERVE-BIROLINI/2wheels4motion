<?php

namespace App\Controller;

use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

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
        if ($user) {
            // ... pour un compte qui n'a pas été vérifié, renvoi un nouvel email
            if ($user->isVerified()==null) {
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
                return $this->redirectToRoute('mailer_register');

            }

            // astuce de l'argument passé dans le RedirecToRoute qui nous a amené ici..
            elseif(isset($_GET['goto'])){
                $this->addFlash('success', "Content de vous voir ".$user->getFirstname().". Quel trajet auriez-vous besoin d'effectuer ?");
                return $this->redirectToRoute($_GET['goto']);
            }

            // ... si est déjà validé, et a un compte Pilote...
            elseif($user->getDriver()){
                return $this->redirectToRoute('profile_driver');
                // return $this->redirectToRoute('homepage');
            }
            // ... si est déjà validé, et a un compte Client...
            elseif($user->getCustomer()){
                return $this->redirectToRoute('profile_customer');
                // return $this->redirectToRoute('homepage');
            }
            // ... si est déjà validé, et a un compte Client...
            else{
                return $this->redirectToRoute('profile_user', ['id'=>$user->getID(),]);
                // return $this->redirectToRoute('homepage');
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
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
        dd("Passe dans LOGOUT (src/controller)");

        // tu peux écrire ce que tu veux là-dedans,
        // c'est ce qui est défini dans Config/Packages/SECURITY.YAML
        // qui sera lu et exécuté !...
        // Actuellement, renvoi vers 'security_login'
        //
        // throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
