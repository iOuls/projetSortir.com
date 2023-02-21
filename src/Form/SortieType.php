<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function Sodium\add;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, ['label' => 'Nom de la sortie '])
            ->add('dateHeureDebut', DateTimeType::class, ['html5' => true,
                'widget' => 'single_text',
                'label' => 'Date et heure de la sortie ',
                'attr' => [
                    'min' => (new \DateTime())->format('Y-m-d H:i'),
                    'format' => 'dd/MM/yyyy H:i'
                ]])
            ->add('dateLimitInscription', DateType::class, ['html5' => true,
                'widget' => 'single_text',
                'label' => 'Date limite d\'inscription',
                'attr' => [
                    'min' => (new \DateTime())->format('Y-m-d'),
                    'format' => 'dd/MM/yyyy'
                ]])
            ->add('nbInscriptionsMax', null, ['label' => 'Nombre de place '])
            ->add('duree', null, ['label' => 'Durée '])
            ->add('InfosSortie', null, ['label' => 'Description et infos '])
            ->add('site', EntityType::class, [
                "class" => Site::class,
                "choice_label" => "nom"
            ])
            ->add('lieu', EntityType::class, [
                "class" => Lieu::class,
                "choice_label" => "nom"
            ])
            ->add('Enregistrer', SubmitType::class, ['label' => 'Enregistrer'])
            ->add('Publier', SubmitType::class, ['label' => 'Publier']);


        //  ->add("Enregistrer", SubmitType::class)
        // ->add("Publier", SubmitType::class);


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
