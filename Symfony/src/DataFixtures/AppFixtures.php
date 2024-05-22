<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        // Admin
        $user = new User();
        $user->setEmail('admin@admin.com');
        $user->setPassword('$2y$10$PYNxB4I2/0LfIwBMg9EBaeVTHY/00IrH.LxkVbWnmITqBnXawEIQm');
        $user->setFirstname('Maxime');
        $user->setLastname('LE GENTIL');
        $user->setCreatedAt(new DateTimeImmutable());
        //$user->setUpdatedAt();
        $manager->persist($user);

        // Client
        $customer = new Customer();
        $customer->setFirstname('Fabrice');
        $customer->setLastname('LECONTE');
        $customer->setEmail('fabrice@fff.fr');
        $customer->setPhonenumber('0696586356');
        $customer->setAddress('5 rue ddddddd');
        $customer->setCreatedAt(new DateTimeImmutable());
        //$customer->setUpdatedAt();
        $manager->persist($customer);

        // Product
        $product = new Product();
        $product->setName('BIE');
        $product->setPrice('63');
        $product->setCreatedAt(new DateTimeImmutable());
        //$customer->setUpdatedAt();
        $manager->persist($product);

        // Order
        $order = new Order();
        $order->setCustomer($customer);
        $order->setOderNumber(rand(2000, 99999999));
        $order->setTotalAmmount(rand(10, 95));
        $order->setCreatedAt(new DateTimeImmutable());
        //$customer->setUpdatedAt();
        $manager->persist($order);

        $product->addOrder($order);
        $manager->persist($product);


        $manager->flush();
    }
}