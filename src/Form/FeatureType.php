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
        $builder
            ->add('id_game_api', HiddenType::class)
            ->add('disability', EntityType::class, [
                'class' => Disability::class,
                'attr' => [
                    'class' => 'w-full px-5 py-4 text-black/80 rounded-lg mt-2'
                ],
                'label' => 'Choose a disability category <span class="text-red-500">*</span>',
                'label_html' => true,
                'label_attr' => [
                    'class' => 'text-black/80 dark:text-white/80'
                ]
                ])
            ->add('name', TextType::class, [
                'attr' => [
                    'class' => 'w-full px-5 py-4 text-gray-700 bg-gray-200 rounded-lg mt-2',
                ],
                'label' => 'Enter a feature title <span class="text-red-500">*</span>',
                'label_html' => true,
                'label_attr' => [
                    'class' => 'text-black/80 dark:text-white/80'
                ]
            ])
            ->add('content', TextareaType::class, [
                'attr' => [
                    'class' => 'w-full px-5 py-4 text-gray-700 bg-gray-200 rounded-lg mt-2',
                ],
                'label' => 'Enter content for the feature <span class="text-red-500">*</span>',
                'label_html' => true,
                'label_attr' => [
                    'class' => 'text-black/80 dark:text-white/80'
                ]
            ])
            ->add('images', FileType::class, [
                'label' => false,
                'multiple' => true,
                'mapped' => false,
                'required' => false,
            ])
            // ->add('images', CollectionType::class, [
            //     'entry_type' => ImageType::class,
            //     'entry_options' => ['label' => false],
            //     'allow_add' => true,
            //     'by_reference' => false,
            // ])
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
            'data_class' => Feature::class,
        ]);
    }
}
