<?php

namespace App\Tests\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\ProductService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class ProductServiceTest extends TestCase
{
    private MockObject & ProductRepository $productRepository;
    private MockObject & EntityManagerInterface $entityManager;

    private ProductService $productService;

    private function mockRepositoryFunctions(): void
    {
        // findOneBy
        $this->productRepository->expects($this->any())
            ->method('findOneBy')
            ->with(['uuid' => "1234"])
            ->willReturn((new Product())->setName('test')->setPrice(200));

        // findAll
        $this->productRepository->expects($this->any())
            ->method('findAll')
            ->willReturn([
                (new Product())->setName('test')->setPrice(200),
                (new Product())->setName('test2')->setPrice(400)
            ]);
    }

    public function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepository::class);
        $this->entityManager = $this->createMock(EntityManager::class);

        $this->productService = new ProductService($this->productRepository, $this->entityManager);
    }

    public function testCreateProduct(): void
    {
        $this->mockRepositoryFunctions();

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Product::class));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $response = $this->productService->createProduct('test', 100);
        $this->assertInstanceOf(Product::class, $response);
        $this->assertEquals('test', $response->getName());
        $this->assertEquals(100, $response->getPrice());
    }

    public function testUpdateProduct(): void
    {
        $this->mockRepositoryFunctions();

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Product::class));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $before = $this->productService->getProduct("1234");
        $this->assertInstanceOf(Product::class, $before);
        $this->assertEquals('test', $before->getName());
        $this->assertEquals(200, $before->getPrice());

        $response = $this->productService->updateProduct("1234", 'test', 100);
        $this->assertInstanceOf(Product::class, $response);
        $this->assertEquals('test', $response->getName());
        $this->assertEquals(100, $response->getPrice());
    }

    public function testUpdateProductFailed(): void
    {
        $this->entityManager->expects($this->never())
            ->method('persist');

        $this->entityManager->expects($this->never())
            ->method('flush');

        $this->expectException(NotFoundResourceException::class);

        $this->productService->updateProduct("12345", 'test', 100);
    }

    public function testDeleteProduct(): void
    {
        $this->mockRepositoryFunctions();

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($this->isInstanceOf(Product::class));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->productService->deleteProduct("1234");
    }

    public function testDeleteProductFailed(): void
    {
        $this->entityManager->expects($this->never())
            ->method('remove');

        $this->entityManager->expects($this->never())
            ->method('flush');

        $this->expectException(NotFoundResourceException::class);

        $this->productService->deleteProduct("12345");
    }

    public function testGetProduct(): void
    {
        $this->mockRepositoryFunctions();

        $response = $this->productService->getProduct("1234");
        $this->assertInstanceOf(Product::class, $response);
        $this->assertEquals('test', $response->getName());
        $this->assertEquals(200, $response->getPrice());
    }

    public function testGetProducts(): void
    {
        $this->mockRepositoryFunctions();

        $response = $this->productService->getProducts();
        $this->assertIsArray($response);
    }
}
