<?php
namespace diMuG\API;

use diMuG\API\Interfaces\FactoryInterface;
use diMuG\API\Interfaces\FinderInterface;
use diMuG\API\Interfaces\GlossaryInterface;
use diMuG\API\Interfaces\LoaderInterface;

/**
 * Retrieve configuration information's for a given type or create the necessary classes.
 *
 * @author  Benjamin Geißler <benjamin.geissler@gmail.com>
 * @package diMuG\API
 * @licence GPL v3
 */
class Loader implements LoaderInterface
{
    /** @var array */
    private $configuration;
    /** @var Factory */
    private $factory;

    /**
     * Inject the factory to create the necessary instances and the configuration array.
     *
     * @param Factory $factory
     * @param array   $configuration
     */
    public function __construct(FactoryInterface $factory, array $configuration)
    {
        $this->configuration = $configuration;
        $this->factory       = $factory;
    }

    /**
     * Load and return an instance of the finder of the given type.
     *
     * @param $type
     * @return FinderInterface
     * @throws \ErrorException
     */
    public function loadFinder($type)
    {
        /** @var \diMuG\API\Interfaces\FinderInterface $finder */
        $finder = $this->factory->create($this->load($type, 'finder'));

        if (($finder instanceof FinderInterface) == true) {
            return $finder;
        }

        throw new \ErrorException('errors.finder.interface');
    }

    /**
     * Return the field configuration for the given type.
     *
     * @param $type
     * @return array
     */
    public function loadFields($type)
    {
        return $this->load($type, 'fields');
    }

    /**
     * Return the configuration to access and retrieve the pictures associated with the given type.
     *
     * @param string $type
     * @return array
     */
    public function loadPictureConfiguration($type)
    {
        return $this->load($type, 'pictures');
    }

    /**
     * Return the optional configuration to access and retrieve the sound files for the audio guide associated with
     * the given type.
     *
     * @param string $type
     * @return array
     * @throws \ErrorException When the type is missing
     */
    public function loadSoundConfiguration($type)
    {
        try {
            return $this->load($type, 'sounds');
        } catch (\ErrorException $error) {
            if ($error->getMessage() == 'errors.fields.missing') {
                return array();
            }

            throw new \ErrorException($error->getMessage());
        }
    }

    /**
     * Return all glossary entries for the given type.
     *
     * @param $type
     * @return array
     * @throws \ErrorException
     */
    public function loadGlossary($type)
    {
        /** @var \diMuG\API\Interfaces\GlossaryInterface $glossary */
        $glossary = $this->factory->create($this->load($type, 'glossary'));

        if (($glossary instanceof GlossaryInterface) == true) {
            return $glossary->findAll();
        }

        throw new \ErrorException('errors.glossary.interface');
    }

    /**
     * Retrieve the requested configuration section for a given type from the configuration file.
     *
     * @param string $type
     * @param string $option
     * @return array
     * @throws \ErrorException
     */
    private function load($type, $option)
    {
        if (array_key_exists($type, $this->configuration['types']) == true) {
            if (array_key_exists($option, $this->configuration['types'][$type]) == true) {
                return $this->configuration['types'][$type][$option];
            }

            throw new \ErrorException('errors.fields.missing');
        }

        throw new \ErrorException('errors.type.missing');
    }
}
