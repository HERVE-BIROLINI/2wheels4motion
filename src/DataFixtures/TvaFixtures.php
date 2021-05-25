<?php

namespace App\DataFixtures;

use App\Entity\Tva;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

class TvaFixtures extends Fixture
    implements OrderedFixtureInterface
{
    
    const TVA = [
        ['value'    => 0,   'comment' => "Non soumis à la TVA, selon Art, 293B du Code Général des Impôts."],
        ['value'    => 10,  'comment' => "Taux normal sur forfait, ou si destination déterminée à l'avance."],
        ['value'    => 20,  'comment' => "Taux normal."]
        // ....
    ];
    
    public function load(ObjectManager $manager)
    {

        ////---------------------------
        foreach (self::TVA as $tva) {
            $obCategory = new Tva();
            $obCategory->setValue($tva['value']);
            $obCategory->setComment($tva['comment']);

            // utiliser AVANT Persist le setReference, 
            // afin de stocker en mémoire les instances des objets 
            // qui dans un second temps (chargement de Article),
            // récupèrera la donnée...
            // $this->setReference($tva['label'],$obCategory);
            // $this->setReference($tva['key'],$obCategory);

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