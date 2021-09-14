<?php

namespace App\Twig;

// use App\Tools\DBTools;

use App\Entity\Claim;
use App\Entity\Remarkableplace;
// NORMALEMENT UTILE POUR INJECTER AUTOMATIQUEMENT UN ARGUMENT AU CONSTRUCTEUR
// QUI VIENDRAIT .ENV (VIA SERVICES.YAML)
use Psr\Log\LoggerInterface;
//////////////////////////////////////////////////////////////////////////////
use Twig\Extension\AbstractExtension;
// use Twig\TwigFilter;
use Twig\TwigFunction;
use Doctrine\ORM\EntityManagerInterface;

class FrenchGeographyTwig extends AbstractExtension
{
    // private $rootJSON="build/json/";
    private $rootJSON="../src/twig/";
    //
    private $entityManager;
    //
    static public $GoogleAPI_key='AIzaSyAmIEoTvCXk8eoyG2mtVVhL_9x25xCNX9k';
    // private $logger;
    // private $api_gmaps_key;

    public function __construct(EntityManagerInterface $entityManager
        // , LoggerInterface $logger, string $api_gmaps_key
    ){
        $this->entityManager = $entityManager;
        // $this->logger = $logger;
        // $this->api_gmaps_key=$api_gmaps_key;
    }


    // Déclaration des extensions de functions TWIG
    public function getFunctions(){
        return [
            new TwigFunction('getregions', [$this, 'getAllRegions']),
            new TwigFunction('JSgetregions', [$this, 'getAllRegions_4Javascript']),
            // new TwigFunction('getregionbyslug', [$this, 'getRegionBySlug']),
            new TwigFunction('getregionbycode', [$this, 'getRegionByCode']),
            new TwigFunction('getregionbydeptcode', [$this, 'getRegionByDeptCode']),
            new TwigFunction('getregionbyzip', [$this, 'getRegionByZip']),
            new TwigFunction('getregions4claim', [$this, 'getRegions4Claim']),
            //
            new TwigFunction('getdepartments', [$this, 'getAllDepartments']),
            new TwigFunction('JSgetdpts', [$this, 'getAllDepartments_4Javascript']),
            // new TwigFunction('getdepartmentbyslug', [$this, 'getDepartmentBySlug']),
            new TwigFunction('getdepartmentbycode', [$this, 'getDepartmentByCode']),
            //
            new TwigFunction('getcities', [$this, 'getAllCities']),
            new TwigFunction('JSgetcities', [$this, 'getAllCities_4Javascript']),
            // new TwigFunction('getcitybyslug', [$this, 'getCityBySlug']),
            new TwigFunction('getcitybyzip', [$this, 'getCityByZip']),
            new TwigFunction('getzipbycity', [$this, 'getZipByCity']),
            //
            // new TwigFunction('getimages', [$this, 'getImages']),
            //
            new TwigFunction('getremarkableplaces', [$this, 'getRemarkableplaces']),
            //
            new TwigFunction('mapsdistancematrix', [$this, 'mapsDistancematrix']),
        ];
    }

    // Déclaration des filters TWIG
    public function getFilters(){
        return [
            // new TwigFilter('cast_to_array', array($this, 'castClassToArray')),
            // new TwigFilter('cast_to_string', array($this, 'castClassToString')),
            // new TwigFilter('json_decode', array($this, 'jsonDecode')),
        ];
    }

    // ***************************************************************

