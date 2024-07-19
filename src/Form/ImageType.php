<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Image;
use App\Entity\Feature;
use App\Entity\Disability;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('url', TextType::class, [
                'label' => 'Image URL',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('title', TextType::class, [
                'label' => 'Title',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('altText', TextType::class, [
                'label' => 'Alt Text',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            // ->add('submissionDate', null, [
            //     'widget' => 'single_text',
            // ])
            // ->add('updatedAt', null, [
            //     'widget' => 'single_text',
            // ])
            // ->add('slug')
            // ->add('disability', EntityType::class, [
            //     'class' => Disability::class,
            //     'choice_label' => 'id',
            // ])
            // ->add('feature', EntityType::class, [
            //     'class' => Feature::class,
            //     'choice_label' => 'id',
            // ])
            // ->add('user', EntityType::class, [
            //     'class' => User::class,
            //     'choice_label' => 'id',
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
        ]);
    }
}
