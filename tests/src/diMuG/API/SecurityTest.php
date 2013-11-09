<?php
/**
 * SecurityTest.php.
 *
 * @author     Benjamin Geißler <benjamin.geissler@gmail.com>
 * @package    diMuG\API
 */
namespace diMuG\API;

use Silex\Application;

class SecurityTest extends \PHPUnit_Framework_TestCase
{
    public function testAddFirewall()
    {
        $file = __DIR__ . '/../../../config/security.yml';
        $this->assertTrue(Security::addFirewall(new Application(), $file));
    }

    public function testDoNotAddFirewall()
    {
        $this->assertFalse(Security::addFirewall(new Application(), ''));
    }

    public function testDoNotAddFirewall1()
    {
        $file = __DIR__ . '/../../../config/error.yml';
        $this->assertFalse(Security::addFirewall(new Application(), $file));
    }
}
 