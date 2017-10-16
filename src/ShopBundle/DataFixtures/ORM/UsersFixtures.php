<?php
/**
 * Created by PhpStorm.
 * User: AndreiBorzov
 * Date: 10.10.17
 * Time: 20:02
 */

namespace ShopBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ShopBundle\Entity\Role;
use ShopBundle\Entity\Users;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class UsersFixtures implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        // создание роли ROLE_ADMIN
        $role = new Role();
        $role->setName('ROLE_ADMIN');
        $manager->persist($role);

        // создание пользователя
        $user = new Users();
        $user->setFirstname('John');
        $user->setLastname('Doe');
        $user->setSex('M');
        $user->setAge(new \DateTime("11-11-1990"));
        $user->setEmail('john@example.com');
        $user->setUsername('john.doe');
        $user->setSalt(md5(time()));

        // шифрует и устанавливает пароль для пользователя,
        // эти настройки совпадают с конфигурационными файлами
        $encoder = new MessageDigestPasswordEncoder('sha512', true, 10);
        $password = $encoder->encodePassword('admin', $user->getSalt());
        $user->setPassword($password);

        $user->getUserRoles()->add($role);

        $manager->persist($user);


        $manager->flush();

    }
}