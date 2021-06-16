<?php

namespace App\Twig;

use App\Entity\Driver;
use App\Entity\Company;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
// use App\Repository\DriverRepository;
// use App\Repository\CompanyRepository;
// use App\Repository\PicturelabelRepository;
// use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
// use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
// use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
// use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class DriverTwig extends AbstractExtension
{

    private $entityManager;
    // private $urlGenerator;
    // private $passwordEncoder;
    // private $csrfTokenManager;

    public function __construct(EntityManagerInterface $entityManager
                                // , UrlGeneratorInterface $urlGenerator
                                // , UserPasswordEncoderInterface $passwordEncoder
                                // , CsrfTokenManagerInterface $csrfTokenManager
                                )
    {
        // $this->csrfTokenManager = $csrfTokenManager;
        // $this->urlGenerator = $urlGenerator;
        // $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
    // // private $drivers;
    // // private $companies;
    // // public function __construct(DriverRepository $drivers
    // //                             , CompanyRepository $companies
    // //                             )
    // // {
    // //     $this->drivers=$drivers;
    // //     $this->companies=$companies;
    }

    // Déclaration des extensions de functions TWIG
    public function getFunctions(){
        return [
            // fonctions de manipulations de l'objet Driver
            new TwigFunction('getdriverbyid', [$this, 'getDriverById']),
            // fonctions de manipulations de l'objet Company
            new TwigFunction('getallcompanies', [$this, 'getAllCompanies']),
            new TwigFunction('getcompanybyid', [$this, 'getCompanyById']),
            new TwigFunction('getknowncompanies', [$this, 'getKnownCompanies']),
        ];
    }

    // Déclaration des filters TWIG
    // public function getFilters(){
    //     return [
    //         new TwigFilter('cast_to_array', array($this, 'castClassToArray')),
    //     ];
    // }

    // *************************************************************

    // ** Les méthodes liées aux extensions de fonctions **
    // fonctions de manipulations de l'objet Driver
    public function getDriverById($id){
        return $this->entityManager->getRepository(Driver::class)->findOneBy(['id'=>$id]);
    }
    // fonctions de manipulations de l'objet Company
    public function getAllCompanies(){
        return $this->entityManager->getRepository(Company::class)->findAll();
    }
    public function getCompanyById($id){
        return $this->entityManager->getRepository(Company::class)->findOneBy(['id'=>$id]);
    }
    public function getKnownCompanies(){
        return $this->entityManager->getRepository(Company::class)->findOneBy(['isconfirmed'=>true]);
    }

    // *************************************************************

    // ** Les méthodes liées aux filtres **
    //
    // public function castClassToArray($stdClassObject) {
    //     $response = array();
    //     foreach ($stdClassObject as $key => $value) {
    //         $response[] = array($key, $value);
    //     }
    //     return $response;
    // }




}

?>