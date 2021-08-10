<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\Mailer\MailerInterface;
// use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class MailerController extends AbstractController
{
    /**
     * @Route("/mailer", name="mailer")
     */
    public function index(): Response
    {
        return $this->render('mailer/index.html.twig', [
            'controller_name' => 'MailerController',
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
    //     return $this->render('mailer/index.html.twig', [
    //         'controller_name' => 'MailerController',
    //     ]);
    // }
}
