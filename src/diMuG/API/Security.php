<?php
namespace diMuG\API;

use diMuG\API\Interfaces\SecurityInterface;
use Silex\Application;
use Silex\Provider\SecurityServiceProvider;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Security.php.
 *
 * @author  Benjamin Geißler <benjamin.geissler@gmail.com>
 * @package diMuG
 * @licence GPL v3
 */
class Security implements SecurityInterface
{
    /**
     * Parses a yaml configuration file with the user name and password to access this API and creates a corresponding
     * firewall. Returns true if firewall is created.
     *
     * @param Application $app
     * @param             $file
     * @return bool
     */
    public static function addFirewall(Application $app, $file)
    {
        if (file_exists($file) == true) {
            try {
                $security = Yaml::parse($file);

                if (array_key_exists('api', $security) == true
                    && array_key_exists('user', $security['api']) == true
                    && array_key_exists('password', $security['api']) == true
                ) {
                    $app->register(
                        new SecurityServiceProvider(),
                        array(
                            'security.firewalls' => array(
                                'api' => array(
                                    'pattern' => '^/api',
                                    'http'    => true,
                                    'users'   => array(
                                        $security['api']['user'] => array('ROLE_USER', $security['api']['password'])
                                    ),
                                )
                            )
                        )
                    );

                    $app->boot();
                    return true;
                }
            } catch (ParseException $error) {
                return false;
            }
        }

        return false;
    }
}
