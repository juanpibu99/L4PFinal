<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre',TextType::class,array('required' => false ,'attr' => ['class' => 'foo']))
            ->add('foto',FileType::class,array('data_class' => null,'required' => false , 'attr' => ['class' => 'foo']))
            ->add('descripcion',TextareaType::class,array('required' => false ,'attr' => ['class' => 'foo']))
            ->add('ubicacion',TextType::class,array('required' => false ,'attr' => ['class' => 'foo']))
            ->add('save',SubmitType::class,array('label'=>'Modificar', 'attr' => ['class' => 'quitarb']))
          
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
