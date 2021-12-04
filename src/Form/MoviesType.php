<?php

namespace App\Form;

use App\Entity\Actors;
use App\Entity\Categories;
use App\Entity\Movies;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class MoviesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['add']== true):

        $builder
            ->add('actors', EntityType::class, [
                "label"=>false,
                "class"=>Actors::class,
                "choice_label"=> function(Actors $actors){
                    return $actors->getFirstname().' '.$actors->getLastname();
                },
                "multiple"=>true,
                "attr"=>[
                    'class'=>'select2',
                    'data-placeholder'=>"Sélectionnez un ou des acteurs de ce film"
                ]
            ])
            ->add('categories', EntityType::class,[
                "label"=>false,
                "class"=>Categories::class,
                'choice_label'=>'name'
            ])
            ->add('title',TextType::class, [
                "required"=>false,
                "label"=>false,
                "attr"=>[
                    "placeholder"=>"Saisir le titre du film"
                ]

            ])
            ->add('director',TextType::class, [
                "required"=>false,
                "label"=>false,
                "attr"=>[
                    "placeholder"=>"Saisir le réalisateur"
                ]

            ])
            ->add('resume', TextareaType::class,[
                "required"=>false,
                "label"=>false,
            ])
            ->add('cover', FileType::class,[
                "required"=>false,
                "label"=>false,
                "constraints"=>[
                    new File([
                       'mimeTypes'=>[
                           'image/jpg',
                           'image/jpeg',
                           'image/png'
                       ],
                       'mimeTypesMessage'=>'Extensions autorisées : jpg, jpeg, png'
                    ])
                ]
            ])
            ->add('release_date', DateType::class, [
                "required"=>false,
                "label"=>false,
                'widget' => 'single_text',

            ])
            ->add('Valider', SubmitType::class)
        ;

        else:

            $builder
                ->add('actors', EntityType::class, [
                    "label"=>false,
                    "class"=>Actors::class,
                    "choice_label"=> function(Actors $actors){
                    return $actors->getFirstname().' '.$actors->getLastname();
                    },
                    "multiple"=>true,
                    "attr"=>[
                        'class'=>'select2',
                        'data-placeholder'=>"Sélectionnez un ou des acteurs de ce film"
                    ]
                ])
                ->add('categories', EntityType::class,[
                    "label"=>false,
                    "class"=>Categories::class,
                    'choice_label'=>'name'
                ])
                ->add('title',TextType::class, [
                    "required"=>false,
                    "label"=>false,
                    "attr"=>[
                        "placeholder"=>"Saisir le titre du film"
                    ]

                ])
                ->add('director',TextType::class, [
                    "required"=>false,
                    "label"=>false,
                    "attr"=>[
                        "placeholder"=>"Saisir le réalisateur"
                    ]

                ])
                ->add('resume', TextareaType::class,[
                    "required"=>false,
                    "label"=>false,
                ])
                ->add('coverUpdate', FileType::class,[
                    "required"=>false,
                    "label"=>false,
                    "constraints"=>[
                        new File([
                            'mimeTypes'=>[
                                'image/jpg',
                                'image/jpeg',
                                'image/png'
                            ],
                            'mimeTypesMessage'=>'Extensions autorisées : jpg, jpeg, png'
                        ])
                    ]
                ])
                ->add('release_date', DateType::class, [
                    "required"=>false,
                    "label"=>false,
                    'widget' => 'single_text',


                ])
                ->add('Valider', SubmitType::class)
            ;



        endif;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Movies::class,
            'add'=>false,
            'update'=>false
        ]);
    }
}
