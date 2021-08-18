<?php

namespace App\DataFixtures;

use App\Entity\Socialreason;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SocialreasonFixtures
    // extends Fixture implements OrderedFixtureInterface
{
    const REASONS = [
        ['label'    => "EI",    'tva'   =>  ['0']],
        ['label'    => "EIRL",  'tva'   =>  ['0']],
        ['label'    => "EURL",  'tva'   =>  ['10','20']],
        ['label'    => "SA",    'tva'   =>  ['10','20']],
        ['label'    => "SARL",  'tva'   =>  ['10','20']],
        ['label'    => "SAS",   'tva'   =>  ['10','20']],
        ['label'    => "SNC",   'tva'   =>  ['10','20']]
        // ....
    ];
    
    public function no_load(ObjectManager $manager)
    {
        
        ////---------------------------
        foreach (self::REASONS as $reason) {
            $obReason = new Socialreason();
            $obReason->setLabel($reason['label']);

            // utiliser AVANT Persist le setReference, 
            // afin de stocker en mémoire les instances des objets 
            // qui dans un second temps (chargement de Article),
            // récupèrera la donnée...
            foreach($reason['tva'] as $tva){
                $obReason->addTva($this->getReference($tva));
            }
    

            $manager->persist($obReason);
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
    //     return 21;
    // }
}