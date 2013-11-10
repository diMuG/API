<?php
namespace diMuG;

use Silex\Application;
use Silex\Provider\SecurityServiceProvider;
use Silex\WebTestCase;
use Symfony\Component\HttpKernel\HttpKernel;

/**
 * apiTest.php.
 *
 * @author     Benjamin Geißler <benjamin.geissler@gmail.com>
 * @package    diMuG
 */
class APITest extends WebTestCase
{
    public function testWrongConfiguration()
    {
        $this->app['parameters.file.configuration'] = __DIR__ . '/../config/error.yml';
        $client = $this->createClient();
        $client->request('GET', '/api/types');

        $this->assertTrue($client->getResponse()->isServerError());
    }

    public function testVersion()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/version');

        $this->assertTrue($client->getResponse()->isOk());
        $json = json_decode($client->getResponse()->getContent(), true);
        $this->assertInternalType('array', $json);
        $this->assertArrayHasKey('version', $json);
        $this->assertEquals(1, $json['version']);
    }

    public function testTypes()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/types');

        $this->assertTrue($client->getResponse()->isOk());
        $json = json_decode($client->getResponse()->getContent(), true);
        $this->assertInternalType('array', $json);
        $this->assertEquals(3, count($json));

        $this->assertInternalType('array', $json[0]);
        $this->assertArrayHasKey('name', $json[0]);
        $this->assertEquals('coins', $json[0]['name']);
        $this->assertArrayHasKey('label', $json[0]);
        $this->assertEquals('Münzen', $json[0]['label']);

        $this->assertInternalType('array', $json[1]);
        $this->assertArrayHasKey('name', $json[1]);
        $this->assertEquals('foo', $json[1]['name']);
        $this->assertArrayHasKey('label', $json[1]);
        $this->assertEquals('bar', $json[1]['label']);

        $this->assertInternalType('array', $json[2]);
        $this->assertArrayHasKey('name', $json[2]);
        $this->assertEquals('other', $json[2]['name']);
        $this->assertArrayHasKey('label', $json[2]);
        $this->assertEquals('Something new', $json[2]['label']);
    }

    public function testTypesError()
    {
        $this->app['parameters.file.configuration'] = '';

        $client = $this->createClient();
        $client->request('GET', '/api/types');

        $this->assertTrue($client->getResponse()->isServerError());
    }

    public function testFieldsForType()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/fields/foo');

        $this->assertTrue($client->getResponse()->isOk());
        $json = json_decode($client->getResponse()->getContent(), true);
        $this->assertInternalType('array', $json);
        $this->assertEquals(2, count($json));

        $this->assertArrayHasKey('authority', $json);
        $this->assertInternalType('array', $json['authority']);
        $this->assertArrayHasKey('type', $json['authority']);
        $this->assertEquals('string', $json['authority']['type']);
        $this->assertArrayHasKey('label', $json['authority']);
        $this->assertEquals('Prägeherr', $json['authority']['label']);
    }

    public function testNoFieldsForMissingType()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/fields/nobar');

        $this->assertTrue($client->getResponse()->isClientError());
    }

    public function testPicturesForType()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/pictures/coins');

        $this->assertTrue($client->getResponse()->isOk());
        $json = json_decode($client->getResponse()->getContent(), true);
        $this->assertInternalType('array', $json);
        $this->assertArrayHasKey('online', $json);
        $this->assertTrue($json['online']);
        $this->assertArrayHasKey('url', $json);
        $this->assertEquals('http://www.url.com/pictures', $json['url']);
        $this->assertArrayHasKey('security', $json);
        $this->assertTrue($json['security']);
        $this->assertArrayHasKey('user', $json);
        $this->assertEquals('user', $json['user']);
        $this->assertArrayHasKey('password', $json);
        $this->assertEquals('password', $json['password']);
    }

    public function testPicturesForType1()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/pictures/foo');

        $this->assertTrue($client->getResponse()->isOk());
        $json = json_decode($client->getResponse()->getContent(), true);
        $this->assertInternalType('array', $json);
        $this->assertArrayHasKey('online', $json);
        $this->assertFalse($json['online']);
        $this->assertArrayNotHasKey('url', $json);
        $this->assertArrayNotHasKey('security', $json);
    }

    public function testNoPicturesForWrongType()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/pictures/fooBar');

        $this->assertTrue($client->getResponse()->isClientError());
    }


    public function testSoundsForType()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/sounds/coins');

        $this->assertTrue($client->getResponse()->isOk());
        $json = json_decode($client->getResponse()->getContent(), true);
        $this->assertInternalType('array', $json);
        $this->assertArrayHasKey('online', $json);
        $this->assertFalse($json['online']);
    }

    public function testNoSoundsForType()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/sounds/foo');

        $this->assertTrue($client->getResponse()->isOk());
        $json = json_decode($client->getResponse()->getContent(), true);
        $this->assertInternalType('array', $json);
        $this->assertEquals(0, count($json));
    }

    public function testNoSoundsForWrongType()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/sounds/noFooBar');
        $this->assertTrue($client->getResponse()->isClientError());
    }

    public function testGlossaryForType()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/glossary/coins');

        $this->assertTrue($client->getResponse()->isOk());
        $json = json_decode($client->getResponse()->getContent(), true);
        $this->assertInternalType('array', $json);
        foreach ($json as $entry) {
            $this->assertArrayHasKey('title', $entry);
            $this->assertArrayHasKey('match', $entry);
            $this->assertArrayHasKey('content', $entry);
        }
    }

    public function testNoGlossaryForWrongType()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/glossary/noFooBar');
        $this->assertTrue($client->getResponse()->isClientError());
    }

    public function testFindArtefactByInventory()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/find/coins/123');

        $this->assertTrue($client->getResponse()->isOk());
        $json = json_decode($client->getResponse()->getContent(), true);
        $this->assertInternalType('array', $json);

        $this->assertArrayHasKey('title', $json);
        $this->assertEquals('test', $json['title']);
        $this->assertArrayHasKey('inventory', $json);
        $this->assertEquals('123', $json['inventory']);
        $this->assertArrayHasKey('other', $json);
        $this->assertEquals('value', $json['other']);
        $this->assertArrayHasKey('more', $json);
        $this->assertEquals('-123', $json['more']);
        $this->assertArrayHasKey('yes', $json);
        $this->assertTrue($json['yes']);
        $this->assertArrayHasKey('float', $json);
        $this->assertEquals(1.2, $json['float']);
    }

    public function testFindNoArtefact()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/find/coins/456');

        $this->assertTrue($client->getResponse()->isOk());
        $json = json_decode($client->getResponse()->getContent(), true);
        $this->assertInternalType('array', $json);
        $this->assertEquals(0, count($json));
    }

    public function testFindNothingForWrongType()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/find/noFooBar/123');
        $this->assertTrue($client->getResponse()->isClientError());
    }

    public function testFindAllForTypeWithPOST()
    {
        $client = $this->createClient();
        $client->request('POST', '/api/findAll/coins');

        $this->assertTrue($client->getResponse()->isOk());
        $json = json_decode($client->getResponse()->getContent(), true);
        $this->assertInternalType('array', $json);
        $this->assertEquals(2, count($json));

        $this->assertArrayHasKey('title', $json[0]);
        $this->assertEquals('test', $json[0]['title']);
        $this->assertArrayHasKey('inventory', $json[0]);
        $this->assertEquals('123', $json[0]['inventory']);
        $this->assertArrayHasKey('other', $json[0]);
        $this->assertEquals('value', $json[0]['other']);

        $this->assertArrayHasKey('title', $json[1]);
        $this->assertEquals('bar', $json[1]['title']);
        $this->assertArrayHasKey('inventory', $json[1]);
        $this->assertEquals('456', $json[1]['inventory']);
        $this->assertArrayHasKey('other', $json[1]);
        $this->assertEquals('foo bar 123', $json[1]['other']);
    }

    public function testFindAllForTypeWithGET()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/findAll/coins');

        $this->assertTrue($client->getResponse()->isOk());
        $json = json_decode($client->getResponse()->getContent(), true);
        $this->assertInternalType('array', $json);
        $this->assertEquals(2, count($json));

        $this->assertArrayHasKey('title', $json[0]);
        $this->assertEquals('test', $json[0]['title']);
        $this->assertArrayHasKey('inventory', $json[0]);
        $this->assertEquals('123', $json[0]['inventory']);
        $this->assertArrayHasKey('other', $json[0]);
        $this->assertEquals('value', $json[0]['other']);

        $this->assertArrayHasKey('title', $json[1]);
        $this->assertEquals('bar', $json[1]['title']);
        $this->assertArrayHasKey('inventory', $json[1]);
        $this->assertEquals('456', $json[1]['inventory']);
        $this->assertArrayHasKey('other', $json[1]);
        $this->assertEquals('foo bar 123', $json[1]['other']);
    }

    public function testFindNothingForWrongType1()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/findAll/noFooBar');
        $this->assertTrue($client->getResponse()->isClientError());
    }

    /**
     * Creates the application.
     *
     * @return HttpKernel
     */
    public function createApplication()
    {
        ob_start();
        require __DIR__ . '/../../web/api.php';
        ob_clean();

        /** @var Application $app */
        $app['debug'] = false;
        $app['exception_handler']->disable();
        $app['security.active']               = true;
        $app['parameters.file.configuration'] = __DIR__ . '/../config/configuration.yml';

        return $app;
    }

    /**
     * Remove the security config file, so that security active is set to false
     */
    public static function setUpBeforeClass()
    {
        if (file_exists(__DIR__ . '/../../config/security.yml') == true) {
            rename(__DIR__ . '/../../config/security.yml', __DIR__ . '/../../config/security.yml.test');
        }
    }

    /**
     * Restore security file
     */
    public static function tearDownAfterClass()
    {
        if (file_exists(__DIR__ . '/../../config/security.yml.test') == true) {
            rename(__DIR__ . '/../../config/security.yml.test', __DIR__ . '/../../config/security.yml');
        }
    }


}
 