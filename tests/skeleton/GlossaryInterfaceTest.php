<?php
/**
 * Skeleton to test your implementation of the GlossaryInterface
 *
 * @author     Benjamin Geißler <benjamin.geissler@gmail.com>
 * @package    diMuG\API
 */
// TODO: replace with your namespace
namespace diMuG\API;

class GlossaryInterfaceTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \diMuG\API\Interfaces\GlossaryInterface */
    protected $object;

    protected function setUp()
    {
        // TODO: create instance of your one glossary class
        //$this->object   =   new Glossary;
    }

    /**
     * Sample data's
     * @return array
     */
    public function dataProviderForGlossaryTest()
    {
        // TODO: replace this with our own test data
        return array(
            array('title', array('match'), 'content')
        );
    }

    /**
     * Test if the glossary returns the expected results
     * @param       $title
     * @param array $match
     * @param       $content
     * @dataProvider dataProviderForGlossaryTest
     */
    public function testFindAllGlossaryEntries($title, array $match, $content)
    {
        $this->assertInstanceOf('\diMuG\API\Interfaces\GlossaryInterface', $this->object);
        $result = $this->object->findAll();
        $this->assertInternalType('array', $result, 'FindAll result must be of type "array"!');
        $length = count($result);
        $this->assertGreaterThan(0, $length, 'FindAll did not return a result!');

        $exists = false;
        for ($i = 0; $i < $length; $i++) {
            $this->assertArrayHasKey('title', $result[$i], 'Field "title" is missing!');
            $this->assertArrayHasKey('match', $result[$i], 'Field "match" is missing!');
            $this->assertInternalType('array', $result[$i]['match'], 'Field "match" must be of type "array"');
            $this->assertArrayHasKey('content', $result[$i], 'Field "content" is missing!');

            if ($result[$i]['title'] == $title) {
                $this->assertEquals($match, $result[$i]['match'], 'Field "match" does not match!');
                $this->assertEquals($content, $result[$i]['content'], 'Field "content" does not match!');
                $exists = true;
            }
        }

        $this->assertTrue($exists, 'No entry with title "' . $title . '" found!');
    }
}
 