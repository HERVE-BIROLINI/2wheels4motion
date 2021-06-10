<?php

namespace App\Twig;

use App\Tools\DBTools;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Repository\DriverRepository;
use App\Repository\CompanyRepository;
use App\Repository\PicturelabelRepository;
use Doctrine\Persistence\ManagerRegistry;

class DriverTwig extends AbstractExtension
{

    private $drivers;
    private $companies;
    public function __construct(DriverRepository $drivers
                                , CompanyRepository $companies
                                )
    {
        $this->drivers=$drivers;
        $this->companies=$companies;
    }

    // Déclaration des extensions de functions TWIG
    public function getFunctions(){
        return [
            // new TwigFunction('getallcompanies', [$this, 'getAllCompanies']),
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
    //
    public function getAllCompanies(){
        return $this->companies->findAll();
    }

    // *************************************************************

    // ** Les méthodes liées aux filtres **
    //
    public function castClassToArray($stdClassObject) {
        $response = array();
        foreach ($stdClassObject as $key => $value) {
            $response[] = array($key, $value);
        }
        return $response;
    }




}

?>