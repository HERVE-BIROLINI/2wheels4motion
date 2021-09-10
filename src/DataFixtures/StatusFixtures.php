<?php

namespace App\DataFixtures;

use App\Entity\Status;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

class StatusFixtures
    // extends Fixture implements OrderedFixtureInterface
{
    
    const STATUS = [
        ['value'    => 0,   'label' => "A lire"],
        ['value'    => 1,   'label' => "Lu(e)"],
        ['value'    => 2,   'label' => "Devis envoyé"],
        ['value'    => 4,   'label' => "Archivé(e)"]
        // ....
    ];
    
    public function no_load(ObjectManager $manager)
    {

        ////---------------------------
        foreach (self::STATUS as $status) {
            $obStatus = new Status();
            $obStatus->setValue($status['value']);
            $obStatus->setLabel($status['label']);
            //
            $manager->persist($obStatus);
        }
        //
        $manager->flush();
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