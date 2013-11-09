<?php
namespace diMuG\Test;

use diMuG\API\Interfaces\GlossaryInterface;

/**
 * GlossaryMock.php.
 *
 * @author     Benjamin Geißler <benjamin.geissler@gmail.com>
 * @package    diMuG\Test
 */
class GlossaryMock implements GlossaryInterface
{
    /**
     * Retrieve all entries of your glossary. The result must be an array of arrays where each entry has the following
     * keys:
     *  title (= heading to be displayed; content must be of type string)
     *  match (= entries for which the glossary entry should be displayed; content must be an array of strings)
     *  content (= description of the entry; content must be of type string)
     *
     * @return array
     */
    public function findAll()
    {
        return array(
            array(
                'title'   => 'FooBar',
                'match'   => array('foo bar', 'foobar'),
                'content' => 'me foo bar!'
            ),
            array(
                'title'   => 'Foo',
                'match'   => array('foo'),
                'content' => 'me foo bar!'
            )
        );
    }
}
 