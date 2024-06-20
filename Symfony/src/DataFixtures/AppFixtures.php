<?php

namespace App\DataFixtures;

use App\Entity\Address;
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
        // Utilisateur
        $user = new User();
        $user->setEmail('maxime.legentil17@gmail.com');
        $user->setPassword('$2y$10$PYNxB4I2/0LfIwBMg9EBaeVTHY/00IrH.LxkVbWnmITqBnXawEIQm'); // admin
        $user->setFirstname('Maxime');
        $user->setLastname('LE GENTIL');
        $user->setPhonenumber('069863523');
        $user->setSociety('Hiboo CRM');
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
                'address' => '5 rue du Foubourg St Honoré, 75001 PARIS'
            ],
            [
                'firstname' => 'Julie',
                'lastname' => 'FOUCHÉ',
                'email' => 'fouche.julie@gmail.com',
                'phonenumber' => '0706986350',
                'address' => '85 Bvd des mouettes, 69000 LYON'
            ],
            [
                'firstname' => 'Edward',
                'lastname' => 'NORTON',
                'email' => 'edd.norton@outlook.fr',
                'phonenumber' => '0616989650',
                'address' => '55 impasse du Foubourg St Honoré, 13002 MARSEILLE'
            ],
            [
                'firstname' => 'Alain',
                'lastname' => 'DELON',
                'email' => 'a.delon@gmail.com',
                'phonenumber' => '0796989550',
                'address' => '5 rue du Foubourg St Honoré, 75001 PARIS'
            ],
            // Additional customers
            [
                'firstname' => 'Sophie',
                'lastname' => 'MARCEAU',
                'email' => 's.marceau@famous.fr',
                'phonenumber' => '0654879625',
                'address' => '14 avenue des Champs-Élysées, 75008 PARIS'
            ],
            [
                'firstname' => 'Jean',
                'lastname' => 'DUJARDIN',
                'email' => 'jean.dujardin@mail.fr',
                'phonenumber' => '0632145876',
                'address' => '18 rue de Rivoli, 75004 PARIS'
            ],
            [
                'firstname' => 'Marion',
                'lastname' => 'COTILLARD',
                'email' => 'm.cotillard@yahoo.fr',
                'phonenumber' => '0611223344',
                'address' => '22 rue Oberkampf, 75011 PARIS'
            ],
            [
                'firstname' => 'Gérard',
                'lastname' => 'DEPARDIEU',
                'email' => 'gerard.depardieu@gmail.com',
                'phonenumber' => '0799123456',
                'address' => '33 boulevard Haussmann, 75009 PARIS'
            ],
            [
                'firstname' => 'Isabelle',
                'lastname' => 'HUPPERT',
                'email' => 'isabelle.huppert@cinema.fr',
                'phonenumber' => '0709876543',
                'address' => '44 rue de la Paix, 75002 PARIS'
            ],
            [
                'firstname' => 'Vincent',
                'lastname' => 'CASSEL',
                'email' => 'vincent.cassel@mail.com',
                'phonenumber' => '0612457896',
                'address' => '56 avenue Montaigne, 75008 PARIS'
            ],
            [
                'firstname' => 'Laetitia',
                'lastname' => 'CASTA',
                'email' => 'laetitia.casta@model.com',
                'phonenumber' => '0625897412',
                'address' => '67 rue de Passy, 75016 PARIS'
            ],
            [
                'firstname' => 'Mathieu',
                'lastname' => 'KASSOVITZ',
                'email' => 'mathieu.kassovitz@direct.com',
                'phonenumber' => '0654789123',
                'address' => '78 rue Saint-Honoré, 75001 PARIS'
            ],
            [
                'firstname' => 'Audrey',
                'lastname' => 'TAUTOU',
                'email' => 'audrey.tautou@actress.com',
                'phonenumber' => '0698741235',
                'address' => '89 boulevard Saint-Michel, 75005 PARIS'
            ],
            [
                'firstname' => 'Gaspard',
                'lastname' => 'ULLIEL',
                'email' => 'gaspard.ulliel@cinema.fr',
                'phonenumber' => '0601987654',
                'address' => '101 rue de Rennes, 75006 PARIS'
            ],
            [
                'firstname' => 'Catherine',
                'lastname' => 'DENEUVE',
                'email' => 'catherine.deneuve@france.com',
                'phonenumber' => '0712345678',
                'address' => '111 rue de la Pompe, 75016 PARIS'
            ],
        ];
        $customers = [];

        $address_data = [
            "country" => "France",
            "city" => "Marseille",
            "zipcode" => "13001"
        ];

        foreach ($customers_data as $customer_data) {
            $customer = new Customer();
            $customer->setFirstname($customer_data['firstname']);
            $customer->setLastname($customer_data['lastname']);
            $customer->setEmail($customer_data['email']);
            $customer->setPhonenumber($customer_data['phonenumber']);
            //$customer->setAddress($customer_data['address']);
            $customer->setCreatedAt(new DateTimeImmutable());
            $manager->persist($customer);
            $manager->flush();
            

            // Addresse :
            $address = new Address();
            $address->setCountry($address_data["country"]);
            $address->setCity($address_data["city"]);
            $address->setZipcode($address_data["zipcode"]);
            $address->setCustomer($customer);

            $manager->persist($address);
            $manager->flush();

            $customers[] = $customer;
        }


        /*$address = new Address();
        $address->setCountry($address_data["country"]);
        $address->setCity($address_data["city"]);
        $address->setZipcode($address_data["zipcode"]);

        foreach ($customers as $key => $value) {
            $address->setCustomer($value);
            $manager->persist($address);
            $manager->flush();
        }*/


        // Products
        $products_data = [
            ['name' => 'Pot de peinture blanc 20L', 'price' => 39],
            ['name' => 'Malette de poker 300 jetons', 'price' => 89],
            ['name' => 'Chaise de bureau ergonomique', 'price' => 149],
            ['name' => 'Table de salon en bois', 'price' => 199],
            ['name' => 'Aspirateur sans fil', 'price' => 129],
            ['name' => 'Ordinateur portable 15"', 'price' => 799],
            ['name' => 'Smartphone 64Go', 'price' => 699],
            ['name' => 'Montre connectée', 'price' => 249],
            ['name' => 'Télévision 4K 55"', 'price' => 899],
            ['name' => 'Machine à café expresso', 'price' => 99],
            ['name' => 'Casque audio sans fil', 'price' => 199],
            ['name' => 'Appareil photo reflex', 'price' => 499],
            ['name' => 'Tablette graphique', 'price' => 349],
            ['name' => 'Imprimante multifonction', 'price' => 149],
            ['name' => 'Lave-linge 7kg', 'price' => 399],
            ['name' => 'Réfrigérateur combiné', 'price' => 599],
            ['name' => 'Four à micro-ondes', 'price' => 89],
            ['name' => 'Grille-pain inox', 'price' => 49],
            ['name' => 'Mixeur plongeant', 'price' => 59],
            ['name' => 'Tondeuse électrique', 'price' => 299],
            ['name' => 'Tondeuse à gazon', 'price' => 349],
            ['name' => 'Barbecue à gaz', 'price' => 399],
            ['name' => 'Enceinte Bluetooth', 'price' => 99],
            ['name' => 'Drone avec caméra', 'price' => 499],
            ['name' => 'Vélo électrique', 'price' => 999],
            ['name' => 'Tapis de course', 'price' => 799],
            ['name' => 'Appareil de musculation', 'price' => 499],
            ['name' => 'Sac de voyage 60L', 'price' => 79],
            ['name' => 'Parure de lit 240x220', 'price' => 119],
            ['name' => 'Couverts en argent', 'price' => 299],
            ['name' => 'Trousse de maquillage', 'price' => 59],
            ['name' => 'Cafetière italienne', 'price' => 39],
            ['name' => 'Lampe de bureau LED', 'price' => 69],
            ['name' => 'Chauffage d\'appoint', 'price' => 109],
            ['name' => 'Ventilateur colonne', 'price' => 99],
            ['name' => 'Tablette 10" 64Go', 'price' => 399],
            ['name' => 'Souris gamer RGB', 'price' => 59],
            ['name' => 'Clavier mécanique', 'price' => 129],
            ['name' => 'Webcam HD', 'price' => 89],
            ['name' => 'Station météo connectée', 'price' => 149],
        ];
        $products = [];

        foreach ($products_data as $product_data) {
            $product = new Product();
            $product->setName($product_data['name']);
            $product->setPrice($product_data['price']);
            $product->setCreatedAt(new DateTimeImmutable());
            $manager->persist($product);
            $products[] = $product;
        }
        $manager->flush();

        // Orders (uncomment and modify if needed)
        /*
        for ($i = 0; $i < 5; $i++) {
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
        $manager->flush();
        */
    }
}
