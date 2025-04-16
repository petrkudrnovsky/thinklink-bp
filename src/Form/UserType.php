<?php

namespace App\Form;

use App\Entity\User;
use App\Form\DTO\UserCreateFormData;
use App\Form\DTO\UserEditFormData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'E-mail',
                'required' => true,
            ])
            ->add('name', TextType::class, [
                'label' => 'Jméno',
                'required' => true,
            ]);

            if($options['show_admin']) {
                $builder->add('isAdmin', CheckboxType::class, [
                    'required' => false,
                    'label' => 'Administrátor',
                ]);
            }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserEditFormData::class,
            'show_admin' => false,
        ]);
    }
}