    /*
        //
        // [{"id":1,"code":"01","name":"Guadeloupe","slug":"guadeloupe"},
        //  ...
        // ]
    */
    public function getAllRegions(){
        $regions=json_decode(file_get_contents($this->rootJSON."regions.json"));
        $columns=array_column($regions, "slug");
        array_multisort($columns, SORT_LOCALE_STRING, SORT_ASC, $regions);
        //
        return $regions;
    }
    public function getAllRegions_4Javascript(){
        $sRegions='';
        foreach($this->getAllRegions() as $obRegion){
            $sRegions.='$'.$obRegion->code.'#'.$obRegion->name.'';
        }
        return substr($sRegions,1);
    }
    /*
        // public function getRegionBySlug($slug){
        //     foreach($this->getAllRegions() as $obRegion){
        //         if($slug==$obRegion->slug){
        //             $obRegionFounded=$obRegion;
        //         }
        //     }
        //     return $obRegionFounded;
        // }
    */
    public function getRegionByCode($code){
        foreach($this->getAllRegions() as $obRegion){
            if($code==$obRegion->code){
                $obRegionFounded=$obRegion;
            }
        }
        return $obRegionFounded;
    }
    public function getRegionByDeptCode($department_code){
        $region_code=$this->getDepartmentByCode($department_code)->region_code;
        foreach($this->getAllRegions() as $obRegion){
            if($region_code==$obRegion->code){
                $obRegionFounded=$obRegion;
            }
        }
        return $obRegionFounded;
    }
    public function getRegionByZip($zip){
        $obRegionFounded=null;
        if($obCity=$this->getCityByZip($zip)){
            $obDpt=$this->getDepartmentByCode($obCity->department_code);
            $obRegionFounded=$this->getRegionByCode($obDpt->region_code);
        }
        return $obRegionFounded;
    }
    public function getRegions4Claim(Claim $claim){
        $remarkableplace_from=$claim->getRemarkableplaceFrom();
        $remarkableplace_to=$claim->getRemarkableplaceTo();
        $arZip=[];
        // ... une adresse particulière a été choisi comme lieu de Prise en charge
        if($remarkableplace_from){
            $oRemarkable=$this->entityManager->getRepository(Remarkableplace::class)->findOneBy(['id'=>$remarkableplace_from]);
            $arZip[]=$oRemarkable->getDeptCode()."000";
        }elseif($claim->getFromZip()){
            $arZip[]=$claim->getFromZip();
        }
        // ... une adresse particulière a été choisi comme lieu de Destination
        if($remarkableplace_to){
            $oRemarkable=$this->entityManager->getRepository(Remarkableplace::class)->findOneBy(['id'=>$remarkableplace_to]);
            $arZip[]=$oRemarkable->getDeptCode()."000";
        }elseif($claim->getToZip()){
            $arZip[]=$claim->getToZip();
        }
        // crée la liste des régions concernées par les Zip
        // celle(s) concernant l'(es) adresse(s) particulière(s) choisie(s)
        $arRegions=[];
        foreach($arZip as $zip){
            $obRegion=$this->getRegionByZip($zip);
            //
            if(!in_array($obRegion,$arRegions)){
                array_push($arRegions,$obRegion);
            }
        }
        return $arRegions;
    }
    /*
        //
        // [{"id":1,"region_code":"84","code":"01","name":"Ain","slug":"ain"},
        //   ...
        // ]
    */
    public function getAllDepartments(){
        $departments=json_decode(file_get_contents($this->rootJSON."departments.json"));
        $columns=array_column($departments, "slug");
        array_multisort($columns, SORT_LOCALE_STRING, SORT_ASC, $departments);
        return $departments;
    }
    public function getAllDepartments_4Javascript(){
        $sDepartments='';
        foreach($this->getAllDepartments() as $obDpt){
            $sDepartments.='$'.$obDpt->region_code.'/'.$obDpt->code.'#'.$obDpt->name.'';
        }
        return substr($sDepartments,1);
    }
    /*
        // public function getDepartmentBySlug($slug){
        //     foreach($this->getAllRegions() as $obDepartment){
        //         if($slug==$obDepartment->slug){
        //             $obDepartmentFounded=$obDepartment;
        //         }
        //     }
        //     return $obDepartmentFounded;
        // }
    */
    public function getDepartmentByCode($code){
        if($code and $code!='' and $code!='all'){
            $obDepartmentFounded=null;
            foreach($this->getAllDepartments() as $obDepartment){
                if($code===$obDepartment->code){
                    $obDepartmentFounded=$obDepartment;
                }
            }
            return $obDepartmentFounded;
        }
        else{return null;}
    }
    /*
        //
        // [{"id":31390,"department_code":"78","insee_code":"78621","zip_code":"78190",
        //   "name":"Trappes","slug":"trappes","gps_lat":48.77304956521739,
        //   "gps_lng":1.9908103260869598},
        //   ....
        // ]
    */
    public function getAllCities(){
        $cities=json_decode(file_get_contents($this->rootJSON."cities.json"));
        $columns=array_column($cities, "slug");
        array_multisort($columns, SORT_LOCALE_STRING, SORT_ASC, $cities);
        //
        return $cities;
        // $cities = file_get_contents($this->rootJSON."cities.json");
        // return(json_decode($cities));
    }
    public function getAllCities_4Javascript(){
        $sCities='';
        foreach($this->getAllCities() as $obCity){
            $sCities.='$'.$obCity->zip_code.$obCity->name.'';
        //     $sCities.='${"zip_code":"'.$obCity->zip_code.'","name":"'.$obCity->name.'"}';
        }
        return substr($sCities,1);
    }
    /*
        // public function getCityBySlug($slug){
        //     foreach($this->getAllCities() as $obCity){
        //         if($slug==$obCity->slug){
        //             $obCityFounded=$obCity;
        //         }
        //     }
        //     return $obCityFounded;
        // }
    */
    public function getCityByZip($zip){
        if($zip!=null){
            if($zip=='75000'){$zip='75001';}
            $obCityFounded=null;
            foreach($this->getAllCities() as $obCity){
                if($zip==$obCity->zip_code){
                    $obCityFounded=$obCity;
                }
            }
            if(!is_null($obCityFounded)){
                return $obCityFounded;
            }
        }
    }
    public function getZipByCity($city){
        $obZipFounded=null;
        foreach($this->getAllCities() as $obCity){
            if(strtolower($city)==strtolower($obCity->name)){
                $obZipFounded=$obCity;
            }
        }
        if(!is_null($obZipFounded)){
            return $obZipFounded;
        }
    }

