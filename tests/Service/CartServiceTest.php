<?php

namespace App\Tests\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Service\CartService;
use App\Service\ProductService;
use App\Storage\CartStorage;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Uid\Uuid;

class CartServiceTest extends TestCase
{
    private MockObject & CartStorage $cartStorage;
    private MockObject & ProductService $productService;
    private MockObject & EntityManagerInterface $entityManager;

    private CartService $cartService;

    public function setUp(): void
    {
        $this->cartStorage = $this->createMock(CartStorage::class);
        $this->productService = $this->createMock(ProductService::class);
        $this->entityManager = $this->createMock(EntityManager::class);

        $this->cartService = new CartService($this->cartStorage, $this->productService, $this->entityManager);
    }

    public function testGetCartWithStorage(): void
    {
        $this->cartStorage
            ->expects($this->any())
            ->method('getCart')
            ->willReturn(new Cart());

        $response = $this->cartService->getCart();
        $this->assertInstanceOf(Cart::class, $response);
    }

    public function testGetCartWithoutStorage(): void
    {
        $this->cartStorage
            ->expects($this->any())
            ->method('getCart')
            ->willReturn(null);

        $response = $this->cartService->getCart();
        $this->assertInstanceOf(Cart::class, $response);
    }

    public function testSaveCart(): void
    {
        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $this->cartStorage->expects($this->once())->method('setCart')
            ->with($this->isInstanceOf(Cart::class));

        $cart = new Cart();
        $this->cartService->saveCart($cart);
        $this->assertInstanceOf(Cart::class, $cart);
    }

    public function testRemoveCartWithStorage(): void
    {
        $this->cartStorage
            ->expects($this->any())
            ->method('getCart')
            ->willReturn(new Cart());

        $this->entityManager->expects($this->once())->method('remove');
        $this->entityManager->expects($this->once())->method('flush');

        $this->cartStorage->expects($this->once())->method('setCart')
            ->with(null);

        $this->cartService->removeCart();
    }

    public function testRemoveCartWithoutStorage(): void
    {
        $this->cartStorage
            ->expects($this->any())
            ->method('getCart')
            ->willReturn(null);

        $this->entityManager->expects($this->never())->method('remove');
        $this->entityManager->expects($this->never())->method('flush');

        $this->cartStorage->expects($this->never())->method('setCart');

        $this->cartService->removeCart();
    }

    public function testAddProductSuccess(): void
    {
        $this->productService->expects($this->any())->method('getProduct')
            ->with('product-uuid')
            ->willReturn(new Product());

        $cart = $this->cartService->addProduct('product-uuid');
        $this->assertInstanceOf(Cart::class, $cart);
        $this->assertEquals(1, $cart->getItems()->count());
    }

    public function testAddProductExisting(): void
    {
        $productUuid = Uuid::v4();
        $product = (new Product())->setUuid($productUuid)->setPrice(100);
        $cartItem = (new CartItem())->setProduct($product)->setQuantity(1);

        $cart = (new Cart())->addItem($cartItem);

        $this->cartStorage
            ->expects($this->any())
            ->method('getCart')
            ->willReturn($cart);

        $this->productService->expects($this->any())->method('getProduct')
            ->with($productUuid->__toString())
            ->willReturn($product);

        $cart = $this->cartService->addProduct($productUuid->__toString());
        $this->assertInstanceOf(Cart::class, $cart);
        $this->assertEquals(1, $cart->getItems()->count());
        $this->assertEquals(200, $cart->getTotal());
    }

    public function testAddProductFailed(): void
    {
        $this->productService->expects($this->any())->method('getProduct')
            ->with('product-uuid')
            ->willReturn(null);

        $this->expectException(NotFoundResourceException::class);

        $cart = $this->cartService->addProduct('product-uuid');
        $this->assertInstanceOf(Cart::class, $cart);
        $this->assertEquals(0, $cart->getItems()->count());
    }

    public function testAddProductTotalPrice(): void
    {
        $this->productService->expects($this->any())->method('getProduct')
            ->with('product-uuid')
            ->willReturn((new Product())->setPrice(100));

        $cart = $this->cartService->addProduct('product-uuid');
        $this->assertInstanceOf(Cart::class, $cart);
        $this->assertEquals(100, $cart->getTotal());

        $cart = $this->cartService->addProduct('product-uuid', 2);
        $this->assertInstanceOf(Cart::class, $cart);
        $this->assertEquals(200, $cart->getTotal());
    }

