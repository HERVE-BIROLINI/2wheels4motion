<?php

namespace App\Twig;

// use App\Entity\Picture;
// use App\Entity\User;
// use App\Tools\DBTools;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
// use App\Entity\Picturelabel;
// use App\Repository\PicturelabelRepository;
use Doctrine\ORM\EntityManagerInterface;
// use Doctrine\Persistence\ManagerRegistry;

class FlatrateTwig extends AbstractExtension
{

    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }




    // Déclaration des extensions de functions TWIG
    public function getFunctions(){
        return [
            // new TwigFunction('TWIGfunction_name', [$this, 'PHPfunction_name']),
        ];
    }

    // Déclaration des filters TWIG
    public function getFilters(){
        return [
            new TwigFilter('gethoursinlabel', array($this, 'getHoursInLabel')),
        ];
    }

    // *************************************************************

    // ** Les méthodes liées aux extensions de fonctions **
    //

    // *************************************************************

    // ** Les méthodes liées aux filtres **
    //
    public function getHoursInLabel($flatrate){
        if(is_object($flatrate)){
            $sHours=$flatrate->getLabel();
        }elseif(is_string($flatrate)){
            $sHours=$flatrate;
        }
        if(isset($sHours) and strpos($sHours,'h ')){
            return strrev(explode(' ',strrev(explode('h ',$sHours)[0]))[0]);
        }else{
            return null;
        }
    }




}

?>