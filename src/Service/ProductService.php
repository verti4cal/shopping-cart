<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class ProductService
{
    public function __construct(
        private ProductRepository $productRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Get all products
     * @return Product[]
     */
    public function getProducts(): array
    {
        return $this->productRepository->findAll();
    }

    /**
     * Get product
     * @param string $uuid 
     * @return null|Product 
     */
    public function getProduct(string $uuid): ?Product
    {
        return $this->productRepository->findOneBy(['uuid' => $uuid]);
    }

    /**
     * Delete product
     * @param string $uuid 
     * @return void 
     * @throws NotFoundResourceException 
     */
    public function deleteProduct(string $uuid): void
    {
        $product = $this->getProduct($uuid);
        if (!$product) {
            throw new NotFoundResourceException('Product not found');
        }

        $this->entityManager->remove($product);
        $this->entityManager->flush();
    }

    /**
     * Create product
     * @param string $name 
     * @param int $price 
     * @return Product 
     */
    public function createProduct(string $name, int $price): Product
    {
        $product = new Product();

        $product->setName($name);
        $product->setPrice($price);

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $product;
    }

    /**
     * Update product
     * @param string $uuid 
     * @param string $name 
     * @param int $price 
     * @return Product 
     * @throws NotFoundResourceException 
     */
    public function updateProduct(string $uuid, string $name, int $price): Product
    {
        $product = $this->getProduct($uuid);
        if (!$product) {
            throw new NotFoundResourceException('Product not found');
        }

        $product->setName($name);
        $product->setPrice($price);

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $product;
    }
}
