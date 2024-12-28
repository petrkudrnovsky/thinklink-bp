<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UploadImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('files', FileType::class, [
                'label' => 'Nahrajte obrázky',
                'multiple' => true,
                'mapped' => false,
                'required' => true,
//                'constraints' => [
//                    new File([
//                        'mimeTypes' => [
//                            'image/jpeg',
//                            'image/png',
//                            'image/gif',
//                            'image/webp',
//                            'image/svg+xml',
//                        ],
//                        'mimeTypesMessage' => 'Please upload a valid image (jpg, png, gif, webp, svg)'
//                    ])
//                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Nahrát',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
