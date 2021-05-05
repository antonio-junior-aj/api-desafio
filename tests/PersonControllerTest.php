<?php
namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PersonControllerTest extends WebTestCase
{

    const ID_TRATADO = 1; # ID utilizado de forma estática

    public function testCreateWithSuccess(): void
    {
        $client = static::createClient();
        $postData = [
            "type" => "J",
            "value" => "55.238.879/0001-04"
        ];

        $client->request(
            'POST',
            '/persons/',
            $postData,
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
//        self::assertTrue($client->getResponse()->hasHeader('Location')); # passado no HEADER
        $finishedData = json_decode($client->getResponse()->getContent(true), true);
        $expected = ["data" => "Pessoa cadastrada"];
        self::assertEquals($expected, $finishedData);
    }

    public function testCreateWithFailure(): void
    {
        $client = static::createClient();
        $postData = [
            "type" => "J",
            "value" => "000" # CNPJ inválido
        ];

        $client->request(
            'POST',
            '/persons/',
            $postData,
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
        $finishedData = json_decode($client->getResponse()->getContent(true), true);
        $expected = ["data" => "Pessoa cadastrada"];
        self::assertEquals($expected, $finishedData);
    }

    public function testUpdateWithSuccess(): void
    {
        $client = static::createClient();
        $postData = [
            "type" => "J",
            "value" => "55.238.879/0001-04"
        ];

        $client->request(
            'PUT',
            '/persons/' . self::ID_TRATADO,
            $postData,
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
        $finishedData = json_decode($client->getResponse()->getContent(true), true);
        $expected = ["data" => "Pessoa editada"];
        self::assertEquals($expected, $finishedData);
    }

    public function testUpdateWithFailure(): void
    {
        $client = static::createClient();
        $postData = [
            "type" => "F",
            "value" => "" # CPF em branco
        ];

        $client->request(
            'PUT',
            '/persons/' . self::ID_TRATADO,
            $postData,
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
        $finishedData = json_decode($client->getResponse()->getContent(true), true);
        $expected = ["data" => "Pessoa edita"];
        self::assertEquals($expected, $finishedData);
    }

    public function testDeleteWithSuccess(): void
    {
        $client = static::createClient();

        $client->request(
            'DELETE',
            '/persons/' . self::ID_TRATADO,
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
        $finishedData = json_decode($client->getResponse()->getContent(true), true);
        $expected = ["data" => "Pessoa excluída"];
        self::assertEquals($expected, $finishedData);
    }

    public function testDeleteWithFailure(): void
    {
        $client = static::createClient();

        $client->request(
            'DELETE',
            '/persons/0', # sem id passado
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
        $finishedData = json_decode($client->getResponse()->getContent(true), true);
        $expected = ["data" => "Pessoa excluída"];
        self::assertEquals($expected, $finishedData);
    }

    public function testBlacklistWithSuccess(): void
    {
        $client = static::createClient();
        $postData = [
            "blacklist" => true,
            "reason" => "Removido devido ao teste"
        ];

        $client->request(
            'PUT',
            '/persons/' . self::ID_TRATADO . '/blacklist',
            $postData,
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testBlacklistWithFailure(): void
    {
        $client = static::createClient();
        $postData = [
//            "blacklist" => false, # sem blacklist
            "reason" => ""
        ];

        $client->request(
            'PUT',
            '/persons/' . self::ID_TRATADO . '/blacklist',
            $postData,
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testIndex(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/persons',
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testReorder(): void
    {
        $client = static::createClient();
        $postData = [
            "ids" => array(2, 1)
        ];

        $client->request(
            'POST',
            '/persons/reorder',
            $postData,
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
