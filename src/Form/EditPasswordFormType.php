<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class EditPasswordFormType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->security->getUser();

        if ($user instanceof User && !$user->isGoogleUser()) {
            $builder
                ->add('oldPassword', PasswordType::class, [
                    'label' => 'Old Password',
                    'mapped' => false,
                    'attr' => [
                        'autocomplete' => 'current-password',
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please enter your current password',
                        ]),
                        new UserPassword([
                            'message' => 'Invalid current password',
                        ]),
                    ],
                ])
                ->add('plainPassword', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'options' => [
                        'attr' => [
                            'autocomplete' => 'new-password',
                        ],
                    ],
                    'first_options' => [
                        'label' => 'New Password',
                        'constraints' => [
                            new NotBlank([
                                'message' => 'Password must not be empty.',
                            ]),
                            new Length([
                                'min' => 12,
                                'minMessage' => 'Your password should be at least {{ limit }} characters.',
                                'max' => 4096,
                                'maxMessage' => 'Your password cannot be longer than {{ limit }} characters.',
                            ]),
                            new Regex([
                                'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*(_|[^\w])).+$/',
                                'message' => 'Your password must contain at least one lowercase letter, one uppercase letter, one number, and one special character.',
                            ]),
                        ],
                    ],
                    'second_options' => [
                        'label' => 'Confirm Password',
                    ],
                    'invalid_message' => 'The password fields must match.',
                    'mapped' => false,
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
