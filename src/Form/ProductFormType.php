<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reference', TextType::class, [
                'label' => 'Référence',
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'veuillez saisir une référence.'
                    ])
                ]
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un titre.'
                    ])
                ]
            ])
            ->add('color', ChoiceType::class, [
                'label' => 'Couleur',
                'choices' => [
                    'Blanc' => 'blanc',
                    'Noir' => 'noir',
                    'Gris sidéral' => 'gris sideral',
                    'Bleu' => 'bleu',
                    'Rose' => 'rose'
                ]
            ])
            ->add('size', ChoiceType::class, [
                'label' => 'Mémoire',
                'choices' => [
                    '128G' => '128G',
                    '256G' => '256G',
                    '512G' => '512G'
                ]
            ])
            ->add('gender')
            ->add('picture')
            ->add('price')
            ->add('stock')
            ->add('description', TextareaType::class, [
                'label' => 'description',
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir une description.'
                    ])
                    ],
                    'attr' => [
                        'rows' => 10
                    ]
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'title',
            ])
            /*
            ->add('category) correspond à la clé étrangère SQL category.
            Ici c'est un champs provenant d'une autre table SQL donc un champ EntityType, qui va générer dans le formulaire un menu déroulant permettant de sélectionner la catégorie
            */
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
