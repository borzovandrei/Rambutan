<?php

namespace ShopBundle\Form;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder -> add("name", TextType::class, array(
                'label' => 'Название:',
                'empty_data' => 'John Doe',
                'attr' => array(
                    'placeholder' => "Яблоко"
                )
             ))
            ->add('idClass',EntityType::class, array(
                'label' => 'Тип:',
                'class' => 'ShopBundle\Entity\Sort',
                'choice_label' => 'name',
            ))
            ->add("price", TextType::class, array(
                'label' => 'Цена у поставщика:',
                'attr' => array(
                    'placeholder' => "99"
                )
            ))
            ->add("shopPrice", TextType::class, array(
                'label' => 'Цена на продажу:',
                'attr' => array(
                    'placeholder' => "999"
                )
            ))
            ->add("balanse", TextType::class, array(
                'label' => 'Сейчас на складе:',
                'attr' => array(
                    'placeholder' => "10"
                )
            ))
            ->add('measure',EntityType::class, array(
                'label' => 'Измерение:',
                'class' => 'ShopBundle\Entity\Measure',
                'choice_label' => 'name',
            ))
            ->add('file'
                , FileType::class, array(
                'label' => 'Изображение:',
            )
            )
            ->add("Добавить", SubmitType::class, array(
                'attr' => array(
                    'class' => "btn btn-success"
                )
            ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver -> setDefaults([
            "data_class" => 'ShopBundle\Entity\Products'
        ]);
    }

}