<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\UX\TogglePassword\Form\TogglePasswordTypeExtension;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Email must not be empty.'
                    ]),
                    new Regex([
                        'pattern' => '^[\w\.-]+@[a-zA-Z\d\.-]+\.[a-zA-Z]{2,}$^',
                        'message' => 'Email incorrect.'
                    ]),
                    new Length([
                        'min' => 1,
                        'max' => 255,
                        'minMessage' => 'Email must contain at least one character.',
                        'maxMessage' => 'Email cannot be longer that {{ limit }}.'
                    ]),
                ]
            ])
            ->add('pseudo', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Username must not be empty.'
                    ]),
                    new Length([
                        'min' => 3,
                        'max' => 50,
                        'minMessage' => 'Your username should be at least {{ limit }} characters.',
                        'maxMessage' => 'Your username cannot be longer than {{ limit }} characters.'
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z0-9_]+$/',
                        'message' => 'Your username can only contain letters, numbers and underscores.'
                    ])
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'mapped' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => ['attr' => ['class' => 'password-field'], 'label_attr' => ['class' => 'block'],],
                'required' => true,
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Password must not be empty.',
                    ]),
                    new Length([
                        'min' => 12,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096, // max length allowed by Symfony for security reasons
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*(_|[^\w])).+$/',
                        'message' => 'Your password must contain at least one lowercase letter, one uppercase letter, one number and one special character.'
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])


            ->add('email_confirm', TextType::class, [
                'mapped' => false,
                'required' => false,
                'attr' => ['style' => 'display:none !important', 'tabindex' => '-1', 'autocomplete' => 'off'],
                'label' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'registration_form',
            'sanitize_html' => true,
        ]);
    }
}
