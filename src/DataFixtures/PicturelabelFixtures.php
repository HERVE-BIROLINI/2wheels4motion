<?php

namespace App\DataFixtures;

use App\Entity\Picturelabel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PicturelabelFixtures 
    // extends Fixture implements OrderedFixtureInterface
{
    
    const PICTURELABELS = [
        ['label'    => "Avatar"],
        ['label'    => "Carte pro. VMDTR - Face"],
        ['label'    => "Carte pro. VMDTR - Dos"],
        ['label'    => "Visite médicale"],
        ['label'    => "Autre"],
        // ....
    ];
    
    public function no_load(ObjectManager $manager)
    {

        ////---------------------------
        foreach (self::PICTURELABELS as $Picturelabel) {
            $obPicturelabel = new Picturelabel();
            $obPicturelabel->setLabel($Picturelabel['label']);

            // utiliser AVANT Persist le setReference, 
            // afin de stocker en mémoire les instances des objets 
            // qui dans un second temps (chargement de Article),
            // récupèrera la donnée...
            // $this->setReference($Picturelabel['label'],$obPicturelabel);
            // $this->setReference($Picturelabel['key'],$obPicturelabel);

            $manager->persist($obPicturelabel);
        }
        //
        $manager->flush();
        ////----------------------------

        // $product = new Product();
        // $manager->persist($product);
        // $manager->flush();
    }



    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    // public function getOrder(): int{
    //     return 1;
    // }
}