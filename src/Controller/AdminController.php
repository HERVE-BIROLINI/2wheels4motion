<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Driver;
use App\Entity\Flatrate;
use App\Entity\Socialreason;
use App\Entity\Tva;
use App\Repository\FlatrateRepository;
use App\Repository\SocialreasonRepository;
use App\Repository\TvaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin", name="admin_")
 */
class AdminController extends AbstractController
{
    /**
     * ****************************************
     *  PAGE DE GESTION DES ACTIONS EN ATTENTE
     * ****************************************
     */

    /**
     * @Route("/pending", name="pending")
     */
    public function pending(): Response
    {
        // test si l'utilisateur N'est PAS encore identifié...
        if(!$this->getUser() or !in_array("ROLE_ADMIN",$this->getUser()->getRoles())){
            // ... renvoi vers la page de connection
            return $this->redirectToRoute('app_login');
        }

        $entityManager=$this->getDoctrine()->getManager();
        //
        return $this->render('admin/pending.html.twig', [
            'controller_name' => 'AdminController',
            //
            'allcompaniesunknown'=>$entityManager->getRepository(Company::class)->findBy(['isconfirmed'=>null]),
            'alldriversunverified'=>$entityManager->getRepository(Driver::class)->findBy(['is_verified'=>null]),
        ]);
    }

