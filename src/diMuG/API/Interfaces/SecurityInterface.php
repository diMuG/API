<?php
namespace diMuG\API\Interfaces;

use Silex\Application;

/**
 * SecurityInterface.php.
 *
 * @author  Benjamin Geißler <benjamin.geissler@gmail.com>
 * @package diMuG
 * @licence GPL v3
 */
interface SecurityInterface
{
    /**
     * Parses a yaml configuration file with the user name and password to access this API and creates a corresponding
     * firewall. Returns true if firewall is created.
     *
     * @param Application $app
     * @param             $file
     * @return bool
     */
    public static function addFirewall(Application $app, $file);
}
