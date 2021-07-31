<?php

namespace App\Form;

use App\Entity\Claim;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ClaimFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ->add('claim_datetime')
            ->add('journey_date',DateType::class,[
                'widget' => 'single_text',
                'label' => "Jour de la course :",
                'attr'  => ['id' => "journey_date"],
                //
                'years' => range(date('Y'), date('Y')+1),
                // liste des contraintes...
                'constraints'   =>  [
                    new NotBlank(['message' => "L'heure d'arrivée est indispensable"]),
                    // new GreaterThan(['value'  => new DateTime('2008-08-03 14:52:10'),
                    //                 'message' => "Vous devez préciser une date 'correcte'..."
                    //             ]),
                ],
            ])
            ->add('arrivalat_time',TimeType::class,[
                'label' => "Heure d'arrivée :",
                'attr'  => ['id' => "arrivalat_time"],
                // 'data' => new DateTime('now'),
                // pseudo placeholder pris en charge par Symfony pour les Select !
                // 'placeholder'   =>  ['year'=>"Année",
                //                     'month'=>"Mois",
                //                     'day'=>"Jour",
                // ],
                // liste des options...
                'constraints'   =>  [
                    new NotBlank(['message'=>"La date de la course est obligatoire"]),
                ],
            ])
            ->add('from_road',TextType::class,[
                'label' => "N° et Voie :",
                'attr'  => ['placeholder'=>"ex: 666 route de l'enfer",],
                'constraints'   =>  [
                    new NotBlank(['message'=>"Le numéro et la voie 'de prise en charge' sont indispensables"]),
                ],
            ])
            ->add('from_city',TextType::class,[
                'label' => "Ville :",
                'attr'  => ['placeholder'=>"ex: paris",
                            'class'=>"inputcity",
                            ],
                'constraints'=>[
                    new NotBlank(['message'=>"La ville 'de prise en charge' est indispensable"]),
                ],
            ])
            ->add('from_zip',TextType::class,[
                'label' => "Code postal :",
                'required'=> false,
                'attr'  => ['placeholder'=>"ex: 75001 (indiquez la ville)",],
                'constraints'   =>  [
                    //new NotBlank(['message'=>"Le CP est déterminé par l'indication de la ville..."]),
                    new Regex([
                        'pattern'   => "@^[\d]{5}+@i",
                        'message'   => "Doit contenir 5 chiffres",
                    ]),
                ],
            ])
            ->add('to_road',TextType::class,[
                'label' => "N° et Voie :",
                'attr'  => ['placeholder'=>"ex: 7 avenue du paradis",],
                'constraints'   =>  [
                    new NotBlank(['message'=>"Le numéro et la voie 'destination' sont indispensables"]),
                ],
            ])
            ->add('to_city',TextType::class,[
                'label' => "Ville :",
                'attr'  => ['placeholder'=>"ex: paris",
                            'class'=>"inputcity",
                            ],
                'constraints'=>[
                    new NotBlank(['message'=>"La ville 'destination' est indispensable"]),
                ],
            ])
            ->add('to_zip',TextType::class,[
                'label' => "Code postal :",
                'required'=> false,
                'attr'  => ['placeholder'=>"ex: 75001 (indiquez la ville)",],
                'constraints'   =>  [
                    //new NotBlank(['message'=>"Le CP est déterminé par l'indication de la ville..."]),
                    new Regex([
                        'pattern'   => "@^[\d]{5}+@i",
                        'message'   => "Doit contenir 5 chiffres",
                    ]),
                ],
            ])
            ->add('comments',TextareaType::class,[
                'label' => "Commentaires :",
                'required'=> false,
                'attr'  => ['placeholder'=>"ex: précisez un bagage, une 'étape' sur le trajet...",],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Claim::class,
        ]);
    }
}
