<?php

namespace App\Controller;

// use App\Entity\Flatrate;
use App\Repository\FlatrateRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/tarif", name="flatrate_")
 */
class FlatrateController extends AbstractController
{
    /**
     * @Route("s", name="index")
     */
    public function index(FlatrateRepository $flatrateRepository): Response
    {
        return $this->render('flatrate/index.html.twig', [
            'controller_name' => 'FlatrateController',
            'flatrates' => $flatrateRepository->findBy(array(), array('region_code'=>'asc','price'=>'asc','label'=>'asc')),
            // 'flatrates'  => $flatrateRepository->findAll(),
        ]);
    }
}
