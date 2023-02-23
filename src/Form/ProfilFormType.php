<?php

namespace App\Form;

use App\Entity\User;
use PharIo\Manifest\Email;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Validator\Constraints\File;


class ProfilFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Pseudo', TextType::class, [
                'constraints' => [
                    new Regex('/^[A-Za-z]+$/')
                ]
            ])
            ->add('email')//, null, ['attr' => ['disabled' => true]])
            ->add('password', null, ['label' => 'Password :'])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les champs mot de passe doivent correspondre.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmez le mot de passe'],
            ])
//
            ->add('nom', TextType::class, [
                'constraints' => [
                    new Regex('/^[A-Za-z]+$/')
                ]
            ])
            ->add('prenom', TextType::class, [
                'constraints' => [
                    new Regex('/^[A-Za-z]+$/')
                ]
            ])
            ->add('telephone')
            ->add('imageFile', FileType::class, [
                'attr' => [
                    'accept' => "image/*"
                ],
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',

                    ])
                ],

            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer les modifications',
                'attr' => [
                    'class' => 'btn mt-3 cardButton col-12',
                    'id' => 'loginBtn'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