    public function testRemoveProduct(): void
    {
        $product1Uuid = Uuid::v4();
        $product2Uuid = Uuid::v4();

        $product1 = (new Product())->setUuid($product1Uuid)->setPrice(100);
        $product2 = (new Product())->setUuid($product2Uuid)->setPrice(100);

        $cartItem1 = (new CartItem())->setProduct($product1)->setQuantity(1);
        $cartItem2 = (new CartItem())->setProduct($product2)->setQuantity(2);

        $cart = (new Cart())->addItem($cartItem1)->addItem($cartItem2);

        $this->cartStorage
            ->expects($this->any())
            ->method('getCart')
            ->willReturn($cart);

        $this->assertEquals(300, $cart->getTotal());

        $cart = $this->cartService->removeProduct($product1Uuid->__toString());

        $this->assertInstanceOf(Cart::class, $cart);
        $this->assertEquals(1, $cart->getItems()->count());
        $this->assertEquals(200, $cart->getTotal());
    }

    public function testUpdateQuantity(): void
    {
        $product1Uuid = Uuid::v4();
        $product2Uuid = Uuid::v4();

        $product1 = (new Product())->setUuid($product1Uuid)->setPrice(100);
        $product2 = (new Product())->setUuid($product2Uuid)->setPrice(100);

        $cartItem1 = (new CartItem())->setProduct($product1)->setQuantity(1);
        $cartItem2 = (new CartItem())->setProduct($product2)->setQuantity(2);

        $cart = (new Cart())->addItem($cartItem1)->addItem($cartItem2);

        $this->cartStorage
            ->expects($this->any())
            ->method('getCart')
            ->willReturn($cart);

        $this->assertEquals(300, $cart->getTotal());

        $cart = $this->cartService->updateQuantity($product1Uuid->__toString(), 2);

        $this->assertInstanceOf(Cart::class, $cart);
        $this->assertEquals(2, $cart->getItems()->count());
        $this->assertEquals(400, $cart->getTotal());
    }

    public function testUpdateQuantityRemove(): void
    {
        $product1Uuid = Uuid::v4();
        $product2Uuid = Uuid::v4();

        $product1 = (new Product())->setUuid($product1Uuid)->setPrice(100);
        $product2 = (new Product())->setUuid($product2Uuid)->setPrice(100);

        $cartItem1 = (new CartItem())->setProduct($product1)->setQuantity(1);
        $cartItem2 = (new CartItem())->setProduct($product2)->setQuantity(2);

        $cart = (new Cart())->addItem($cartItem1)->addItem($cartItem2);

        $this->cartStorage
            ->expects($this->any())
            ->method('getCart')
            ->willReturn($cart);

        $this->assertEquals(300, $cart->getTotal());

        $cart = $this->cartService->updateQuantity($product1Uuid->__toString(), 0);

        $this->assertInstanceOf(Cart::class, $cart);
        $this->assertEquals(1, $cart->getItems()->count());
        $this->assertEquals(200, $cart->getTotal());
    }

    public function testUpdateQuantityAdd(): void
    {
        $product1Uuid = Uuid::v4();
        $product2Uuid = Uuid::v4();

        $product1 = (new Product())->setUuid($product1Uuid)->setPrice(100);
        $product2 = (new Product())->setUuid($product2Uuid)->setPrice(100);

        $cartItem1 = (new CartItem())->setProduct($product1)->setQuantity(1);

        $cart = (new Cart())->addItem($cartItem1);

        $this->cartStorage
            ->expects($this->any())
            ->method('getCart')
            ->willReturn($cart);

        $this->productService->expects($this->any())->method('getProduct')
            ->with($product2Uuid->__toString())
            ->willReturn($product2);

        $this->assertEquals(100, $cart->getTotal());

        $cart = $this->cartService->updateQuantity($product2Uuid->__toString(), 1);

        $this->assertInstanceOf(Cart::class, $cart);
        $this->assertEquals(2, $cart->getItems()->count());
        $this->assertEquals(200, $cart->getTotal());
    }
}
