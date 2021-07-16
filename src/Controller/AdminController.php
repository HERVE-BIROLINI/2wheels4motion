<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Driver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/", name="admin_")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="index")
     */
    public function index(): Response
    {
        $entityManager=$this->getDoctrine()->getManager();
        //
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            //
            'allcompaniesunknown'=>$entityManager->getRepository(Company::class)->findBy(['isconfirmed'=>null]),
            'alldriversunverified'=>$entityManager->getRepository(Driver::class)->findBy(['is_verified'=>null]),

        ]);
    }
    
    /**
     * @Route("/registered", name="read")
     */
    public function read(): Response
    {
        return $this->render('admin/read.html.twig', [
            // 'controller_name' => 'AdminController',
        ]);
    }
}
