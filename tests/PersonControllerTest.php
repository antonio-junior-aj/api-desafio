<?php
namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PersonControllerTest extends WebTestCase
{

    public function testCreate(): void
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
//        self::assertTrue($client->getResponse()->hasHeader('Location'));
        $finishedData = json_decode($client->getResponse()->getContent(true), true);
        $expected = ["data" => "Pessoa cadastrada"];
        self::assertEquals($expected, $finishedData);
    }
}
