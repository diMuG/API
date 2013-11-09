<?php
/**
 * LoaderTest.php.
 *
 * @author     Benjamin Geißler <benjamin.geissler@gmail.com>
 * @package    diMuG\API
 */
namespace diMuG\API;

use diMuG\Test\GlossaryMock;

class LoaderTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \diMuG\API\Loader */
    protected $object;

    protected function setUp(array $config = array())
    {
        $this->object   =   new Loader(new Factory(), $config);
    }

    public function testLoadFinder()
    {
        $this->setUp(
            array(
                'types' => array(
                'foo' => array(
                    'finder' => array(
                        'class' => '\diMuG\Test\FinderMock'
                    )
                )
                )
            )
        );
        $this->assertInstanceOf('\diMuG\Test\FinderMock', $this->object->loadFinder('foo'));
    }

    public function testDoNotLoadFinder()
    {
        $this->setExpectedException('ErrorException');
        $this->setUp(
            array(
                'types' => array(
                    'foo' => array(
                        'finder' => array(
                            'class' => '\diMuG\Test\GlossaryMock'
                        )
                    )
                )
            )
        );
        $this->assertInstanceOf('\diMuG\API\Interfaces\FinderInterface', $this->object->loadFinder('foo'));
    }

    public function testDoNotLoadFinder1()
    {
        $this->setExpectedException('ErrorException');
        $this->setUp(
            array(
                'types' => array(
                    'foo' => array(
                        'finder' => array(
                            'class' => '\diMuG\Test\FinderMock'
                        )
                    )
                )
            )
        );
        $this->assertInstanceOf('\diMuG\API\Interfaces\FinderInterface', $this->object->loadFinder('bar'));
    }

    public function testDoNotLoadFinder2()
    {
        $this->setExpectedException('ErrorException');
        $this->setUp(
            array(
                'types' => array(
                    'foo' => array(
                    )
                )
            )
        );
        $this->assertInstanceOf('\diMuG\API\Interfaces\FinderInterface', $this->object->loadFinder('foo'));
    }

    public function testLoadFields()
    {
        $fields = array(
            'first' => array(
                'type' => 'string',
                'label' => 'First Field',
                'nullable' => true
            ),
            'second' => array(
                'type' => 'integer',
                'label' => 'Second Field'
            )
        );
        $this->setUp(
            array(
                'types' => array(
                    'foo' => array(
                        'fields' => $fields
                    )
                )
            )
        );

        $this->assertInternalType('array', $this->object->loadFields('foo'));
        $this->assertEquals($fields, $this->object->loadFields('foo'));
    }

    public function testLoadPictureConfiguration()
    {
        $this->setUp(
            array(
                'types' => array(
                    'foo' => array(
                        'pictures' => array(
                            'online'   => true,
                            'url'      => 'http://www.images.com/pictures',
                            'security' => true,
                            'user'     => 'user',
                            'password' => 'password123'
                        )
                    )
                )
            )
        );

        $pictures = $this->object->loadPictureConfiguration('foo');
        $this->assertInternalType('array', $pictures);
        $this->assertArrayHasKey('online', $pictures);
        $this->assertArrayHasKey('url', $pictures);
        $this->assertArrayHasKey('security', $pictures);
        $this->assertArrayHasKey('user', $pictures);
        $this->assertArrayHasKey('password', $pictures);
        $this->assertTrue($pictures['online']);
        $this->assertTrue($pictures['security']);
        $this->assertEquals('http://www.images.com/pictures', $pictures['url']);
        $this->assertEquals('user', $pictures['user']);
        $this->assertEquals('password123', $pictures['password']);
    }

    public function testDoNotLoadPictureConfiguration()
    {
        $this->setUp(
            array(
                'types' => array(
                    'foo' => array(
                    )
                )
            )
        );
        $this->setExpectedException('ErrorException');
        $this->object->loadPictureConfiguration('foo');
    }

    public function testLoadSound()
    {
        $this->setUp(
            array(
                'types' => array(
                    'foo' => array(
                        'sounds' => array(
                            'online'   => true,
                            'url'      => 'test',
                            'security' => true,
                            'user'     => 'user',
                            'password' => 'password123'
                        )
                    )
                )
            )
        );

        $sounds = $this->object->loadSoundConfiguration('foo');
        $this->assertInternalType('array', $sounds);
        $this->assertArrayHasKey('online', $sounds);
        $this->assertArrayHasKey('url', $sounds);
        $this->assertArrayHasKey('security', $sounds);
        $this->assertArrayHasKey('user', $sounds);
        $this->assertArrayHasKey('password', $sounds);
        $this->assertTrue($sounds['online']);
        $this->assertTrue($sounds['security']);
        $this->assertEquals('test', $sounds['url']);
        $this->assertEquals('user', $sounds['user']);
        $this->assertEquals('password123', $sounds['password']);
    }

    public function testLoadNoSound()
    {
        $this->setUp(
            array(
                'types' => array(
                    'foo' => array(
                    )
                )
            )
        );

        $sounds = $this->object->loadSoundConfiguration('foo');
        $this->assertInternalType('array', $sounds);
        $this->assertEquals(0, count($sounds));
    }

    public function testLoadNoSoundFromMissingType()
    {
        $this->setExpectedException('ErrorException');
        $this->setUp(
            array(
                'types' => array(
                    'foo' => array(
                    )
                )
            )
        );

        $sounds = $this->object->loadSoundConfiguration('bar');
    }

    public function testLoadGlossary()
    {
        $this->setUp(
            array(
                'types' => array(
                    'foo' => array(
                        'glossary' => array(
                            'class' => '\diMuG\Test\GlossaryMock'
                        )
                    )
                )
            )
        );
        $this->assertInternalType('array', $this->object->loadGlossary('foo'));
        $glossary = new GlossaryMock();
        $this->assertEquals($glossary->findAll(), $this->object->loadGlossary('foo'));
    }

    public function testDoNotLoadGlossary()
    {
        $this->setExpectedException('ErrorException');
        $this->setUp(
            array(
                'types' => array(
                    'foo' => array(
                        'glossary' => array(
                            'class' => '\diMuG\Test\FinderMock'
                        )
                    )
                )
            )
        );
        $this->assertInternalType('array', $this->object->loadGlossary('foo'));
    }
}
 