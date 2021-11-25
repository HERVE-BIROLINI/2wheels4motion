<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/", name="homepage_")
 */
class HomepageController extends AbstractController
{
    
    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        // echo'<pre>';
        // var_dump($_ENV);
        // echo'</pre>';
        // 
        return $this->render('homepage/index.html.twig', [
            'controller_name' => 'HomepageController',
        ]);
    }

    /**
     * @Route("/manual", name="manual")
     */
    public function manual(): Response
    {
        return $this->render('homepage/manual.html.twig', [
            'controller_name' => 'HomepageController',
        ]);
    }
}