    /**
     * @Route("/company{company}/confirm", name="company_confirm", methods={"GET","POST"}, requirements={"company":"\d+"})
     */
    public function companyConfirm(Company $company): Response
    {
        // test si l'utilisateur N'est PAS encore identifié...
        if(!$this->getUser() or !in_array("ROLE_ADMIN",$this->getUser()->getRoles())){
            // ... renvoi vers la page de connection
            return $this->redirectToRoute('app_login');
        }

        $entityManager = $this->getDoctrine()->getManager();
        if($entityManager->getRepository(Company::class)->findOneBy(['id'=>$company->getId()])) {
            $company->setIsconfirmed(True);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_pending', [
            'controller_name' => 'AdminController',
            //
            'allcompaniesunknown'=>$entityManager->getRepository(Company::class)->findBy(['isconfirmed'=>null]),
            'alldriversunverified'=>$entityManager->getRepository(Driver::class)->findBy(['is_verified'=>null]),
        ]);
    }

    /**
     * @Route("/driver{driver}/confirm", name="driver_confirm", methods={"GET","POST"}, requirements={"driver":"\d+"})
     */
    public function driverConfirm(Driver $driver): Response
    {
        // test si l'utilisateur N'est PAS encore identifié...
        if(!$this->getUser() or !in_array("ROLE_ADMIN",$this->getUser()->getRoles())){
            // ... renvoi vers la page de connection
            return $this->redirectToRoute('app_login');
        }

        $entityManager = $this->getDoctrine()->getManager();
        if($entityManager->getRepository(Driver::class)->findOneBy(['id'=>$driver->getId()])) {
            $driver->setIsVerified(True);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_pending', [
            'controller_name' => 'AdminController',
            //
            'allcompaniesunknown'=>$entityManager->getRepository(Company::class)->findBy(['isconfirmed'=>null]),
            'alldriversunverified'=>$entityManager->getRepository(Driver::class)->findBy(['is_verified'=>null]),
        ]);
    }


    /**
     * ***********************************
     *  PAGE DE GESTION DES TARIFS (CRUD)
     * ***********************************
     */

    /**
     * @Route("/rate/create", name="flatrate_create", methods={"GET","POST"})
     */
    public function rateCreate(FlatrateRepository $flatrateRepository): Response
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

            // message de confirmation de la création
            $this->addFlash('success', "La création du nouveau tarif a bien été enregistrée...");
            //
            return $this->redirectToRoute('admin_flatrate_read', [
                'flatrates'  => $flatrateRepository->findAll(),
            ]);
        }

        return $this->render('flatrate/update.html.twig', [
            // 'controller_name' => 'AdminController',
            'flatrate'  => null,
        ]);
    }

    /**
     * @Route("/rates/list", name="flatrate_read", methods={"GET","POST"})
     */
    public function rateRead(FlatrateRepository $flatrateRepository): Response
    {
        // test si l'utilisateur N'est PAS encore identifié...
        if(!$this->getUser() or !in_array("ROLE_ADMIN",$this->getUser()->getRoles())){
            // ... renvoi vers la page de connection
            return $this->redirectToRoute('app_login');
        }

        return $this->render('flatrate/read.html.twig', [
            // 'controller_name' => 'AdminController',
            'flatrates'  => $flatrateRepository->findAll(),
        ]);
    }

    /**
     * @Route("/rate_{flatrate}/update", name="flatrate_update", methods={"GET","POST"})
     */
    public function rateUpdate(Flatrate $flatrate, FlatrateRepository $flatrateRepository): Response
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

            // message de confirmation de la création
            $this->addFlash('success', "La modification du tarif a bien été enregistrée...");
            //
            return $this->redirectToRoute('admin_flatrate_read', [
                // 'controller_name' => 'AdminController',
                'flatrates'  => $flatrateRepository->findAll(),
            ]);
        }

        return $this->render('flatrate/update.html.twig', [
            // 'controller_name' => 'FlatrateController',
            'flatrate'  => $obFlatrate,
        ]);
    }

    /**
     * @Route("/rate_{flatrate}/delete", name="flatrate_delete", methods={"GET","POST"}, requirements={"flatrate":"\d+"})
     */
    public function rateDelete(Request $request, Flatrate $flatrate, FlatrateRepository $flatrateRepository): Response
    {
        // test si l'utilisateur N'est PAS encore identifié...
        if(!$this->getUser() or !in_array("ROLE_ADMIN",$this->getUser()->getRoles())){
            // ... renvoi vers la page de connection
            return $this->redirectToRoute('app_login');
        }

        if($this->isCsrfTokenValid('delete'.$flatrate->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($flatrate);
            $entityManager->flush();
        }

        // message de confirmation de la création
        $this->addFlash('success', "La supression du tarif a bien été effectuée...");
        //
        return $this->redirectToRoute('admin_flatrate_read', [
            // 'controller_name' => 'AdminController',
            'flatrates'  => $flatrateRepository->findAll(),
        ]);
    }


    /**
     * ***************************************************************
     *  PAGE DE GESTION DES ASSOCIATIONS RAISON SOCIALE / TAUX DE TVA
     * ***************************************************************
     */

    /**
     * @Route("/socialreason", name="socialreason_settings", methods={"GET","POST"})
     */
    public function socialreasonSettings(SocialreasonRepository $socialreasonRepository, TvaRepository $tvaRepository): Response
    {
        // test si l'utilisateur N'est PAS encore identifié...
        if(!$this->getUser() or !in_array("ROLE_ADMIN",$this->getUser()->getRoles())){
            // ... renvoi vers la page de connection
            return $this->redirectToRoute('app_login');
        }
        
        //
        if(isset($_POST['socialreason']) and $_POST['socialreason']!=''
            and isset($_POST['tva']) and $_POST['tva']!=''
            )
        {
            // vérifie que l'association n'existe pas déjà
            $tva=$tvaRepository->findOneBy(['id'=>$_POST['tva']]);
            $socialreason=$socialreasonRepository->findOneBy(['id'=>$_POST['socialreason'] ]);//,'tva'=>array($tva)]);
            if(!$socialreason->getTva()->contains($tva)){
                // ... si NON, la créer
                $entityManager = $this->getDoctrine()->getManager();
                $socialreason->addTva($tva);
                $entityManager->persist($socialreason);
                $entityManager->flush();
                // message de confirmation de la création
                $this->addFlash('success', "La création de l'association du taux de TVA avec la raison solciale a bien été enregistrée...");
            }
            else{
                // message d'avertissement de l'existance préalable de l'association
                $this->addFlash('warning', "L'association du taux de TVA avec la raison solciale existe déjà...");
            }
        }

        return $this->render('socialreason/update.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @Route("/socialreason/assocdelete_{socialreason}-{tva}", name="socialreason_assocdelete", methods={"GET","POST"}, requirements={"socialreason":"\d+","tva":"\d+"})
     */
    public function assocDelete(Request $request, Socialreason $socialreason, Tva $tva): Response
    {
        // test si l'utilisateur N'est PAS encore identifié...
        if(!$this->getUser() or !in_array("ROLE_ADMIN",$this->getUser()->getRoles())){
            // ... renvoi vers la page de connection
            return $this->redirectToRoute('app_login');
        }
        
        //
        if($this->isCsrfTokenValid('delete'.$socialreason->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $socialreason->removeTva($tva);
            $entityManager->persist($socialreason);
            $entityManager->flush();
            // message de confirmation de suppression
            $this->addFlash('success', "La suppression de l'association du taux de TVA avec la raison solciale a bien été supprimée...");
        }

        return $this->redirectToRoute('admin_socialreason_settings');
    }


    /**
     * ***************************************************************
     *  PAGE DE GESTION DE.....
     * ***************************************************************
     */

    /**
     * @Route("/registered", name="registered")
     */
    public function read(): Response
    {
        return $this->render('admin/registered.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
}
