<?php
/**
 * FinderInterfaceTest.php.
 *
 * @author     Benjamin Geißler <benjamin.geissler@gmail.com>
 * @package    diMuG\API
 */
// TODO: replace with your own namespace
namespace diMuG\API;


class FinderInterfaceTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \diMuG\API\Interfaces\FinderInterface */
    protected $object;

    protected function setUp()
    {
        // TODO: replace with your Finder class
        // $this->object = new Finder();
    }

    // TODO: add your own result data
    public function dataProviderForFindOneByInventory()
    {
        return array(
            array('inventory', 'title', array('other' => 'fields', 'foo' => true))
        );
    }

    // TODO: add your own result data
    public function dataProviderForFindNothing()
    {
        return array(
            array('wrong inventory')
        );
    }

    // TODO: add your own result data
    public function dataProviderForFindAll()
    {
        return array(
            array(
                'inventory number' => array(
                    'inventory' => 'inventory number',
                    'title'     => 'test',
                    'other'     => 'fields'
                ),
                'next inventory'   => array(
                    'inventory' => 'next inventory',
                    'title'     => 'foo bar 2000',
                    'other'     => 'fields'
                )
            )
        );
    }

    /**
     * @param       $inventory
     * @param       $title
     * @param array $result
     * @dataProvider dataProviderForFindOneByInventory
     */
    public function testFindOneByInventory($inventory, $title, array $result)
    {
        $artefact = $this->object->findOne($inventory);
        $this->assertInternalType('array', $artefact);
        $this->assertArrayHasKey('title', $artefact, 'Field "title" is missing!');
        $this->assertEquals($title, $artefact['title']);
        $this->assertArrayHasKey('inventory', $artefact, 'Field "inventory" is missing!');
        $this->assertEquals($inventory, $artefact['inventory']);

        foreach ($result as $field => $value) {
            $this->assertArrayHasKey($field, $artefact, 'Field "' . $field. '" is missing!');
            $this->assertEquals($value, $artefact[$field]);
        }
    }

    /**
     * @param $inventory
     * @dataProvider dataProviderForFindNothing
     */
    public function testFindNothing($inventory)
    {
        $artefact = $this->object->findOne($inventory);
        $this->assertInternalType('array', $artefact);
        $this->assertEquals(0, count($artefact));
    }

    /**
     * @param array $data
     * @dataProvider dataProviderForFindAll
     */
    public function testFindAll(array $data)
    {
        $result = $this->object->findAll();
        $this->assertInternalType('array', $result);

        foreach ($result as $artefact) {
            $this->assertArrayHasKey('inventory', $artefact, 'Field "inventory" is missing!');
            $this->assertArrayHasKey($artefact['inventory'], $data);
            $this->assertArrayHasKey('title', $artefact, 'Field "title" is missing!');

            foreach ($data[$artefact['inventory']] as $field => $value) {
                $this->assertArrayHasKey($field, $artefact, 'Field "' . $field. '" is missing!');
                $this->assertEquals($value, $artefact[$field]);
            }
        }
    }
}
 