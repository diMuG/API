<?php
namespace diMuG\Test;

use diMuG\API\Interfaces\FinderInterface;

/**
 * FinderMock.php.
 *
 * @author     Benjamin Geißler <benjamin.geissler@gmail.com>
 * @package    diMuG\Test
 */
class FinderMock implements FinderInterface
{

    /**
     * @param string $inventory
     * @return array
     */
    public function findOne($inventory)
    {
        if ($inventory == '123') {
            return array(
                'title' => 'test',
                'inventory' => '123',
                'other' => 'value',
                'more' => '-123',
                'yes' => true,
                'float' => 1.2
            );
        } else {
            return array();
        }
    }

    /**
     * @return array
     */
    public function findAll()
    {
        return
            array(
                array(
                    'title'     => 'test',
                    'inventory' => '123',
                    'other'     => 'value'
                ),
                array(
                    'title'     => 'bar',
                    'inventory' => '456',
                    'other'     => 'foo bar 123'
                )
            );
    }
}
 