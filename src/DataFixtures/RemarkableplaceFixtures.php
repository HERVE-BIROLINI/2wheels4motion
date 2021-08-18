<?php

namespace App\DataFixtures;

use App\Entity\Remarkableplace;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RemarkableplaceFixtures
    // extends Fixture implements OrderedFixtureInterface
{

    const REMARKABLEPLACES = [
        ['typeplace' => 'Aéroport', 'label' => 'Orly (ADP)', 'dept_code' => '91'],
        ['typeplace' => 'Aéroport', 'label' => 'Roissy CDG (ADP)', 'dept_code' => '95'],
        ['typeplace' => 'Gare ferroviaire', 'label' => 'Paris Gare d\'Austerlitz', 'dept_code' => '75'],
        ['typeplace' => 'Gare ferroviaire', 'label' => 'Paris Gare de l\'Est', 'dept_code' => '75'],
        ['typeplace' => 'Gare ferroviaire', 'label' => 'Paris Gare de Lyon', 'dept_code' => '75'],
        ['typeplace' => 'Gare ferroviaire', 'label' => 'Paris Gare du Nord', 'dept_code' => '75'],
        ['typeplace' => 'Gare ferroviaire', 'label' => 'Paris Montparnasse', 'dept_code' => '75'],
        ['typeplace' => 'Gare ferroviaire', 'label' => 'Paris Gare Saint-Lazare', 'dept_code' => '75'],
        ['typeplace' => 'Gare ferroviaire', 'label' => 'Gare de Paris-Bercy', 'dept_code' => '75'],
        ['typeplace' => 'Quartier d\'affaires', 'label' => 'La Défense', 'dept_code' => '92'],
        ['typeplace' => 'Aéroport', 'label' => 'Nice Côte d\'Azur', 'dept_code' => '06'],
        ['typeplace' => 'Gare ferroviaire', 'label' => 'Nice-ville', 'dept_code' => '06'],
        ['typeplace' => 'Quartier d\'affaires', 'label' => 'Sophia Antipolis', 'dept_code' => '06'],
        ['typeplace' => 'Aéroport', 'label' => 'Marseille Provence (AMP)', 'dept_code' => '13'],
        ['typeplace' => 'Gare ferroviaire', 'label' => 'Marseille St Charles', 'dept_code' => '13'],
        ['typeplace' => 'Aéroport', 'label' => 'Lyon Saint Exupéry', 'dept_code' => '69'],
        ['typeplace' => 'Gare ferroviaire', 'label' => 'Lyon Part Dieu', 'dept_code' => '69'],
        ['typeplace' => 'Gare ferroviaire', 'label' => 'Lyon Perrache', 'dept_code' => '69'],
        ['typeplace' => 'Gare ferroviaire', 'label' => 'Lyon Saint-Paul', 'dept_code' => '69'],
        ['typeplace' => 'Aéroport', 'label' => 'Montpellier Méditerranée', 'dept_code' => '34'],
        ['typeplace' => 'Gare ferroviaire', 'label' => 'Montpellier Saint-Roch', 'dept_code' => '34'],
        ['typeplace' => 'Gare ferroviaire', 'label' => 'Montpellier Sud de France', 'dept_code' => '34'],
        // ....
    ];

    public function no_load(ObjectManager $manager)
    {

        ////---------------------------
        foreach (self::REMARKABLEPLACES as $remarkableplace) {
            $obRemarkableplace = new Remarkableplace();
            $obRemarkableplace->setLabel($remarkableplace['label']);
            $obRemarkableplace->setDeptCode($remarkableplace['dept_code']);
            //
            $obRemarkableplace->setTypeplace($this->getReference($remarkableplace['typeplace']));
            //
            $manager->persist($obRemarkableplace);
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
    //     return 11;
    // }
}
