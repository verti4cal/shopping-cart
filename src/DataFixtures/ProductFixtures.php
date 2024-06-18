<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $uuid = Uuid::fromString('8e6442d1-78bc-4809-8c03-8aee292e5550');
        $product = new Product();
        $product->setName('Product 1');
        $product->setPrice(299);
        $product->setUuid($uuid);
        $manager->persist($product);

        $uuid = Uuid::fromString('3a594d59-7cdf-4c9b-a3e3-7dfe726ab37c');
        $product = new Product();
        $product->setName('Product 2');
        $product->setPrice(599);
        $product->setUuid($uuid);
        $manager->persist($product);

        $manager->flush();
    }
}
