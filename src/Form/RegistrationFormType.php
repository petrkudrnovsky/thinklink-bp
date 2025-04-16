<?php

namespace App\Form;

use App\Entity\User;
use App\Form\DTO\UserCreateFormData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
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

            // Registration form can be used for both user and admin registration. Admin registration requires another admin to create the user.
            if($options['show_admin']) {
                $builder->add('isAdmin', CheckboxType::class, [
                    'required' => false,
                    'label' => 'Administrátor',
                ]);
            }

            $builder->add('plainPassword', PasswordType::class, [
                'attr' => ['autocomplete' => 'new-password'],
                'label' => 'Heslo',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserCreateFormData::class,
            'show_admin' => false,
        ]);
    }
}
