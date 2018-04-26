<?php

namespace ShopBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class EmileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, array(
            'label' => 'Ваше имя:',
            'attr' => array(
                'placeholder' => "Иванов Иван"
            )));
        $builder->add('email', EmailType::class, array(
            'label' => 'Ваша почта:',
            'attr' => array(
                'placeholder' => "qwerty@example.ru"
            )));
        $builder->add('subject', TextType::class, array(
            'label' => 'Тема:',
            'attr' => array(
                'placeholder' => "Суть обращения"
            )));
        $builder->add('body', TextareaType::class, array(
            'label' => 'Сообщение:',
            'attr' => array(
                'placeholder' => "Пожалуйста, напишите более подрбно (не менее 50 символов)."
            )));
    }

    public function configureOptions(OptionsResolver $resolver)
    {

    }

    public function getBlockPrefix()
    {
        return 'contact';
    }
}