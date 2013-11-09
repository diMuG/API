<?php
namespace diMuG\API;

/**
 * Installer.php.
 *
 * @author  Benjamin Geißler <benjamin.geissler@gmail.com>
 * @package diMuG\API\Commands
 * @licence GPL v3
 */
class Installer
{
    /**
     * Copy the skeleton files for a standard diMuG API implementation to the project root.
     */
    public static function initAPI()
    {
        if (file_exists(__DIR__ . '/composer.json') == true) {
            self::createDir('app');
            self::copyFiles('/app', '/app', array('console'));

            self::createDir('config');
            self::copyFiles('/config', '/config', array('configuration.yml', 'security.yml'));

            self::createDir('locales');
            self::copyFiles('/locales', '/locales', array('en.yml', 'de.yml'));

            self::createDir('log');

            self::createDir('tests');
            self::copyFiles('/tests/skeleton', '/tests', array('FinderInterfaceTest.php', 'GlossaryInterfaceTest.php'));

            self::createDir('web');
            self::copyFiles('/web', '/web', array('api.php'));
        }
    }

    /**
     * Create a dir, if not already existing.
     * @param $name
     */
    private static function createDir($name)
    {
        $dir = __DIR__ . '/' . $name;
        if (file_exists($dir) == false
            || is_dir($dir) == false) {
            mkdir($dir);
        }
    }

    /**
     * Copy the basic files to implement the diMuG API from the vendor dir to the main project dir.
     *
     * @param       $source
     * @param       $target
     * @param array $files
     */
    private static function copyFiles($source, $target, array $files)
    {
        $source = __DIR__ . '/vendor/dimug/api' .  $source;
        $target = __DIR__. $target;
        if (file_exists($source) == true
            && is_dir($source) == true
            && file_exists($target) == true
            && is_dir($target) == true) {
            foreach ($files as $file) {
                if (file_exists($source . '/' . $file) == true
                    && file_exists($target . '/' . $file) == false) {
                    copy($source . '/' . $file, $target . '/' . $file);
                } else {
                    echo 'Could not copy "' . $source . '/' . $file . '" to "' . $target . '/' . $file . '"!';
                }
            }
        }
    }
}
