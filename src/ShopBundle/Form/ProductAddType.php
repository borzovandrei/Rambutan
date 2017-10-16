<?php

namespace ShopBundle\Form;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductAddType  extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder -> add("name")
            ->add("class")
            ->add('idClass',EntityType::class, array(
                'class' => 'ShopBundle\Entity\Sort',
                'choice_label' => 'name',
            ))
            ->add("price")
            ->add("shopPrice")
            ->add("balanse")
            ->add('measure',EntityType::class, array(
                'class' => 'ShopBundle\Entity\Measure',
                'choice_label' => 'name',
            ))
            ->add('file')
            ->add("save", SubmitType::class);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver -> setDefaults([
            "data_class" => 'ShopBundle\Entity\Products'
        ]);
    }

}