<?php

namespace App\Controller;

use App\DataTransferObject\ProductDTO;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/product', name: 'product_')]
class ProductController extends AbstractController
{
    public function __construct(private ProductService $productService)
    {
    }

    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $products = $this->productService->getProducts();
        return $this->json($products);
    }

    #[Route('/{uuid}', name: 'get', methods: ['GET'])]
    public function get(string $uuid): JsonResponse
    {
        $product = $this->productService->getProduct($uuid);
        return $this->json($product);
    }

    #[Route('/{uuid}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $uuid): JsonResponse
    {
        $this->productService->deleteProduct($uuid);
        return $this->json(null, 204);
    }

    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(#[MapRequestPayload] ProductDTO $product): JsonResponse
    {
        $product = $this->productService->createProduct($product->name, $product->price);
        return $this->json($product, 201);
    }

    #[Route('/{uuid}', name: 'update', methods: ['PUT'])]
    public function update(string $uuid, #[MapRequestPayload] ProductDTO $product): JsonResponse
    {
        $product = $this->productService->updateProduct($uuid, $product->name, $product->price);
        return $this->json($product);
    }
}
