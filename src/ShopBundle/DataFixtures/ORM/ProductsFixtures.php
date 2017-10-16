<?php

namespace ShopBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ShopBundle\Entity\Products;
use ShopBundle\Entity\Sort;

class ProductsFixtures extends AbstractFixture implements FixtureInterface
{

    public function load(ObjectManager $manager)
    {
        $product = new Products();
        $product->setName('Grenny Smith');
        $product->setClass('Фрукты');
        $product->setPrice(42.41);
        $product->setShopPrice(63.2);
        $manager->persist($product);

        $product = new Products();
        $product->setName('Спелая вишня');
        $product->setClass('Фрукты');
        $product->setPrice(14.4);
        $product->setShopPrice(50.34);
        $manager->persist($product);

        $product = new Products();
        $product->setName('Клубника');
        $product->setClass('Фрукты');
        $product->setPrice(42.11);
        $product->setShopPrice(120.22);
        $manager->persist($product);

        $sort = new Sort();
        $sort->setName('Овощи, фрукты, зелень, грибы');
        $sort->setAbout('Продукты');
        $manager->persist($sort);

        $sort = new Sort();
        $sort->setName('Молочные продукты, сыр, яйца');
        $sort->setAbout('Продукты');
        $manager->persist($sort);

        $sort = new Sort();
        $sort->setName('Мясо и птица');
        $sort->setAbout('Продукты');
        $manager->persist($sort);

        $sort = new Sort();
        $sort->setName('Рыба, икра, морепродукты');
        $sort->setAbout('Продукты');
        $manager->persist($sort);

        $sort = new Sort();
        $sort->setName('Колбасы, сосиски, деликатесы');
        $sort->setAbout('Продукты');
        $manager->persist($sort);
        $sort = new Sort();

        $sort->setName('Крупы, макароны, бакалея');
        $sort->setAbout('Продукты');
        $manager->persist($sort);

        $sort = new Sort();
        $sort->setName('Консервация, соусы, приправы');
        $sort->setAbout('Продукты');
        $manager->persist($sort);

        $sort = new Sort();
        $sort->setName('Напитки, чай, кофе');
        $sort->setAbout('Продукты');
        $manager->persist($sort);

        $sort = new Sort();
        $sort->setName('Хлеб, кондитерские изделия');
        $sort->setAbout('Продукты');
        $manager->persist($sort);

        $sort = new Sort();
        $sort->setName('Заморозка и готовые блюда');
        $sort->setAbout('Продукты');
        $manager->persist($sort);
        $sort = new Sort();

        $sort->setName('Алкогольные напитки и пиво');
        $sort->setAbout('Продукты');
        $manager->persist($sort);

        $sort = new Sort();
        $sort->setName('Для детей и мам');
        $sort->setAbout('Продукты');
        $manager->persist($sort);

        $sort = new Sort();
        $sort->setName('Чистота и порядок');
        $sort->setAbout('Продукты');
        $manager->persist($sort);


        $manager->flush();

    }

}