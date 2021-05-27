<?php

namespace App\twig;

use App\Tools\DBTools;
// use App\Entity\Category;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class FrenchGeography extends AbstractExtension
{

    // Déclaration des extensions de functions TWIG
    public function getFunctions(){
        return [
            new TwigFunction('getregions', [$this, 'getAllRegions']),
            new TwigFunction('getregionbyslug', [$this, 'getRegionBySlug']),
            //
            new TwigFunction('getdepartments', [$this, 'getAllRegions']),
            new TwigFunction('getcitybyslug', [$this, 'getCityBySlug']),
            //
            new TwigFunction('getcities', [$this, 'getAllRegions']),
            new TwigFunction('getcitybyslug', [$this, 'getCityBySlug']),
            new TwigFunction('getcitybyzip', [$this, 'getCityByZip']),
            //
            new TwigFunction('getimages', [$this, 'getImages']),
        ];
    }

    // Déclaration des filters TWIG
    public function getFilters(){
        return [
            new TwigFilter('cast_to_array', array($this, 'castClassToArray')),
        ];
    }

    // ***************************************************************

    // public function isUsedCategory($category): bool{
    //     $obPDO = new DBTools;
    //     $obPDO->init();
    //     $articles=$obPDO->execSqlQuery("select * from article where category_id=".$category);
    //     if(count($articles)>0){
    //         return true;
    //     }
    //     else{return false;}
    // }
    // ** Les méthodes liées aux extensions de fonctions **

    //
    // [{"id":1,"code":"01","name":"Guadeloupe","slug":"guadeloupe"},
    //  ...
    // ]
    public function getAllRegions(){
        $regions = file_get_contents("../src/twig/regions.json");
        return(json_decode($regions));
    }
    public function getRegionBySlug($slug){
        foreach($this->getAllRegions() as $obRegion){
            if($slug==$obRegion->slug){
                $obRegionFounded=$obRegion;
            }
        }
        return $obRegionFounded;
    }
    public function getRegionByCode($code){
        foreach($this->getAllRegions() as $obRegion){
            if($code==$obRegion->code){
                $obRegionFounded=$obRegion;
            }
        }
        return $obRegionFounded;
    }
    //
    // [{"id":1,"region_code":"84","code":"01","name":"Ain","slug":"ain"},
    //   ...
    // ]
    public function getAllDepartments(){
        $departments = file_get_contents("../src/twig/departments.json");
        return(json_decode($departments));
    }
    public function getDepartmentBySlug($slug){
        foreach($this->getAllRegions() as $obDepartment){
            if($slug==$obDepartment->slug){
                $obDepartmentFounded=$obDepartment;
            }
        }
        return $obDepartmentFounded;
    }
    public function getDepartmentByCode($code){
        foreach($this->getAllRegions() as $obDepartment){
            if($code==$obDepartment->code){
                $obDepartmentFounded=$obDepartment;
            }
        }
        return $obDepartmentFounded;
    }
    //
    // [{"id":31390,"department_code":"78","insee_code":"78621","zip_code":"78190",
    //   "name":"Trappes","slug":"trappes","gps_lat":48.77304956521739,
    //   "gps_lng":1.9908103260869598},
    //   ....
    // ]
    public function getAllCities(){
        $cities = file_get_contents("../src/twig/cities.json");
        return(json_decode($cities));
    }
    public function getCityBySlug($slug){
        foreach($this->getAllCities() as $obCity){
            if($slug==$obCity->slug){
                $obCityFounded=$obCity;
            }
        }
        return $obCityFounded;
    }
    public function getCityByZip($zip){
        foreach($this->getAllCities() as $obCity){
            if($zip==$obCity->zip_code){
                $obCityFounded=$obCity;
            }
        }
        return $obCityFounded;
    }



    //
    public function getImages(string $article){
        $obPDO = new DBTools;
        $obPDO->init();
        $article=intval($article);
        $regions=$obPDO->execSqlQuery("select pathname from picture where article_id=".$article);
        //
        if(!isset($regions[0])){
            $regions=array(['pathname'=>'no-image.png']);
        }
        return $regions;
    }

    // *************************************************************

    // ** Les méthodes liées aux filtres **
    public function castClassToArray($stdClassObject) {
        $response = array();
        foreach ($stdClassObject as $key => $value) {
            $response[] = array($key, $value);
        }
        return $response;
    }




}

?>