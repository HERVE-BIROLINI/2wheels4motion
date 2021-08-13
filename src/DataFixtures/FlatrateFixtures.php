<?php

namespace App\DataFixtures;

use App\Entity\Flatrate;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class FlatrateFixtures
    // extends Fixture implements OrderedFixtureInterface
{

    const FLATRATES = [
        ['price'    => 30,  'label' => "prise en charge (hors forfait)",   'pickup_included' => 1],
        ['price'    => 50,  'label' => "Paris/Paris",   'pickup_included' => 1],
        ['price'    => 60,  'label' => "Paris/Petite couronne",   'pickup_included' => 1],
        ['price'    => 70,  'label' => "Paris/Orly",   'pickup_included' => 1],
        ['price'    => 90,  'label' => "Paris/Roissy CDG",   'pickup_included' => 1],
        ['price'    => 120, 'label' => "Orly/Roissy CDG",   'pickup_included' => 1],
        ['price'    => 80,  'label' => "Mise à disposition 1h (25km maxi)",   'pickup_included' => 1],
        ['price'    => 240, 'label' => "Mise à disposition 3h (75km maxi)",   'pickup_included' => 1],
        ['price'    => 2,   'label' => "par km (GPS)",   'pickup_included' => 0],
        // ....
    ];

    public function no_load(ObjectManager $manager)
    {

        ////---------------------------
        foreach (self::FLATRATES as $Flatrate) {
            $obFlatrate = new Flatrate();
            $obFlatrate->setPrice($Flatrate['price']);
            $obFlatrate->setLabel($Flatrate['label']);
            $obFlatrate->setPickupIncluded($Flatrate['pickup_included']);

            // utiliser AVANT Persist le setReference, 
            // afin de stocker en mémoire les instances des objets 
            // qui dans un second temps (chargement de Article),
            // récupèrera la donnée...
            // $this->setReference($Flatrate['label'],$obFlatrate);
            // $this->setReference($Flatrate['key'],$obFlatrate);

            $manager->persist($obFlatrate);
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
