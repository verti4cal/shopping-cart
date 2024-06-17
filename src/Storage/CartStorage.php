<?php

namespace App\Storage;

use App\Entity\Cart;
use App\Repository\CartRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartStorage
{
    const CART_KEY = 'cart';

    public function __construct(
        private RequestStack $requestStack,
        private CartRepository $cartRepository
    ) {
    }

    public function getCart(): ?Cart
    {
        $cartUuid = $this->getCartUuid();

        if (null === $cartUuid) {
            return null;
        }

        return $this->cartRepository->findOneBy(['uuid' => $cartUuid]);
    }

    public function getCartUuid(): ?string
    {
        return $this->requestStack->getSession()->get(self::CART_KEY);
    }

    public function setCart(?Cart $cart): void
    {
        if (null === $cart) {
            $this->requestStack->getSession()->remove(self::CART_KEY);
            return;
        }

        $this->requestStack->getSession()->set(self::CART_KEY, $cart->getUuid());
    }
}
