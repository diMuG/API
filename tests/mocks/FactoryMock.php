<?php
namespace diMuG\Test;

/**
 * FactoryMock.php.
 *
 * @author     Benjamin Geißler <benjamin.geissler@gmail.com>
 * @package    diMuG\Test
 */
class FactoryMock
{
    public function getFinder()
    {
        return new FinderMock();
    }

    public static function createFinder()
    {
        return new FinderMock();
    }
}
 