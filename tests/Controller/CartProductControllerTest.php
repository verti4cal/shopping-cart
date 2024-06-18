<?php

namespace App\Tests\Controller;

use App\DataTransferObject\CartItemDTO;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CartProductControllerTest extends WebTestCase
{
    public function testListEmpty(): void
    {
        $client = static::createClient();
        $client->request('GET', '/cart/product/');

        $response = $client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());

        $json = json_decode($response->getContent(), true);
        $this->assertIsArray($json);
        $this->assertEquals(0, count($json));
    }

    public function testCreate(): void
    {
        $dto = new CartItemDTO();
        $dto->productUuid = '3a594d59-7cdf-4c9b-a3e3-7dfe726ab37c';
        $dto->quantity = 1;

        $client = static::createClient();
        $client->request('POST', '/cart/product/', [
            'productUuid' => $dto->productUuid,
            'quantity' => $dto->quantity
        ]);

        $response = $client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());

        $json = json_decode($response->getContent(), true);
        $this->assertIsArray($json);
        $this->assertEquals($dto->productUuid, $json['items'][0]['product']['uuid']);
        $this->assertEquals($dto->quantity, $json['items'][0]['quantity']);
    }

    public function testCreateFailed(): void
    {
        $dto = new CartItemDTO();
        $dto->productUuid = '3a594d59-7cdf-4c9b-a3e3-7dfe726ab372';
        $dto->quantity = 1;

        $client = static::createClient();
        $client->request('POST', '/cart/product/', [
            'productUuid' => $dto->productUuid,
            'quantity' => $dto->quantity
        ]);

        $this->assertResponseStatusCodeSame(500);
    }

    public function testGet(): void
    {
        $client = static::createClient();

        $dto = new CartItemDTO();
        $dto->productUuid = '3a594d59-7cdf-4c9b-a3e3-7dfe726ab37c';
        $dto->quantity = 1;

        $client->request('POST', '/cart/product/', [
            'productUuid' => $dto->productUuid,
            'quantity' => $dto->quantity
        ]);

        $client->request('GET', '/cart/product/3a594d59-7cdf-4c9b-a3e3-7dfe726ab37c');

        $response = $client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());

        $json = json_decode($response->getContent(), true);
        $this->assertIsArray($json);
        $this->assertEquals('3a594d59-7cdf-4c9b-a3e3-7dfe726ab37c', $json['product']['uuid']);
        $this->assertEquals(1, $json['quantity']);
    }

    public function testDelete(): void
    {
        $client = static::createClient();

        $dto = new CartItemDTO();
        $dto->productUuid = '3a594d59-7cdf-4c9b-a3e3-7dfe726ab37c';
        $dto->quantity = 1;

        $client->request('POST', '/cart/product/', [
            'productUuid' => $dto->productUuid,
            'quantity' => $dto->quantity
        ]);

        $client->request('DELETE', '/cart/product/3a594d59-7cdf-4c9b-a3e3-7dfe726ab37c');

        $this->assertResponseIsSuccessful();
    }

    public function testUpdate(): void
    {
        $client = static::createClient();

        $dto = new CartItemDTO();
        $dto->productUuid = '3a594d59-7cdf-4c9b-a3e3-7dfe726ab37c';
        $dto->quantity = 1;

        $client->request('POST', '/cart/product/', [
            'productUuid' => $dto->productUuid,
            'quantity' => $dto->quantity
        ]);

        $client->request('PUT', '/cart/product/', [
            'productUuid' => $dto->productUuid,
            'quantity' => 2
        ]);

        $client->request('GET', '/cart/product/3a594d59-7cdf-4c9b-a3e3-7dfe726ab37c');

        $response = $client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());

        $json = json_decode($response->getContent(), true);
        $this->assertIsArray($json);
        $this->assertEquals('3a594d59-7cdf-4c9b-a3e3-7dfe726ab37c', $json['product']['uuid']);
        $this->assertEquals(2, $json['quantity']);
    }
}
