<?php

namespace App\Form;

use App\Entity\Disability;
use App\Entity\Game;
use App\Entity\Subtype;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubtypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id_game_api')
            ->add('name')
            ->add('content')
            ->add('state')
            ->add('Disability', EntityType::class, [
                'class' => Disability::class,
                'choice_label' => 'id',
            ])
            ->add('Game', EntityType::class, [
                'class' => Game::class,
                'choice_label' => 'id',
            ])
            ->add('User', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
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
