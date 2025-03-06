<?php

namespace App\Form;

use App\Entity\User;
use Dom\Text;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', TextType::class, [
                'required' => false,
                'label' => 'email',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de saisir votre adresse Email',
                    ])
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'required' => false,
                'label' => 'J\'accepte les CGU',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions générales.',
                    ]),
                ],
            ])
            ->add('password', RepeatedType::class, [
                'required' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe ne correspondent pas',
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmez votre mot de passe'],
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner votre mot de passe',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Votre mot de passe doit avoir au minimum 8 caractères',
                        'max' => 4096,
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!#%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
                        'match' => true,
                        'message' => 'Votre mot de passe doit avoir au moins une minuscule, une majuscule, un chiffre et un caractère spécial (@$!#%*?&)'
                    ])
                ],
            ])
            ->add("firstName", TextType::class, [
                'required' => false,
                'label' => 'Prénom',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de saisir votre prénom',
                    ])
                ],
            ])
            ->add("lastName", TextType::class, [
                'required' => false,
                'label' => 'Nom',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de saisir votre nom',
                    ])
                ],
            ])
            ->add("city", TextType::class, [
                'required' => false,
                'label' => 'Ville',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de saisir votre ville',
                    ])
                ],
            ])
            ->add("phone", TextType::class, [
                'required' => false,
                'label' => 'Téléphone',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de saisir votre numéro de téléphone',
                    ]),
                    new Length([
                        'min' => 10,
                        'minMessage' => 'Ce numéro de téléphone n\'est pas au bon format',
                        // max length allowed by Symfony for security reasons
                        'max' => 10,
                    ])
                ],
            ])
            ->add("zipcode", TextType::class, [
                'required' => false,
                'label' => 'Code postal',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de saisir votre code postal',
                    ])
                ],
            ])
            ->add("address", TextType::class, [
                'required' => false,
                'label' => 'Adresse',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de saisir votre adresse',
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
