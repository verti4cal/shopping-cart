<?php

namespace App\Storage;

use App\Entity\Cart;
use App\Repository\CartRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartStorage
{
    const CART_KEY = 'cart';

    public function __construct(
        private RequestStack $requestStack,
        private CartRepository $cartRepository
    ) {
    }

    /**
     * Get session
     * @return null|SessionInterface 
     */
    private function getSession(): ?SessionInterface
    {
        try {
            return $this->requestStack->getSession();
        } catch (\Throwable $th) {
            return null;
        }
    }

    /**
     * Get cart
     * @return null|Cart 
     */
    public function getCart(): ?Cart
    {
        $cartUuid = $this->getCartUuid();

        if (null === $cartUuid) {
            return null;
        }

        return $this->cartRepository->findOneBy(['uuid' => $cartUuid]);
    }

    /**
     * Get cart Uuid
     * @return null|string 
     */
    public function getCartUuid(): ?string
    {
        $session = $this->getSession();
        if (!$session) {
            return null;
        }

        return $session->get(self::CART_KEY);
    }

    /**
     * Set cart
     * @param null|Cart $cart 
     * @return void 
     */
    public function setCart(?Cart $cart): void
    {
        $session = $this->getSession();
        if (!$session) {
            return;
        }

        if (null === $cart) {
            $this->requestStack->getSession()->remove(self::CART_KEY);
            return;
        }

        $this->requestStack->getSession()->set(self::CART_KEY, $cart->getUuid());
    }
}
