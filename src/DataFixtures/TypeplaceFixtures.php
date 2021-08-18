<?php

namespace App\DataFixtures;

use App\Entity\Typeplace;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

class TypeplaceFixtures
    // extends Fixture implements OrderedFixtureInterface
{
    
    const Typeplace = [
        ['label'    => 'Aéroport'],
        ['label'    => 'Gare ferroviaire'],
        ['label'    => 'Gare routière'],
        ['label'    => 'Quartier d\'affaires']
        // ....
    ];
    
    public function no_load(ObjectManager $manager)
    {

        ////---------------------------
        foreach (self::Typeplace as $typeplace) {
            $obTypeplace = new Typeplace();
            $obTypeplace->setLabel($typeplace['label']);

            // utiliser AVANT Persist le setReference, 
            // afin de stocker en mémoire les instances des objets 
            // qui dans un second temps (chargement de Article),
            // récupèrera la donnée...
            $this->setReference($typeplace['label'],$obTypeplace);
            // $this->setReference($typeplace['key'],$obTypeplace);

            $manager->persist($obTypeplace);
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
    //     return 10;
    // }
}