<?php

namespace App\Form;

use App\Entity\Article;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CreateArticleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'constraints' => [
                    New NotBlank([
                        'message' => 'Merci de renseigner un titre.'
                    ]),
                    New Length([
                        'max' => 150,
                        'maxMessage' => 'Votre titre ne peut pas faire plus de 150 caractères.'
                    ])
                ]
            ])

            ->add('content', CKEditorType::class, [
                'label' => 'Article:',
                'attr' =>[
                    'class' => 'd-none',
                ],
                'purify_html' => true,
                'constraints' => [
                    New NotBlank([
                        'message' => 'Merci de remplir l\'article'
                    ]),
                    New Length([
                        'max' => 50000,
                        'maxMessage' => 'L\'article ne peut pas faire plus de 50000 caractères.'
                    ])

                ],
                'attr' => [
                    'rows' => '15',
                ],
            ])


            ->add('save', SubmitType::class, [ // Ajout d'un champ de type bouton de validation
                'label' => 'Créer article',// Texte du bouton
                'attr' => [
                    'class' => 'btn btn-outline-primary w-100',
                ],
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
