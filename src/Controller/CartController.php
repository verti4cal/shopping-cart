<?php

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cart', name: 'cart_')]
class CartController extends AbstractController
{
    public function __construct(private CartService $cartService)
    {
    }

    #[Route('/', name: 'get', methods: ['GET'])]
    public function get(): JsonResponse
    {
        $cart = $this->cartService->getCart();
        return $this->json($cart);
    }

    #[Route('/', name: 'delete', methods: ["DELETE"])]
    public function delete(): JsonResponse
    {
        $this->cartService->removeCart();
        return $this->json(null, 204);
    }
}
