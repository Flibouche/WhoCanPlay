<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'required' => true,
                'label' => false,
                'attr' => [
                    'class' => 'w-full resize-none px-5 py-4 text-black/80 rounded-lg mt-2 bg-neutral-200 dark:bg-zinc-700 border border-gray-300 text-black dark:text-white',
                    'placeholder' => 'Enter a message content...',
                ],
            ])

            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'w-full my-2 text-white dark:text-black bg-indigo-800 hover:bg-indigo-600 focus:ring-4 focus:outline-none focus:ring-indigo-800 dark:bg-indigo-200 dark:hover:bg-indigo-400 dark:focus:ring-indigo-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
            'sanitize_html' => true,
        ]);
    }
}
