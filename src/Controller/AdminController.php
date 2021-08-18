<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Driver;
use App\Entity\Flatrate;
use App\Entity\Remarkableplace;
use App\Entity\Socialreason;
use App\Entity\Tva;
use App\Entity\Typeplace;
use App\Repository\FlatrateRepository;
use App\Repository\RemarkableplaceRepository;
use App\Repository\SocialreasonRepository;
use App\Repository\TvaRepository;
use App\Repository\TypeplaceRepository;
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
     * *****************************************
     *  PAGE DE GESTION DES LIEUX "TYPE" (CRUD)
     * *****************************************
     */

    /**
     * @Route("/typeplace/create", name="typeplace_create", methods={"GET","POST"})
     */
    public function typeplaceCreate(TypeplaceRepository $typeplaceRepository): Response
    {
        // test si l'utilisateur N'est PAS encore identifié...
        if(!$this->getUser() or !in_array("ROLE_ADMIN",$this->getUser()->getRoles())){
            // ... renvoi vers la page de connection
            return $this->redirectToRoute('app_login');
        }

        //
        $label=null;
        $error_label=null;
        //
        $entityManager=$this->getDoctrine()->getManager();
        
        if(isset($_POST['label']) and ($label=$_POST['label'] or true)
            and !$obTypeplace=$entityManager->getRepository(Typeplace::class)->findOneBy(['label'=>$label])
            and $label!=''
            )
        {
            $entityManager=$this->getDoctrine()->getManager();
            $obTypeplace=new Typeplace;
            $obTypeplace->setLabel($_POST['label']);
            //
            $entityManager->persist($obTypeplace);
            $entityManager->flush();

            // message de confirmation de la création
            $this->addFlash('success', "La création du nouveau \"type de lieu\" a bien été enregistrée...");
            //
            return $this->redirectToRoute('admin_typeplace_read', [
                'typeplaces'  => $typeplaceRepository->findAll(),
            ]);
        }
        elseif(isset($obTypeplace)){
            $this->addFlash('danger', "Un \"type de lieu\" avec ce libellé existe déjà...");
        }
        //
        if(isset($label) and $label==''){$error_label=true;$label=null;}
        //
        return $this->render('typeplace/update.html.twig', [
            'route'     => 'typeplace_create',
            //
            'typeplace' => null,
            'label'     => $label,
            //
            'error_label'   => $error_label,
        ]);
    }

    /**
     * @Route("/typeplaces/list", name="typeplace_read", methods={"GET","POST"})
     */
    public function typeplaceRead(TypeplaceRepository $typeplaceRepository): Response
    {
        // test si l'utilisateur N'est PAS encore identifié...
        if(!$this->getUser() or !in_array("ROLE_ADMIN",$this->getUser()->getRoles())){
            // ... renvoi vers la page de connection
            return $this->redirectToRoute('app_login');
        }

        return $this->render('typeplace/read.html.twig', [
            'controller_name' => 'AdminController',
            'typeplaces'  => $typeplaceRepository->findBy(array(), array('label'=>'asc')),
            // 'typeplaces'  => $typeplaceRepository->findAll(),
        ]);
    }

    /**
     * @Route("/typeplace_{typeplace}/update", name="typeplace_update", methods={"GET","POST"})
     */
    public function typeplaceUpdate(Typeplace $typeplace, TypeplaceRepository $typeplaceRepository): Response
    {
        // test si l'utilisateur N'est PAS encore identifié...
        if(!$this->getUser() or !in_array("ROLE_ADMIN",$this->getUser()->getRoles())){
            // ... renvoi vers la page de connection
            return $this->redirectToRoute('app_login');
        }
        //
        if(!$obTypeplace=$typeplaceRepository->findOneBy(['id'=>$typeplace])){
            $obTypeplace=null;
        }

        //
        $label=null;
        $error_label=null;
        //
        $entityManager = $this->getDoctrine()->getManager();

        if(isset($_POST['label']) and ($label=$_POST['label'] or true)
            and (!$obTypeplacefounded=$entityManager->getRepository(Typeplace::class)->findOneBy(['label'=>$label])
                    or $obTypeplacefounded==$typeplace
                )
            and $label!=''
            )
        {
            $obTypeplace->setLabel($_POST['label']);

            $entityManager->persist($obTypeplace);
            $entityManager->flush();

            // message de confirmation de la création
            $this->addFlash('success', "La modification du \"type de lieu\" a bien été enregistrée...");
            //
            return $this->redirectToRoute('admin_typeplace_read', [
                // 'controller_name' => 'AdminController',
                'typeplaces'  => $typeplaceRepository->findAll(),
            ]);
        }
        elseif(isset($obTypeplacefounded)){
            $this->addFlash('danger', "Un \"type de lieu\" avec ce libellé existe déjà...");
        }
        //
        if(isset($label) and $label==''){$error_label=true;$label=null;}
        //
        return $this->render('typeplace/update.html.twig', [
            // 'controller_name' => 'TypeplaceController',
            'route'     => 'typeplace_update',
            //
            'typeplace' => $obTypeplace,
            'label'     => $label,
            //
            'error_label'   => $error_label,
        ]);
    }

    /**
     * @Route("/typeplace_{typeplace}/delete", name="typeplace_delete", methods={"GET","POST"}, requirements={"typeplace":"\d+"})
     */
    public function typeplaceDelete(Request $request, Typeplace $typeplace, TypeplaceRepository $typeplaceRepository): Response
    {
        // test si l'utilisateur N'est PAS encore identifié...
        if(!$this->getUser() or !in_array("ROLE_ADMIN",$this->getUser()->getRoles())){
            // ... renvoi vers la page de connection
            return $this->redirectToRoute('app_login');
        }

        if($this->isCsrfTokenValid('delete'.$typeplace->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($typeplace);
            $entityManager->flush();
        }

        // message de confirmation de la création
        $this->addFlash('success', "La supression du \"type de lieu\" a bien été effectuée...");
        //
        return $this->redirectToRoute('admin_typeplace_read', [
            // 'controller_name' => 'AdminController',
            'typeplaces'  => $typeplaceRepository->findAll(),
        ]);
    }


    /**
     * *************************************************
     *  PAGE DE GESTION DES LIEUX "REMARQUABLES" (CRUD)
     * *************************************************
     */

    /**
     * @Route("/remarkableplace/create", name="remarkableplace_create", methods={"GET","POST"})
     */
    public function remarkableplaceCreate(RemarkableplaceRepository $remarkableplaceRepository): Response
    {
        // test si l'utilisateur N'est PAS encore identifié...
        if(!$this->getUser() or !in_array("ROLE_ADMIN",$this->getUser()->getRoles())){
            // ... renvoi vers la page de connection
            return $this->redirectToRoute('app_login');
        }

        //
        $label=null;
        $dept=null;
        $typeplace=null;
        $error_label=null;
        $error_dept=null;
        $error_typeplace=null;
        //
        $entityManager=$this->getDoctrine()->getManager();

        if(isset($_POST['label']) and ($label=$_POST['label'] or true)
            and ((isset($_POST['dept']) and $dept=$_POST['dept']) or true)
            and ((isset($_POST['typeplace']) and $typeplace=$_POST['typeplace']) or true)
            and !$obRemarkableplace=$entityManager->getRepository(Remarkableplace::class)->findOneBy(['label'=>$label])
            and $label!='' and $dept!='' and $typeplace!=''
            )
        {
            $entityManager=$this->getDoctrine()->getManager();
            $obRemarkableplace=new Remarkableplace;
            $obRemarkableplace->setLabel($label);
            $obRemarkableplace->setDeptCode($dept);
            $obRemarkableplace->setTypeplace($entityManager->getRepository(Typeplace::class)->findOneBy(['id'=>$typeplace]));
            //
            $entityManager->persist($obRemarkableplace);
            $entityManager->flush();

            // message de confirmation de la création
            $this->addFlash('success', "La création du nouveau \"lieu\" a bien été enregistrée...");
            //
            return $this->redirectToRoute('admin_remarkableplace_read', [
                'remarkableplaces'  => $remarkableplaceRepository->findAll(),
            ]);
        }
        elseif(isset($obRemarkableplace)){
            $this->addFlash('danger', "Un \"lieu\" avec ce libellé existe déjà...");
        }
        //
        // dd($dept);
        if(isset($label) and $label==''){$error_label=true;$label=null;}
        if(isset($dept) and $dept==''){$error_dept=true;$dept=null;}
        if(isset($typeplace) and $typeplace==''){$error_typeplace=true;$typeplace=null;}
        //
        return $this->render('remarkableplace/update.html.twig', [
            'route' => 'remarkableplace_create',
            //
            'remarkableplace'   => null,
            'label'             => $label,
            'dept_code'         => $dept,
            'typeplace_id'      => $typeplace,
            'typeplaces'        => $entityManager->getRepository(Typeplace::class)->findAll(),
            //
            'error_label'   => $error_label,
            'error_dept'   => $error_dept,
            'error_typeplace'   => $error_typeplace,
        ]);
    }

    /**
     * @Route("/remarkableplaces/list", name="remarkableplace_read", methods={"GET","POST"})
     */
    public function remarkableplaceRead(RemarkableplaceRepository $remarkableplaceRepository): Response
    {
        // test si l'utilisateur N'est PAS encore identifié...
        if(!$this->getUser() or !in_array("ROLE_ADMIN",$this->getUser()->getRoles())){
            // ... renvoi vers la page de connection
            return $this->redirectToRoute('app_login');
        }

        return $this->render('remarkableplace/read.html.twig', [
            'controller_name' => 'AdminController',
            'remarkableplaces'  => $remarkableplaceRepository->findBy(array(), array('dept_code'=>'asc','label'=>'asc','typeplace'=>'asc')),
            // 'remarkableplaces'  => $remarkableplaceRepository->findAll(),
        ]);
    }

    /**
     * @Route("/remarkableplace_{remarkableplace}/update", name="remarkableplace_update", methods={"GET","POST"})
     */
    public function remarkableplaceUpdate(Remarkableplace $remarkableplace, RemarkableplaceRepository $remarkableplaceRepository): Response
    {
        // test si l'utilisateur N'est PAS encore identifié...
        if(!$this->getUser() or !in_array("ROLE_ADMIN",$this->getUser()->getRoles())){
            // ... renvoi vers la page de connection
            return $this->redirectToRoute('app_login');
        }
        //
        if(!$obRemarkableplace=$remarkableplaceRepository->findOneBy(['id'=>$remarkableplace])){
            $obRemarkableplace=null;
        }

        //
        $label=null;
        $error_label=null;
        $error_dept=null;
        $error_typeplace=null;
        //
        $entityManager = $this->getDoctrine()->getManager();

        if(isset($_POST['label']) and ($label=$_POST['label'] or true)
            and ((isset($_POST['dept']) and $dept=$_POST['dept']) or true)
            and ((isset($_POST['typeplace']) and $typeplace=$_POST['typeplace']) or true)
            and (!$obRemarkableplacefounded=$entityManager->getRepository(Remarkableplace::class)->findOneBy(['label'=>$label])
                    or $obRemarkableplacefounded==$remarkableplace
                )
            and $label!='' and $dept!='' and $typeplace!=''
            )
        {
            $obRemarkableplace->setLabel($_POST['label']);
            $obRemarkableplace->setDeptCode($dept);
            $obRemarkableplace->setTypeplace($entityManager->getRepository(Typeplace::class)->findOneBy(['id'=>$typeplace]));

            $entityManager->persist($obRemarkableplace);
            $entityManager->flush();

            // message de confirmation de la création
            $this->addFlash('success', "La modification du \"type de lieu\" a bien été enregistrée...");
            //
            return $this->redirectToRoute('admin_remarkableplace_read', [
                // 'controller_name' => 'AdminController',
                'remarkableplaces'  => $remarkableplaceRepository->findAll(),
            ]);
        }
        elseif(isset($obRemarkableplacefounded)){
            $this->addFlash('danger', "Un \"lieu\" avec ce libellé existe déjà...");
        }
        //
        if(isset($label) and $label==''){$error_label=true;$label=null;}
        //
        return $this->render('remarkableplace/update.html.twig', [
            // 'controller_name' => 'RemarkableplaceController',
            'route' => 'remarkableplace_update',
            //
            'remarkableplace'   => $remarkableplace,
            'typeplaces'        => $entityManager->getRepository(Typeplace::class)->findAll(),
            //
            'error_label'       => $error_label,
            'error_dept'        => $error_dept,
            'error_typeplace'   => $error_typeplace,
        ]);
    }

    /**
     * @Route("/remarkableplace_{remarkableplace}/delete", name="remarkableplace_delete", methods={"GET","POST"}, requirements={"remarkableplace":"\d+"})
     */
    public function remarkableplaceDelete(Request $request, Remarkableplace $remarkableplace, RemarkableplaceRepository $remarkableplaceRepository): Response
    {
        // test si l'utilisateur N'est PAS encore identifié...
        if(!$this->getUser() or !in_array("ROLE_ADMIN",$this->getUser()->getRoles())){
            // ... renvoi vers la page de connection
            return $this->redirectToRoute('app_login');
        }

        if($this->isCsrfTokenValid('delete'.$remarkableplace->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($remarkableplace);
            $entityManager->flush();
        }

        // message de confirmation de la création
        $this->addFlash('success', "La supression du \"lieu\" a bien été effectuée...");
        //
        return $this->redirectToRoute('admin_remarkableplace_read', [
            // 'controller_name' => 'AdminController',
            'remarkableplaces'  => $remarkableplaceRepository->findAll(),
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

        //
        $label=null;
        $price=null;
        $error_label=null;
        $error_price=null;
        //
        $entityManager=$this->getDoctrine()->getManager();
        
        if(isset($_POST['label']) and ($label=$_POST['label'] or true)
            and ($price=$_POST['price'] or true)
            and !$obTypeplace=$entityManager->getRepository(Flatrate::class)->findOneBy(['label'=>$label])
            and $label!='' and $price!='' and is_numeric($price) and intval($price)!==0
            )
        {
            $entityManager=$this->getDoctrine()->getManager();
            $obTypeplace=new Flatrate;
            $obTypeplace->setLabel($_POST['label']);
            $obTypeplace->setPrice($_POST['price']);
            //
            if(isset($_POST['pickupincluded'])){
                $obTypeplace->setPickupIncluded(true);
            }
            else{
                $obTypeplace->setPickupIncluded(false);
            }
            //
            $obTypeplace->setRegionCode($_POST['region']);
            //
            $entityManager->persist($obTypeplace);
            $entityManager->flush();

            // message de confirmation de la création
            $this->addFlash('success', "La création du nouveau tarif a bien été enregistrée...");
            //
            return $this->redirectToRoute('admin_flatrate_read', [
                'flatrates'  => $flatrateRepository->findAll(),
            ]);
        }
        elseif(isset($obTypeplace)){
            $this->addFlash('danger', "Un tarif avec ce libellé existe déjà...");
        }
        //
        if(isset($label) and $label==''){$error_label=true;$label=null;}
        if(isset($price) and ($price=='' or !is_numeric($price) or intval($price)==0)){$error_price=true;$price=null;}
        //
        return $this->render('flatrate/update.html.twig', [
            'route'     => 'flatrate_create',
            //
            'flatrate'  => null,
            'label'     => $label,
            'price'     => $price,
            //
            'error_label'   => $error_label,
            'error_price'   => $error_price,
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
            'flatrates'  => $flatrateRepository->findBy(array(), array('region_code'=>'asc','price'=>'asc')),
            // 'flatrates'  => $flatrateRepository->findAll(),
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
        //
        if(!$obTypeplace=$flatrateRepository->findOneBy(['id'=>$flatrate])){
            $obTypeplace=null;
        }

        //
        $label=null;
        $price=null;
        $error_label=null;
        $error_price=null;
        //
        $entityManager = $this->getDoctrine()->getManager();

        if(isset($_POST['label']) and ($label=$_POST['label'] or true)
            and ($price=$_POST['price'] or true)
            and (!$obTypeplacefounded=$entityManager->getRepository(Flatrate::class)->findOneBy(['label'=>$label])
                    or $obTypeplacefounded==$flatrate
                )
            and $label!='' and $price!='' and is_numeric($price) and intval($price)!==0
            )
        {
            $obTypeplace->setLabel($_POST['label']);
            $obTypeplace->setPrice($_POST['price']);
            //
            if(isset($_POST['pickupincluded'])){
                $obTypeplace->setPickupIncluded(true);
            }
            else{
                $obTypeplace->setPickupIncluded(false);
            }
            //
            $obTypeplace->setRegionCode($_POST['region']);

            $entityManager->persist($obTypeplace);
            $entityManager->flush();

            // message de confirmation de la création
            $this->addFlash('success', "La modification du tarif a bien été enregistrée...");
            //
            return $this->redirectToRoute('admin_flatrate_read', [
                // 'controller_name' => 'AdminController',
                'flatrates'  => $flatrateRepository->findAll(),
            ]);
        }
        elseif(isset($obTypeplacefounded)){
            $this->addFlash('danger', "Un tarif avec ce libellé existe déjà...");
        }
        //
        if(isset($label) and $label==''){$error_label=true;$label=null;}
        if(isset($price) and ($price=='' or !is_numeric($price) or intval($price)==0)){$error_price=true;$price=null;}
        //
        return $this->render('flatrate/update.html.twig', [
            // 'controller_name' => 'FlatrateController',
            'route'     => 'flatrate_update',
            //
            'flatrate'  => $obTypeplace,
            'label'     => $label,
            'price'     => $price,
            //
            'error_label'   => $error_label,
            'error_price'   => $error_price,
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
