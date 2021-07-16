<?php

namespace App\Controller;

use App\Entity\Company;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/", name="company_")
 */
class CompanyController extends AbstractController
{

    /**
     * @Route("{company}/confirm", name="confirm", methods={"GET","POST"}, requirements={"company":"\d+"})
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

        return $this->redirectToRoute('admin_index', [
            'allcompaniesunknown'=>$entityManager->getRepository(Company::class)->findBy(['isconfirmed'=>null]),
        ]);
    }





    /**
     * @Route("/company", name="index")
     */
    // public function index(): Response
    // {
    //     return $this->render('company/index.html.twig', [
    //         'controller_name' => 'CompanyController',
    //     ]);
    // }
}
