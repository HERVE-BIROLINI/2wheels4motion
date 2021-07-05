<?php

namespace App\Form;

use App\Entity\Picture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProfilePictureFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // BIROLINI : pas utiles
            // ->add('pathname')
            // ->add('picturelabel')
            // ->add('file', FileType::class, [
            //     'mapped' => false,
            //     'required' => true,
            //     //
            //     'label' => "Choisir un fichier image :",
            //     // pour pouvoir renommer le bouton dans .CSS
            //     'attr'  =>  ['lang' => "fr"],
            //     'help' => 'Seuls les fichiers .JPG, .JPEG et .PNG sont autorisés',
                // 'constraints' => [
                //     new File([
                //         'maxSize' => '1024k',
                //         'mimeTypes' => [
                //             'application/jpg',
                //             'application/jpeg',
                //             'application/png',
                //         ],
                //         'mimeTypesMessage' => 'Téléchargez un fichier image valide SVP',
                //     ])
                // ],
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Picture::class,
            'choices' => [
                'Standard Shipping'  => 'standard',
                'Expedited Shipping' => 'expedited',
                'Priority Shipping'  => 'priority',
            ],
        ]);
    }
}
