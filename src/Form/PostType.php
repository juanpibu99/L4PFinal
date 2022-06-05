<?php

namespace App\Form;

use App\Entity\Juego;
use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'idJuego',
                EntityType::class,
                [
                    'class' => Juego::class,
                    'choice_label' => 'nombre',
                    'choice_name' => 'id',
                    'expanded' => false,
                    'multiple' => false,
                    'label'=>'Juego'
                ],
      

            )
            ->add('contenido',TextareaType::class,array('attr' => ['class' => 'foo']))
            ->add('foto',FileType::class,array('required' => false , 'attr' => ['class' => 'foo']))
            ->add('save',SubmitType::class,array('label'=>'Insertar','attr' => ['class' => 'quitarb']))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
