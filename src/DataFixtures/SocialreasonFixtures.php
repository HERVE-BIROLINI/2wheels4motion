<?php

namespace App\DataFixtures;

use App\Entity\Socialreason;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SocialreasonFixtures extends Fixture
    implements OrderedFixtureInterface
{
    const REASONS = [
        ['label'    => "SARL"],
        ['label'    => "EURL"],
        ['label'    => "SA"],
        ['label'    => "SAS"],
        ['label'    => "SNC"],
        ['label'    => "EI"],
        ['label'    => "EIRL"],
        // ....
    ];
    public function load(ObjectManager $manager)
    {
        
        ////---------------------------
        foreach (self::REASONS as $reason) {
            $obCategory = new Socialreason();
            $obCategory->setLabel($reason['label']);

            // utiliser AVANT Persist le setReference, 
            // afin de stocker en mémoire les instances des objets 
            // qui dans un second temps (chargement de Article),
            // récupèrera la donnée...
            // $this->setReference($reason['label'],$obCategory);
            // $this->setReference($reason['key'],$obCategory);

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