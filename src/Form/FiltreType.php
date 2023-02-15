<?php

namespace App\Form;

use App\Entity\Filtre;
use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FiltreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
           /* ->add('site', EntityType::class,[
                "class"=>Site::class,
                "choice_label"=>"nom"
            ])*/
            ->add('nomSortieContient' ,null, ['label'=>'Le nom de la sortie contient : '])
            ->add('DateFiltreDebut' , null,['label'=>'Entre  '])
            ->add('dateFiltreFin' ,null, ['label'=>'et  '])
            ->add('organisateur',null, ['label'=>"Sorties dont je suis l'organisateur/trice: "])
            ->add('inscrit',null, ['label'=>'Sorties auxquelles je suis inscrit/e : '])
            ->add('pasInscrit',null, ['label'=>'Sorties auxquelles je ne suis pas inscrit/e : '])
            ->add('sortiesPassees',null, ['label'=>'Sorties passées : '])

            ->add("Rechercher", SubmitType::class);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Filtre::class,
        ]);
    }
}
