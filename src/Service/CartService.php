<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Storage\CartStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class CartService
{
    public function __construct(
        private CartStorage $cartStorage,
        private ProductService $productService,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Get cart
     * @return Cart
     */
    public function getCart(): Cart
    {
        $cart = $this->cartStorage->getCart();

        if (!$cart) {
            $cart = $this->createCart();
            $this->saveCart($cart);
        }

        return $cart;
    }

    /**
     * Create cart
     * @return Cart 
     */
    private function createCart(): Cart
    {
        $cart = new Cart();
        return $cart;
    }

    /**
     * Save cart
     * @param Cart $cart 
     * @return void
     */
    public function saveCart(Cart $cart): void
    {
        $this->entityManager->persist($cart);
        $this->entityManager->flush();

        $this->cartStorage->setCart($cart);
    }

    /**
     * Remove cart
     * @return void
     */
    public function removeCart(): void
    {
        $cart = $this->cartStorage->getCart();
        if (!$cart) {
            return;
        }

        $this->entityManager->remove($cart);
        $this->entityManager->flush();

        $this->cartStorage->setCart(null);
    }

    /**
     * Create cart item
     * @param string $productUuid 
     * @param int $quantity 
     * @return CartItem 
     * @throws NotFoundResourceException 
     */
    public function createCartItem(string $productUuid, int $quantity = 1): CartItem
    {
        $product = $this->productService->getProduct($productUuid);
        if (!$product) {
            throw new NotFoundResourceException('Product not found');
        }

        $cartItem = new CartItem();
        $cartItem->setProduct($product);
        $cartItem->setQuantity($quantity);

        return $cartItem;
    }

    /**
     * Add product
     * @param string $productUuid 
     * @param int $quantity 
     * @return Cart 
     * @throws NotFoundResourceException 
     */
    public function addProduct(string $productUuid, int $quantity = 1): Cart
    {
        $cart = $this->getCart();

        $item = $cart->getItemByProductUuid($productUuid);
        if (!$item) {
            $item = $this->createCartItem($productUuid, $quantity);
            $cart->addItem($item);
        } else {
            $item->setQuantity($item->getQuantity() + $quantity);
        }

        $this->saveCart($cart);

        return $cart;
    }

    /**
     * Remove product
     * @param string $productUuid 
     * @return Cart 
     */
    public function removeProduct(string $productUuid): Cart
    {
        $cart = $this->getCart();

        $item = $cart->getItemByProductUuid($productUuid);
        if (!$item) {
            return $cart;
        }

        $cart->removeItem($item);
        $this->saveCart($cart);

        return $cart;
    }

    /**
     * Update quantity
     * @param string $productUuid 
     * @param int $quantity 
     * @return Cart 
     * @throws NotFoundResourceException 
     */
    public function updateQuantity(string $productUuid, int $quantity): Cart
    {
        $cart = $this->getCart();

        if ($quantity == 0) {
            return $this->removeProduct($productUuid);
        }

        $item = $cart->getItemByProductUuid($productUuid);
        if (!$item) {
            $item = $this->createCartItem($productUuid, $quantity);
            $cart->addItem($item);
        }

        $item->setQuantity($quantity);
        $this->saveCart($cart);

        return $cart;
    }
}
