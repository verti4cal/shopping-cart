<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CartControllerTest extends WebTestCase
{
    public function testGet(): void
    {
        $client = static::createClient();
        $client->request('GET', '/cart/');

        $response = $client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());
    }

    public function testDelete(): void
    {
        $client = static::createClient();
        $client->request('DELETE', '/cart/');

        $this->assertResponseStatusCodeSame(204);
    }
}
