<?php

namespace ShopBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            -> add("date", DateTimeType::class, array(
                'label' => 'Желаемое время доставки:',
                'date_widget' => 'single_text',
               'hours' => range(9,23),
                'minutes'=>range(0,45,15)
                ))
            -> add("phone", TextareaType::class, array(
                'label' => 'Телефон:',
                'data' => 'phone'))
            -> add("address", TextareaType::class, array(
                'label' => 'Адрес:',
                'data' => 'phone'))
            -> add("comment", TextareaType::class, array(
                'label' => 'Комментраий к заказу:'))
            ->add("Оформить", SubmitType::class);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver -> setDefaults([
            "data_class" => 'ShopBundle\Entity\Order'
        ]);
    }

}