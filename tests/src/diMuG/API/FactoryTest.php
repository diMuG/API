<?php
/**
 * FactoryTest.php.
 *
 * @author     Benjamin Geißler <benjamin.geissler@gmail.com>
 * @package    diMuG\API
 */
namespace diMuG\API;


class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \diMuG\API\Factory */
    protected $object;

    protected function setUp()
    {
        $this->object    = new Factory();
    }

    /**
     * @param array $config
     * @param       $class
     * @dataProvider dataProviderForCreate
     */
    public function testCreate(array $config, $class)
    {
        $this->assertInstanceOf($class, $this->object->create($config));
    }

    /**
     * @param $config
     * @dataProvider dataProviderForDoNotCreate
     */
    public function testDoNotCreate($config)
    {
        $this->setExpectedException('ErrorException');
        $this->object->create($config);
    }

    public function dataProviderForCreate()
    {
        return array(
            array(
                array(
                    'class' => '\diMuG\Test\FinderMock'
                ),
                '\diMuG\Test\FinderMock'
            ),
            array(
                array(
                    'class'   => '\diMuG\Test\FinderMock',
                    'factory' => false
                ),
                '\diMuG\Test\FinderMock'
            ),
            array(
                array(
                    'class'   => '\diMuG\Test\FactoryMock',
                    'factory' => true,
                    'method'  => 'getFinder'
                ),
                '\diMuG\Test\FinderMock'
            ),
            array(
                array(
                    'class'   => '\diMuG\Test\FactoryMock',
                    'factory' => true,
                    'static'  => false,
                    'method'  => 'getFinder'
                ),
                '\diMuG\Test\FinderMock'
            ),
            array(
                array(
                    'class'   => '\diMuG\Test\FactoryMock',
                    'factory' => true,
                    'static'  => true,
                    'method'  => 'createFinder'
                ),
                '\diMuG\Test\FinderMock'
            )
        );
    }

    public function dataProviderForDoNotCreate()
    {
        return array(
            array(
                array()
            ),
            array(
                array(
                    'class' => 'NotThereClass'
                )
            ),
            array(
                array(
                    'class'   => '\diMuG\Test\FactoryMock',
                    'factory' => true,
                    'method'  => 'notThereMethod'
                )
            ),
            array(
                array(
                    'class'   => '\diMuG\Test\FactoryMock',
                    'factory' => true
                )
            )
        );
    }
}
 