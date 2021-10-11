<?php

namespace App\DataFixtures;

use App\Entity\Paymentlabel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PaymentlabelFixtures
    // extends Fixture implements OrderedFixtureInterface
{
    
    const PAYMANTLABEL = [
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
        foreach (self::PAYMANTLABEL as $Paymentlabel) {
            $obPaymentlabel = new Paymentlabel();
            $obPaymentlabel->setLabel($Paymentlabel['label']);

            // utiliser AVANT Persist le setReference, 
            // afin de stocker en mémoire les instances des objets 
            // qui dans un second temps (chargement de Article),
            // récupèrera la donnée...
            // $this->setReference($Paymentlabel['label'],$obPaymentlabel);
            // $this->setReference($Paymentlabel['key'],$obPaymentlabel);

            $manager->persist($obPaymentlabel);
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
