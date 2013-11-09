<?php
namespace diMuG\API;

use diMuG\API\Interfaces\FactoryInterface;
use diMuG\API\Interfaces\FinderInterface;
use diMuG\API\Interfaces\GlossaryInterface;

/**
 * Simple factory to create the necessary FinderInterface and GlossaryInterface instances.
 *
 * @author  Benjamin Geißler <benjamin.geissler@gmail.com>
 * @package diMuG\API
 * @licence GPL v3
 */
class Factory implements FactoryInterface
{
    /**
     * Create a instance of a FinderInterface or a GlossaryInterface.
     *
     * @param array $config Part of the configuration array containing the class name
     * @return FinderInterface|GlossaryInterface
     * @throws \ErrorException
     */
    public function create(array $config)
    {
        if (isset($config['class']) == true) {
            $class = $this->createClass($config['class']);

            if (isset($config['factory']) == false
                || $config['factory'] === false) {
                return $class;
            }

            $this->hasMethod($class, $config);

            $method = $config['method'];
            if (isset($config['static']) == true
                && $config['static'] === true) {
                return $class::$method();
            }

            return $class->$method();
        }

        throw new \ErrorException('Missing class definition for creator!');
    }

    /**
     * Creates an instance of the given class, if the class exists.
     *
     * @param string $class
     * @return object
     * @throws \ErrorException When the class is missing
     */
    private function createClass($class)
    {
        if (class_exists($class) === true) {
            return new $class();
        }

        throw new \ErrorException('The class "' . $class  .'" could not be found!');
    }

    /**
     * Check if the method in the factory is given and existing.
     *
     * @param       $class
     * @param array $config
     * @return bool
     * @throws \ErrorException
     */
    private function hasMethod($class, array $config)
    {
        if (isset($config['method']) == false) {
            throw new \ErrorException('Missing method definition for factory (' . $config['class'] . ')');
        } elseif (method_exists($class, $config['method']) == false) {
            throw new \ErrorException(
                'Method "' . $config['method'] . '" is not defined in class "' . $config['class'] . '"'
            );
        }

        return true;
    }
}
 