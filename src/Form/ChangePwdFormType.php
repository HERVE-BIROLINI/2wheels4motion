<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ChangePwdFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type'      => PasswordType::class,
                'mapped'    => false,
                'required'  =>  true,
                //
                'first_options' => [
                    'label' => false,// 'label' => "Mot de passe",
                    'attr'  => ['placeholder'   => "Entrez un nouveau mot de passe",],
                    'constraints'   => [
                        new NotBlank(['message' => 'Please enter a password',]),
                        new Length([
                            'min'           => 6,
                            'minMessage'    => 'Votre mot de passe doit comporter au moins {{ limit }} caractères',
                            // max length allowed by Symfony for security reasons
                            'max'           => 4096,
                        ]),
                        new Regex([
                            'pattern'   => "@^[a-zA-Z0-9\@\$\£]+@i",
                            'message'   => "Doit contenir des caractères alphanumériques",
                        ])
                    ],
                ],
                'second_options' => [
                    // 'label' => "Confirmez le mot de passe :",
                    'label' => false,
                    'attr'  => ['placeholder'   => "Confirmez ce nouveau mot de passe",],
                ],
                // message si les champs ne correspondent pas
                'invalid_message'   => "Les mots de passe ne sont pas identiques..."
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
