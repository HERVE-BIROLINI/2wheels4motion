<?php

namespace App\Twig;

use App\Entity\Driver;
use App\Entity\Company;
use App\Entity\Socialreason;
use App\Entity\Tva;
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
    // (... utilisées essentiellement dans le Template/Profile/Driver...)
    public function getFunctions(){
        return [
            // fonctions de manipulations de l'objet Driver
            new TwigFunction('getdriverbyid', [$this, 'getDriverById']),
            new TwigFunction('getdriversbyregionorzip', [$this, 'getDriversByRegionOrZip']),
            new TwigFunction('getdriversbycompany', [$this, 'getDriversByCompany']),
            // fonctions de manipulations de l'objet Company
            new TwigFunction('getallcompanies', [$this, 'getAllCompanies']),
            new TwigFunction('getcompanybyid', [$this, 'getCompanyById']),
            new TwigFunction('getcompaniesbyregionorzip', [$this, 'getCompaniesByRegionOrZip']),
            new TwigFunction('getknowncompanies', [$this, 'getKnownCompanies']),
            //
            new TwigFunction('getsocialreasons', [$this, 'getSocialReasons']),
            //
            new TwigFunction('gettvarates', [$this, 'getTvaRates']),
            //
            new TwigFunction('getassocsocialreasontva', [$this, 'getAssocSocialreasonTva']),
        ];
    }

    // Déclaration des filters TWIG
    public function getFilters(){
        return [
            // new TwigFilter('cast_to_array', array($this, 'castClassToArray')),
        ];
    }

    // *************************************************************

    // ** Les méthodes liées aux extensions de fonctions **
    // fonctions de manipulations de l'objet Driver
    public function getDriverById($id){
        return $this->entityManager->getRepository(Driver::class)->findOneBy(['id'=>$id]);
    }
    public function getDriversByRegionOrZip($obRegion){
        $obFGTwig = new FrenchGeographyTwig($this->entityManager);
        if(gettype($obRegion)=='string' and is_numeric($obRegion) and strlen($obRegion)==5){
            $obRegion=$obFGTwig->getRegionByZip($obRegion);
        }
        // doit passer par l'adresse des entreprises T3P pour trouver les pilotes...
        $arCompanies=[];
        foreach($this->getCompaniesByRegionOrZip($obRegion) as $obCompany){
            if (!in_array($obCompany,$arCompanies)) {
                array_push($arCompanies,$obCompany);
            }
        }
        // extrait les pilotes associées aux entreprises T3P localisées
        return $this->entityManager->getRepository(Driver::class)->findBy(['company'=>$arCompanies]);
    }
    public function getDriversByCompany($id){
        return $this->entityManager->getRepository(Driver::class)->findBy(['company'=>$id]);
    }
    // fonctions de manipulations de l'objet Company
    public function getAllCompanies(){
        return $this->entityManager->getRepository(Company::class)->findAll();
    }
    public function getCompanyById($id){
        return $this->entityManager->getRepository(Company::class)->findOneBy(['id'=>$id]);
    }
    public function getCompaniesByRegionOrZip($obRegion){
        $obFGTwig = new FrenchGeographyTwig($this->entityManager);
        if(gettype($obRegion)=='string' and is_numeric($obRegion) and strlen($obRegion)==5){
            $obRegion=$obFGTwig->getRegionByZip($obRegion);
        }
        //
        $arCompanies=[];
        foreach($this->getAllCompanies() as $obCompany){
            // dd($obCompany);
            $obCompanyRegion=$obFGTwig->getRegionByZip($obCompany->getZip());
            if($obCompanyRegion==$obRegion){
                array_push($arCompanies,$obCompany);
            }
        }
        return $arCompanies;
    }
    public function getKnownCompanies(){
        return $this->entityManager->getRepository(Company::class)->findBy(['isconfirmed'=>true]);
    }
    //
    public function getSocialReasons(){
        return $this->entityManager->getRepository(Socialreason::class)->findBy(array(), array('label'=>'asc'));
    }
    //
    public function getTvaRates(){
        return $this->entityManager->getRepository(Tva::class)->findBy(array(),array('value'=>'asc'));
    }
    //
    public function getAssocSocialreasonTva(){
        $socialreasons=$this->getSocialReasons();
        $echo=[];
        // boucle sur le nombre d'enregistrements de la table
        foreach($socialreasons as $socialreason){
            foreach($socialreason->getTva() as $tva){
                // dd($tva);
                $echo[]=array('socialreason_label'=>$socialreason->getLabel(),
                                'tva_value'=>$tva->getValue(),
                                'tva_comment'=>$tva->getComment(),
                                'socialreason_id'=>$socialreason->getId(),
                                'tva_id'=>$tva->getId()
                );
            }
        }
        return $echo;
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