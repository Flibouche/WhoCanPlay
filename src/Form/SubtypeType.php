<?php

namespace App\Form;

use App\Entity\Disability;
use App\Entity\Game;
use App\Entity\Subtype;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubtypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('id_game_api', TextType::class)
            ->add('Disability', EntityType::class, [
                'class' => Disability::class,
                'choice_label' => 'name',
                ])
            ->add('name')
            ->add('content', TextareaType::class, [
                // 'attr' => ['class' => 'tinymce'],
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
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'bg-red-500 px-3 py-1'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Subtype::class,
        ]);
    }
}
