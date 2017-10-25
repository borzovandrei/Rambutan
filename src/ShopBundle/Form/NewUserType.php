<?php

namespace ShopBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setMethod('GET')
            ->add("username", TextareaType::class, array(
                'label' => 'Логин:',
                'attr' => array(
                    'placeholder' => "Qwerty"
                )
            ))
            ->add("password", PasswordType::class, array(
                'label' => 'Пароль:',
            ))
            ->add("firstname", TextareaType::class, array(
                'label' => 'Фамилия:',
                'attr' => array(
                    'placeholder' => "Иванов"
                )
            ))
            ->add("lastname", TextareaType::class, array(
                'label' => 'Имя:',
                'attr' => array(
                    'placeholder' => "Иван"
                )
            ))
            ->add("email", EmailType::class, array(
            ))
            ->add("phone", TextareaType::class, array(
                'label' => 'Телефон:',
                'data'=>'+7','attr' => array(
                    'placeholder' => "+78005553535"
                )
            ))
            ->add("address", TextareaType::class, array(
                'label' => 'Адрес:',
                'attr' => array(
                    'placeholder' => "Где проживаете?"
                )
            ))
            ->add("age")
            ->add('sex',EntityType::class, array(
                'class' => 'ShopBundle\Entity\Sex',
                'choice_label' => 'name',
            ))
            ->add("Зарегестрироваться", SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => 'ShopBundle\Entity\Users'
        ]);
    }

}