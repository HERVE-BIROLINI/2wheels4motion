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
        ['price' => 2,  'label' => "par km (GPS)",                       'pickup_included' => 0,'region_code' => null],
        ['price' => 30, 'label' => "prise en charge (hors forfait)",     'pickup_included' => 1,'region_code' => null],
        ['price' => 80, 'label' => "Mise à disposition 1h (25km maxi)",  'pickup_included' => 1,'region_code' => null],
        ['price' => 240,'label' => "Mise à disposition 3h (75km maxi)",  'pickup_included' => 1,'region_code' => null],
        ['price' => 400,'label' => "Mise à disposition 5h (200km maxi)", 'pickup_included' => 1,'region_code' => null],
        ['price' => 750,'label' => "Mise à disposition 10h (400km maxi)",'pickup_included' => 1,'region_code' => null],
        ['price' => 50, 'label' => "Paris <=> Paris (intra-muros)",      'pickup_included' => 1,'region_code' => '11'],
        ['price' => 60, 'label' => "Paris <=> Petite couronne",          'pickup_included' => 1,'region_code' => '11'],
        ['price' => 70, 'label' => "Paris <=> Orly",                     'pickup_included' => 1,'region_code' => '11'],
        ['price' => 90, 'label' => "Paris <=> Roissy CDG",               'pickup_included' => 1,'region_code' => '11'],
        ['price' => 120,'label' => "Orly <=> Roissy CDG",                'pickup_included' => 1,'region_code' => '11'],
        ['price' => 65, 'label' => "Lyon <=> Saint Exupéry",             'pickup_included' => 1,'region_code' => '84'],
        ['price' => 35, 'label' => "Lyon <=> Lyon (intra-muros - Gares)",'pickup_included' => 1,'region_code' => '84'],
        ['price' => 70, 'label' => "Lyon <=> Lyon agglomérations",       'pickup_included' => 1,'region_code' => '84'],
        ['price' => 60, 'label' => "Marseille <=> Marseille Provence (AMP)", 'pickup_included' => 1,'region_code' => '93'],
        ['price' => 35, 'label' => "Marseille <=> Marseille Gare St Charles",'pickup_included' => 1,'region_code' => '93'],
        ['price' => 45, 'label' => "Nice <=> Nice (intra-muros)",            'pickup_included' => 1,'region_code' => '93'],
        ['price' => 35, 'label' => "Montpellier <=> Montpellier Méditerranée",  'pickup_included' => 1,'region_code' => '76'],
        ['price' => 45, 'label' => "Montpellier <=> Montpellier agglomérations",'pickup_included' => 1,'region_code' => '76'],
        ['price' => 35, 'label' => "Montpellier <=> Montpellier (intra-muros)", 'pickup_included' => 1,'region_code' => '76'],
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
            //
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
