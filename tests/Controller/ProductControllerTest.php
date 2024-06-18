<?php

namespace App\Tests\Controller;

use App\DataTransferObject\ProductDTO;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductControllerTest extends WebTestCase
{
    public function testList(): void
    {
        $client = static::createClient();
        $client->request('GET', '/product/');

        $response = $client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());
        $this->assertIsArray(json_decode($response->getContent(), true));
    }

    public function testGet(): void
    {
        $client = static::createClient();
        $client->request('GET', '/product/8e6442d1-78bc-4809-8c03-8aee292e5550');

        $response = $client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());
    }

    public function testUpdate(): void
    {
        $dto = new ProductDTO();
        $dto->name = 'Product 1 - updated';
        $dto->price = 399;

        $client = static::createClient();
        $client->request('PUT', '/product/8e6442d1-78bc-4809-8c03-8aee292e5550', [
            'name' => $dto->name,
            'price' => $dto->price
        ]);

        $response = $client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());

        $product = json_decode($response->getContent(), true);
        $this->assertEquals($dto->name, $product['name']);
        $this->assertEquals($dto->price, $product['price']);
    }

    public function testCreate(): void
    {
        $dto = new ProductDTO();
        $dto->name = 'Product 3';
        $dto->price = 399;

        $client = static::createClient();
        $client->request('POST', '/product/', [
            'name' => $dto->name,
            'price' => $dto->price
        ]);

        $response = $client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());

        $product = json_decode($response->getContent(), true);
        $this->assertEquals($dto->name, $product['name']);
        $this->assertEquals($dto->price, $product['price']);
    }

    public function testDelete(): void
    {
        $client = static::createClient();
        $client->request('DELETE', '/product/8e6442d1-78bc-4809-8c03-8aee292e5550');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(204);
    }

    public function testDeleteFailed(): void
    {
        $client = static::createClient();
        $client->request('DELETE', '/product/00000000-0000-0000-0000-000000000003');

        $this->assertResponseStatusCodeSame(500);
    }
}