    public function getRemarkableplaces(){
        // $obPDO = new DBTools;
        // $obPDO->init();
        // $articles=$obPDO->execSqlQuery("select * from article where category_id=".$category);
        // if(count($articles)>0){
        //     return true;
        // }
        // else{return false;}
        return $this->entityManager->getRepository(Remarkableplace::class)->findBy([],['dept_code'=>'asc']);
    }

    public function mapsDistancematrix($addressFrom, $addressTo){
        // formatage des adresses pour requête API Google
        $addressFrom_URL = str_replace(" ", "+", $addressFrom); //adresse de départ
        $addressTo_URL = str_replace(" ", "+", $addressTo); //adresse d'arrivée
        // dd($addressFrom_URL);
        // dd($addressTo_URL);

        // ... pour calculer Distance et Temps
        $calculDist_url='https://maps.googleapis.com/maps/api/distancematrix/json?origins='.$addressFrom_URL.'&destinations='.$addressTo_URL.'&mode=driving&key='.FrenchGeographyTwig::$GoogleAPI_key;
        // dd($calculDist_url);

        $googleApi_Distancematrix=file_get_contents($calculDist_url);
        // dd($googleApi_Distancematrix);
        $googleApi_Distancematrix=json_decode($googleApi_Distancematrix);
        // dd($googleApi_Distancematrix);
        $googleApi_Distancematrix=$googleApi_Distancematrix->rows[0]->elements[0];
        // Si trajectoire "trouvée"...
        if($googleApi_Distancematrix->status=="OK"){
            // ... rempli les éléments graphiques
            // $driver_racedistance=$googleApi_Distancematrix->distance;
            // $driver_racetime=$googleApi_Distancematrix->duration;
            return $googleApi_Distancematrix;
        }
    }

    // *************************************************************

    /*
        // // ** Les méthodes liées aux filtres **
        // public function castClassToArray($stdClassObject) {
        //     $response = array();
        //     foreach ($stdClassObject as $key => $value) {
        //         $response[] = array($key, $value);
        //     }
        //     return $response;
        // }
        // public function castClassToString($stdClassObject) {
        //     $response = array();
        //     foreach ($stdClassObject as $key => $value) {
        //         $response[] = array($key, $value);
        //     }
        //     return $response;
        // }
        // public function jsonDecode($str) {
        //     return json_decode($str);
        // }
    */


}


?>