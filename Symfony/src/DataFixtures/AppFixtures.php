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
        // Admin
        $user = new User();
        $user->setEmail('admin@admin.com');
        $user->setPassword('$2y$10$PYNxB4I2/0LfIwBMg9EBaeVTHY/00IrH.LxkVbWnmITqBnXawEIQm'); // admin
        $user->setFirstname('Maxime');
        $user->setLastname('LE GENTIL');
        $user->setCreatedAt(new DateTimeImmutable());
        $manager->persist($user);
        $manager->flush();

        // Customers
        $customers_data = [
            [
                'firstname' => 'Fabrice',
                'lastname' => 'LECONTE',
                'email' => 'leconte.f@fff.fr',
                'phonenumber' => '0606986350',
                'address' => '5 rue du Foubourg St Honoré 75001 PARIS'
            ],
            [
                'firstname' => 'Julie',
                'lastname' => 'FOUCHÉ',
                'email' => 'fouche.julie@gmail.com',
                'phonenumber' => '0706986350',
                'address' => '85 Bvd des mouettes 69000 LYON'
            ],
            [
                'firstname' => 'Edward',
                'lastname' => 'NORTON',
                'email' => 'edd.norton@outlook.fr',
                'phonenumber' => '0616989650',
                'address' => '55 impasse du Foubourg St Honoré 13002 MARSEILLE'
            ],
            [
                'firstname' => 'Alain',
                'lastname' => 'DELON',
                'email' => 'a.delon@gmail.com',
                'phonenumber' => '0796989550',
                'address' => '5 rue du Foubourg St Honoré 75001 PARIS'
            ],
        ];
        $customers = [];

        for ($i = 0; $i < count($customers_data); $i++) {
            $customer = new Customer();
            $customer->setFirstname($customers_data[$i]['firstname']);
            $customer->setLastname($customers_data[$i]['lastname']);
            $customer->setEmail($customers_data[$i]['email']);
            $customer->setPhonenumber($customers_data[$i]['phonenumber']);
            $customer->setAddress($customers_data[$i]['address']);
            $customer->setCreatedAt(new DateTimeImmutable());
            $manager->persist($customer);
            $customers[] = $customer;
        }
        $manager->flush();

        // Products
        $products_data = [
            [
                'name' => 'Pot de peinture blanc 20L',
                'price' => 39
            ],
            [
                'name' => 'Malette de poker 300 jetons',
                'price' => 89
            ]
        ];
        $products = [];

        for ($i = 0; $i < count($products_data); $i++) {
            $product = new Product();
            $product->setName($products_data[$i]['name']);
            $product->setPrice($products_data[$i]['price']);
            $product->setCreatedAt(new DateTimeImmutable());
            $manager->persist($product);
            $products[] = $product;
        }
        $manager->flush();

        // Orders
        /*for ($i = 0; $i < 5; $i++) {
            $order = new Order();
            $order->setCustomer($customers[0]);
            $order->setOrderNumber(rand(2000, 99999999));
            $order->setTotalAmount(rand(10, 260));
            $order->setCreatedAt(new DateTimeImmutable());
            foreach ($products as $product) {
                $order->addProduct($product); // Assuming you have a method to add products to orders
            }
            $manager->persist($order);
        }
        $manager->flush();*/
    }
}
