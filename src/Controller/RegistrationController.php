<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Security\LoginFormAuthenticator;
use DateTime;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Validator\Constraints\Date;
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
     * @Route("/", name="user")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator): Response
    {
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
     * @Route("/driver", name="driver")
     */
    public function registerDriver(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator): Response
    {
        // $user = new User();
        // $form = $this->createForm(RegistrationFormType::class, $user);
        // $form->handleRequest($request);

        // if ($form->isSubmitted() && $form->isValid()) {
        //     // Défini les propriétés de l'objet "nouvel utilisateur"
        //     // (pour les propriétés non disponibles dans le formaulaire)
        //     // (encode the plain password)
        //     $user->setPassword(
        //         $passwordEncoder->encodePassword(
        //             $user,
        //             $form->get('plainPassword')->getData()
        //         )
        //     );
        //     //
        //     $user->setRegistrationDate(new DateTime('NOW'));
        //     // $user->setHasagreetoterms(0);

        //     // Effectue les enregistrements dans la BdD
        //     $entityManager = $this->getDoctrine()->getManager();
        //     $entityManager->persist($user);
        //     $entityManager->flush();

        //     // generate a signed url and email it to the user
        //     $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
        //         (new TemplatedEmail())
        //             ->from(new Address('twowheelsformotion@gmail.com', 'Annuaire Moto-taxi'))
        //             ->to($user->getEmail())
        //             ->subject('Merci de confirmer votre adresse électronique.')
        //             ->htmlTemplate('registration/confirmation_email.html.twig')
        //     );

        //     // do anything else you need here, like send an email
        //     // affiche la page d'information de l'envoi de l'email de confirmation
        //     // return $guardHandler->authenticateUserAndHandleSuccess(
        //     //     $user,
        //     //     $request,
        //     //     $authenticator,
        //     //     'main' // firewall name in security.yaml
        //     // );
        //     return $this->redirectToRoute('mailer');
        // }

        // return $this->render('registration/register.html.twig', [
        //     'registrationForm' => $form->createView(),
        // ]);
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
