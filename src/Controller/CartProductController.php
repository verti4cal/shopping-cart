<?php

namespace App\Controller;

use App\DataTransferObject\CartItemDTO;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cart/product', name: 'cart_product_')]
class CartProductController extends AbstractController
{
    public function __construct(private CartService $cartService)
    {
    }

    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $cart = $this->cartService->getCart();
        return $this->json($cart->getItems());
    }

    #[Route('/{uuid}', name: 'get', methods: ['GET'])]
    public function get(string $uuid): JsonResponse
    {
        $cart = $this->cartService->getCart();
        return $this->json($cart->getItemByProductUuid($uuid));
    }

    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(#[MapRequestPayload] CartItemDTO  $dto): JsonResponse
    {
        $item = $this->cartService->addProduct($dto->productUuid, $dto->quantity);
        return $this->json($item);
    }

    #[Route('/{uuid}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $uuid): JsonResponse
    {
        $this->cartService->removeProduct($uuid);
        return $this->json(null, 204);
    }

    #[Route('/', name: 'update', methods: ['PUT'])]
    public function update(#[MapRequestPayload] CartItemDTO  $dto): JsonResponse
    {
        $item = $this->cartService->updateQuantity($dto->productUuid, $dto->quantity);
        return $this->json($item);
    }
}
