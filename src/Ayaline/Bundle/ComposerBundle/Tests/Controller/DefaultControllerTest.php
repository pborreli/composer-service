<?php

namespace Ayaline\ComposerBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class DemoControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Composer as a service")')->count()
        );

        $this->assertEquals(
            Response::HTTP_OK,
            $client->getResponse()->getStatusCode()
        );
    }

    public function testValidateEmptyForm()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $form = $crawler->selectButton('Go')->form();
        $client->submit($form, array('form[body]' => null));

        $this->assertEquals(
            Response::HTTP_OK,
            $client->getResponse()->getStatusCode()
        );

        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('message', $data);

        $this->assertEquals('ko', $data['status']);
        $this->assertContains('Please provide a composer.json', $data['message']);
    }

    public function testValidateIncorrectComposerJsonForm()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $form = $crawler->selectButton('Go')->form();
        $client->submit($form, array('form[body]' => '{]'));

        $this->assertEquals(
            Response::HTTP_OK,
            $client->getResponse()->getStatusCode()
        );

        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('message', $data);

        $this->assertEquals('ko', $data['status']);
        $this->assertContains('does not contain valid JSON', $data['message']);
    }
}
