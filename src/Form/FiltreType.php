<?php

namespace App\Form;

use App\Entity\Filtre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FiltreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomSortieContient')
            ->add('DateFiltreDebut')
            ->add('dateFiltreFin')
            ->add('organisateur')
            ->add('inscrit')
            ->add('PasInscrit')
            ->add('boolean')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Filtre::class,
        ]);
    }
}
