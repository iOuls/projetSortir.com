<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function Sodium\add;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null , ['label'=>'Nom de la sortie : '])
            ->add('dateHeureDebut', null , ['label'=>'Date et heure de la sortie : '])
            ->add('dateLimitInscription', null , ['label'=>"Date limite d'inscription : "])
            ->add('nbInscriptionsMax', null , ['label'=>'Nombre de place : '])
            ->add('duree', null , ['label'=>'Durée : '])
            ->add('InfosSortie', null , ['label'=>'Description et infos : '])
            ->add('lieu', EntityType::class,[
                "class"=>Lieu::class,
                "choice_label"=>"nom"
            ])



            ->add("Enregistrer", SubmitType::class);


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
