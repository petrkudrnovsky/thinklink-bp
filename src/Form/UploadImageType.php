<?php

namespace App\Form;

use App\Form\DTO\UploadImageDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UploadImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('files', FileType::class, [
                'label' => 'Nahrajte obrázky',
                'multiple' => true,
                'mapped' => true,
                'required' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Nahrát',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UploadImageDTO::class,
        ]);
    }
}
