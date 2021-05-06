<?php
namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;

class PersonControllerTest extends WebTestCase
{

    public function setUp(): void
    {
        # para limpar e gerar a fixture para cada execução
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $loader = new Loader();
        $loader->addFixture(new \App\DataFixtures\AppFixtures());

        $purger = new ORMPurger($this->em);
        $executor = new ORMExecutor($this->em, $purger);
        $executor->execute($loader->getFixtures());

        parent::setUp();
    }

    public function testIndex(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/persons/',
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testShow(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/persons/1',
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
    }

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
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($postData),
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
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($postData),
        );

        self::assertEquals(400, $client->getResponse()->getStatusCode());
        $finishedData = json_decode($client->getResponse()->getContent(true), true);
        $expected = ["errors" => "CPF/CNPJ inválido"];
        self::assertEquals($expected, $finishedData);
    }

    public function testUpdateWithSuccess(): void
    {
        $client = static::createClient();
        $postData = [
            "type" => "J",
            "value" => "04.119.828/0001-14"
        ];

        $client->request(
            'PUT',
            '/persons/2',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($postData),
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
            '/persons/2',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($postData),
        );

        self::assertEquals(400, $client->getResponse()->getStatusCode());
        $finishedData = json_decode($client->getResponse()->getContent(true), true);
        $expected = ["errors" => ["CPF/CNPJ não preenchido","CPF/CNPJ deve ter no mínimo 11 caracteres"]];
        self::assertEquals($expected, $finishedData);
    }

    public function testDeleteWithSuccess(): void
    {
        $client = static::createClient();

        $client->request(
            'DELETE',
            '/persons/3',
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

        self::assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function testBlacklist(): void
    {
        $client = static::createClient();
        $postData = [
            "blacklist" => true,
            "reason" => "Removido devido ao teste"
        ];

        $client->request(
            'PUT',
            '/persons/4' . '/blacklist',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($postData),
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
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($postData),
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
