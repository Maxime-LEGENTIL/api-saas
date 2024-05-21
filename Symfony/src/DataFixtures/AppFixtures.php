<?php

namespace App\DataFixtures;

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

        $user = new User();
        $user->setEmail('admin@admin.com');
        $user->setPassword('$2y$10$PYNxB4I2/0LfIwBMg9EBaeVTHY/00IrH.LxkVbWnmITqBnXawEIQm');
        $user->setFirstname('Maxime');
        $user->setLastname('LE GENTIL');
        $user->setCreatedAt(new DateTimeImmutable());
        //$user->setUpdatedAt();
        
        $manager->persist($user);
        $manager->flush();
    }
}