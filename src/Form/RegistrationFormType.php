<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // 
            ->add('firstname',TextType::class,[
                'label'         =>  "Votre prénom :",
                'attr'          =>  ['placeholder'=>"ex: John"],
                'required'      =>  true,
                'constraints'   =>  [
                    new NotBlank(['message'=>"Le prénom est obligatoire"]),
                    new Regex([
                        'pattern'   => "@^[a-zA-Z \-]+@i",
                        'message'   => "Ne doit contenir que des caractères alphabétiques, un espace ou un '-'",
                    ]),
                ],
            ])
            // 
            ->add('lastname',TextType::class,[
                'label'         =>  "Votre nom :",
                'attr'          =>  ['placeholder'=>"ex: Doe"],
                'required'      =>  true,
                'constraints'   =>  [
                    new NotBlank(['message'=>"Le nom est obligatoire"]),
                    new Regex([
                        'pattern'   => "@^[a-zA-Z \-]+@i",
                        'message'   => "Ne doit contenir que des caractères alphabétiques, un espace ou un '-'",
                    ]),
                ],
            ])
            //
            ->add('email',EmailType::class,[
                'label' =>  "Votre courriel :",
                'attr'  =>  ['placeholder'=>"ex: mon@email.fr"],
                // ICI surcharge des sécurités côté FRONT pour :
                //  1. guider l'utilisateur (UX)
                //  2. limiter les aller/retours avec le serveur
                'required'      =>  true,
                'constraints'   =>  [
                    new NotBlank(['message' => "L'adresse eMail est obligatoire"]),
                    new Email   (['message' => "L'adresse eMail n'est pas valide"]),
                    // new Unique  (['message' =>  "Un compte utilisant cet email existe déjà"]),
                    new Regex([
                        'pattern' => "@^[a-zA-Z]{1}+[\da-zA-Z\-\_\.]+\@{1}[\da-zA-Z\-\_\.]+\.{1}+[a-zA-Z]{2}+@i",
                        'message' => "Ne doit contenir que des caractères alphanumériques, un '@' et un '.' quelque chose",
                    ])
                ],
            ])
            //
            ->add('phone',TextType::class,[
                'label'       =>  "Votre numéro de téléphone :",
                'attr'        =>  ['placeholder'=>"ex: 0123456789"],
                'required'    =>  true,
                'constraints' =>  [
                    new NotBlank(['message' =>  "Le numéro de téléphone obligatoire"]),
                    new Regex([
                        'pattern' => "@^0+[\d]{9}@",
                        'message' => "Doit comporter 10 chiffres et commencer par un 0",
                    ]),
                    new Length([
                        'min'        => 10,
                        'max'        => 10,
                        'minMessage' => 'Votre numéro de téléphone doit comporter {{ limit }} chiffres',
                        'maxMessage' => 'Votre numéro de téléphone doit comporter {{ limit }} chiffres',
                    ]),
                ],
            ])
            // ->add('plainPassword', PasswordType::class, [
            //     // instead of being set onto the object directly,
            //     // this is read and encoded in the controller
            //     'mapped' => false,
            //     //'attr' => ['autocomplete' => 'new-password'],
            //     'constraints' => [
            //         new NotBlank([
            //             'message' => 'Please enter a password',
            //         ]),
            //         new Length([
            //             'min' => 6,
            //             'minMessage' => 'Your password should be at least {{ limit }} characters',
            //             // max length allowed by Symfony for security reasons
            //             'max' => 4096,
            //         ]),
            //     ],
            // ])
            // 
            ->add('plainPassword', RepeatedType::class, [
                'type'      => PasswordType::class,
                'mapped'    => false,
                'required'  =>  true,
                //
                'first_options' => [
                    'label' => "Définir un mot de passe :",
                    'attr'  => ['placeholder'   => "Saisir un mot de passe",],
                    'constraints'   => [
                        new NotBlank(['message' => 'Entrez un mot de passe SVP',]),
                        new Length([
                            'min'           => 6,
                            'minMessage'    => 'Votre mot de passe doit comporter au moins {{ limit }} caractères',
                            // max length allowed by Symfony for security reasons
                            'max'           => 4096,
                        ]),
                        new Regex([
                            'pattern'   => "@^[\da-zA-Z\@\$\£]+@i",
                            'message'   => "Doit contenir des caractères alphanumériques",
                        ]),
                    ],
                ],
                'second_options' => [
                    // 'label' => "Confirmez le mot de passe :",
                    'label' => false,
                    'attr'  => ['placeholder'   => "Confirmer le mot de passe",],
                ],
                // message si les champs ne correspondent pas
                'invalid_message'   => "Les mots de passe ne sont pas identiques..."
            ])
            //
            ->add('hasagreetoterms', CheckboxType::class, [
                // 'label' => "Accepter les conditions d'utilisation de l'Annuaire",
                'label' => "En cochant cette case, je reconnais avoir pris connaissance des CONDITIONS GÉNÉRALES D'UTILISATION' et les accepter.",
                // 'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => "Vous devez accepter les termes de l'Annuaire.",
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
