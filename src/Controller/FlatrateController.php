<?php

namespace App\Controller;

use App\Entity\Flatrate;
use App\Repository\DriverRepository;
use App\Repository\FlatrateRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/tarif", name="flatrate_")
 */
class FlatrateController extends AbstractController
{

    /**
     * @Route("/create", name="create", methods={"GET","POST"})
     */
    public function tarifCreate(FlatrateRepository $flatrateRepository): Response
    {
        // test si l'utilisateur N'est PAS encore identifié...
        if(!$this->getUser() or !in_array("ROLE_ADMIN",$this->getUser()->getRoles())){
            // ... renvoi vers la page de connection
            return $this->redirectToRoute('app_login');
        }
        
        // if(!$obFlatrate=$flatrateRepository->findOneBy(['id'=>$flatrate])){
        //     $obFlatrate=null;
        // }

        if(isset($_POST['label']) and $_POST['label']!=''and $_POST['price']!=''){
            $entityManager = $this->getDoctrine()->getManager();
            $obFlatrate=new Flatrate;
            $obFlatrate->setLabel($_POST['label']);
            $obFlatrate->setPrice($_POST['price']);
            //
            if(isset($_POST['pickupincluded'])){
                $obFlatrate->setPickupIncluded(true);
            }
            else{
                $obFlatrate->setPickupIncluded(false);
            }
            //
            $obFlatrate->setDepartmentCode($_POST['department']);
            
            $entityManager->persist($obFlatrate);
            $entityManager->flush();
            //
            return $this->redirectToRoute('flatrate_read', [
                'flatrates'  => $flatrateRepository->findAll(),
            ]);
        }

        return $this->render('flatrate/update.html.twig', [
            // 'controller_name' => 'FlatrateController',
            'flatrate'  => null,
        ]);
    }

    /**
     * @Route("s/list", name="read", methods={"GET","POST"})
     */
    public function tarifRead(FlatrateRepository $flatrateRepository): Response
    {
        // test si l'utilisateur N'est PAS encore identifié...
        if(!$this->getUser() or !in_array("ROLE_ADMIN",$this->getUser()->getRoles())){
            // ... renvoi vers la page de connection
            return $this->redirectToRoute('app_login');
        }

        return $this->render('flatrate/read.html.twig', [
            // 'controller_name' => 'FlatrateController',
            'flatrates'  => $flatrateRepository->findAll(),
        ]);
    }

    /**
     * @Route("{flatrate}/update", name="update", methods={"GET","POST"})
     */
    public function tarifUpdate(Flatrate $flatrate, FlatrateRepository $flatrateRepository): Response
    {
        // test si l'utilisateur N'est PAS encore identifié...
        if(!$this->getUser() or !in_array("ROLE_ADMIN",$this->getUser()->getRoles())){
            // ... renvoi vers la page de connection
            return $this->redirectToRoute('app_login');
        }
        
        if(!$obFlatrate=$flatrateRepository->findOneBy(['id'=>$flatrate])){
            $obFlatrate=null;
        }

        if(isset($_POST['label'])){
            $entityManager = $this->getDoctrine()->getManager();
            $obFlatrate->setLabel($_POST['label']);
            $obFlatrate->setPrice($_POST['price']);
            //
            if(isset($_POST['pickupincluded'])){
                $obFlatrate->setPickupIncluded(true);
            }
            else{
                $obFlatrate->setPickupIncluded(false);
            }
            //
            $obFlatrate->setDepartmentCode($_POST['department']);

            $entityManager->persist($obFlatrate);
            $entityManager->flush();
            //
            return $this->redirectToRoute('flatrate_read', [
            // return $this->render('flatrate/read.html.twig', [
                // 'controller_name' => 'FlatrateController',
                'flatrates'  => $flatrateRepository->findAll(),
            ]);
        }

        return $this->render('flatrate/update.html.twig', [
            // 'controller_name' => 'FlatrateController',
            'flatrate'  => $obFlatrate,
        ]);
    }

    /**
     * @Route("{flatrate}/delete", name="delete", methods={"GET","POST"}, requirements={"flatrate":"\d+"})
     */
    public function tarifDelete(Request $request, Flatrate $flatrate, FlatrateRepository $flatrateRepository): Response
    {
        // test si l'utilisateur N'est PAS encore identifié...
        if(!$this->getUser() or !in_array("ROLE_ADMIN",$this->getUser()->getRoles())){
            // ... renvoi vers la page de connection
            return $this->redirectToRoute('app_login');
        }

        if ($this->isCsrfTokenValid('delete'.$flatrate->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($flatrate);
            $entityManager->flush();
        }

        return $this->redirectToRoute('flatrate_read', [
            'flatrates'  => $flatrateRepository->findAll(),
        ]);
    }

    /**
     * @Route("s", name="index")
     */
    public function index(FlatrateRepository $flatrateRepository): Response
    {
        return $this->render('flatrate/index.html.twig', [
            'controller_name' => 'FlatrateController',
            'flatrates'  => $flatrateRepository->findAll(),
        ]);
    }
}
