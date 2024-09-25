<?php

namespace App\Form;

use App\Entity\Game;
use App\Entity\User;
use App\Entity\Feature;
use App\Entity\Disability;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class FeatureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'];

        $builder
            ->add('id_game_api', HiddenType::class)
            ->add('disability', EntityType::class, [
                'class' => Disability::class])
            ->add('name', TextType::class)
            ->add('content', TextareaType::class)
            ->add('images', FileType::class, [
                
                'required' => !$isEdit,
                'label' => false,
                'multiple' => true,
                'mapped' => false,
            ])
            // ->add('state')
            // ->add('Game', EntityType::class, [
            //     'class' => Game::class,
            //     'choice_label' => 'id',
            // ])
            // ->add('User', EntityType::class, [
            //     'class' => User::class,
            //     'choice_label' => 'id',
            // ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Feature::class,
            'sanitize_html' => true,
            'is_edit' => false,
        ]);
    }
}
