<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $product = new Product();
        $product->setName('Product 1');
        $product->setPrice(299);
        $manager->persist($product);

        $product = new Product();
        $product->setName('Product 2');
        $product->setPrice(599);
        $manager->persist($product);

        $manager->flush();
    }
}
