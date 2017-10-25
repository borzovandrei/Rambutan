<?php

namespace ShopBundle\Form;

use ShopBundle\Entity\Order;
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
            ->add("firstname", TextareaType::class, array(
                'label' => 'Фамилия:',
                'data' => $options['arg1'],))
            ->add("lastname", TextareaType::class, array(
                'label' => 'Имя:',
                'data' => $options['arg2'],))
            -> add("date", DateTimeType::class, array(
                'label' => 'Желаемое время доставки:',
                'date_widget' => 'single_text',
                'hours' => range(9,23),
                'minutes'=>range(0,45,15),
                'data' => new \DateTime(),
                ))
            -> add("phone", TextareaType::class, array(
                'label' => 'Телефон:',
                'data' => $options['arg3'],
                ))
            -> add("address", TextareaType::class, array(
                'label' => 'Адрес:',
                'data' => $options['arg4']))
            -> add("comment", TextareaType::class, array(
                'label' => 'Комментраий к заказу:'))
            ->add("Оформить", SubmitType::class);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver -> setDefaults([
            "data_class" => Order::class,
            'arg1' => null,
            'arg2' => null,
            'arg3' => null,
            'arg4' => null,
        ]);
    }

}