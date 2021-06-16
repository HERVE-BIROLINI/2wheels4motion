<?php

namespace App\Twig;

use App\Entity\Picture;
use App\Entity\User;
use App\Tools\DBTools;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Entity\Picturelabel;
use App\Repository\PicturelabelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class PictureTwig extends AbstractExtension
{

    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }




    // Déclaration des extensions de functions TWIG
    public function getFunctions(){
        return [
            // new TwigFunction('getimages', [$this, 'getImages']),
            new TwigFunction('getuserportrait', [$this, 'getPictureOfUserProfile']),
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

    // *************************************************************

    // ** Les méthodes liées aux extensions de fonctions **
    //
    public function getPictureOfUserProfile($user_id){
        $obPictureLabel_Portrait=$this->entityManager->getRepository(Picturelabel::class)->findOneBy(['label'=>'Avatar']);
        return $this->entityManager->getRepository(Picture::class)
            ->findOneBy(['picturelabel'=>$obPictureLabel_Portrait,'user'=>$user_id]);
    }
    // public function getAllPicturelabels(){
    //     $obPDO = new DBTools;
    //     $obPDO->init();
    //     $picturelabels=$obPDO->execSqlQuery("select * from picturelabel");
    //     //
    //     // if(!isset($picturelabels[0])){
    //     //     $picturelabels=array(['pathname'=>'no-image.png']);
    //     // }
    //     return $picturelabels;
    // }
    //
    // public function getPicturelabelByLabel(string $label
    // // ,PicturelabelRepository $picturelabelRepository
    // ){
    //     foreach($this->getAllPicturelabels() as $obPicturelabel){
    //         if(strtoupper($obPicturelabel['label'])===strtoupper($label)){
    //             $id=$obPicturelabel['id'];
    //         }
    //     }
    //     if($id){
    //         $picturelabelRepository=new PicturelabelRepository(ManagerRegistry::class,Picturelabel::class);
    //         // $obPicturelabel=new Picturelabel;            
    //         $picturelabelRepository = $picturelabelRepository->getDoctrine()->getRepository(Picturelabel::class);
    //         $obPicturelabel = $picturelabelRepository->find($id);
    //         //
    //         return $obPicturelabel;
    //     }
    // }
    // //
    // public function getPicturelabelIdByLabel(string $label){
    //     foreach($this->getAllPicturelabels() as $obPicturelabel){
    //         if(strtoupper($obPicturelabel['label'])===strtoupper($label)){
    //             $id=$obPicturelabel['id'];
    //         }
    //     }
    //     return $id;
    // }

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