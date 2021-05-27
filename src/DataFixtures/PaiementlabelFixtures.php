<?php

namespace App\DataFixtures;

use App\Entity\Paiementlabel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PaiementlabelFixtures
    // extends Fixture implements OrderedFixtureInterface
{
    
    const PAIEMANTLABEL = [
        ['label'    => "Espèces"],
        ['label'    => "CB"],
        ['label'    => "Chèque"],
        ['label'    => "Paypal"],
        ['label'    => "Autre..."],
        // ....
    ];


    public function no_load(ObjectManager $manager)
    {

        ////---------------------------
        foreach (self::PAIEMANTLABEL as $Paiementlabel) {
            $obPaiementlabel = new Paiementlabel();
            $obPaiementlabel->setLabel($Paiementlabel['label']);

            // utiliser AVANT Persist le setReference, 
            // afin de stocker en mémoire les instances des objets 
            // qui dans un second temps (chargement de Article),
            // récupèrera la donnée...
            // $this->setReference($Paiementlabel['label'],$obPaiementlabel);
            // $this->setReference($Paiementlabel['key'],$obPaiementlabel);

            $manager->persist($obPaiementlabel);
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
