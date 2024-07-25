<?php

namespace App\Form;

use App\Entity\Topic;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TopicType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'The title of the topic cannot be empty.'
                    ]),
                    new Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'The title of the topic must be at least {{ limit }} characters long.',
                        'maxMessage' => 'The title of the topic cannot be longer than {{ limit }} characters.'
                    ]),
                ]
            ])
            
            ->add('post', PostType::class, [
                'required' => true,
                'mapped' => false,
                'label' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'The content of the post cannot be empty.'
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Topic::class,
            'sanitize_html' => true,
        ]);
    }
}
