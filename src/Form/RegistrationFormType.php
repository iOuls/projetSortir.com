<?php

namespace App\Form;

use App\Entity\Site;
use App\Entity\User;
use ContainerE1WI7eq\getVichUploader_Form_Type_FileService;
use ContainerE1WI7eq\getVichUploader_Form_Type_ImageService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;



class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Pseudo', TextType::class, [
                'constraints' => [
                    new Regex('/^[A-Za-z]+$/')
                ]
            ])
            ->add('Prenom', TextType::class, [
                'constraints' => [
                    new Regex('/^[A-Za-z]+$/')
                ]
            ])
            ->add('Nom', TextType::class, [
                'constraints' => [
                    new Regex('/^[A-Za-z]+$/')
                ]
            ])
            ->add('Telephone')
            ->add('email')
            ->add('password', PasswordType::class)
            ->add('site', EntityType::class,
                ['class' => Site::class, 'choice_label' => 'nom']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
