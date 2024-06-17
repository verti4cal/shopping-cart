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
    public function createCart(): Cart
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
     * Clear cart
     * @return Cart
     */
    public function clearCart(): Cart
    {
        $cart = $this->getCart();
        if (!$cart) {
            return $cart;
        }

        $cart->clearItems();
        $this->saveCart($cart);

        return $cart;
    }

    /**
     * Remove cart
     * @return void
     */
    public function removeCart(): void
    {
        $cart = $this->getCart();
        if (!$cart) {
            return;
        }

        $this->entityManager->remove($cart);
        $this->entityManager->flush();

        $this->cartStorage->setCart(null);
    }

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

    public function addProduct(string $productUuid, int $quantity = 1): Cart
    {
        $cart = $this->getCart();
        if (!$cart) {
            return $cart;
        }

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

    public function removeProduct(string $productUuid): Cart
    {
        $cart = $this->getCart();
        if (!$cart) {
            return $cart;
        }

        $item = $cart->getItemByProductUuid($productUuid);
        if (!$item) {
            return $cart;
        }

        $cart->removeItem($item);
        $this->saveCart($cart);

        return $cart;
    }

    public function updateQuantity(string $productUuid, int $quantity): Cart
    {
        $cart = $this->getCart();
        if (!$cart) {
            return $cart;
        }

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
