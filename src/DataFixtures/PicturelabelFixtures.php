<?php

namespace App\DataFixtures;

use App\Entity\Picturelabel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PicturelabelFixtures extends Fixture
    implements OrderedFixtureInterface
{
    
    const PICTURES = [
        ['label'    => "Carte pro. VMDTR - Face"],
        ['label'    => "Carte pro. VMDTR - Dos"],
        ['label'    => "Visite médicale"],
        // ....
    ];
    
    public function load(ObjectManager $manager)
    {

        ////---------------------------
        foreach (self::PICTURES as $Picture) {
            $obCategory = new Picturelabel();
            $obCategory->setLabel($Picture['label']);

            // utiliser AVANT Persist le setReference, 
            // afin de stocker en mémoire les instances des objets 
            // qui dans un second temps (chargement de Article),
            // récupèrera la donnée...
            // $this->setReference($Picture['label'],$obCategory);
            // $this->setReference($Picture['key'],$obCategory);

            $manager->persist($obCategory);
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
    public function getOrder(): int{
        return 1;
    }
}