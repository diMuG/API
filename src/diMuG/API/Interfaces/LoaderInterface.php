<?php
namespace diMuG\API\Interfaces;

/**
 * Interface to retrieve configuration information's for a given type or create the necessary classes.
 *
 * @author  Benjamin Geißler <benjamin.geissler@gmail.com>
 * @package diMuG\API\Interfaces
 * @licence GPL v3
 */
interface LoaderInterface
{
    /**
     * Load and return an instance of the finder of the given type.
     *
     * @param $type
     * @return FinderInterface
     * @throws \ErrorException
     */
    public function loadFinder($type);

    /**
     * Return the field configuration for the given type.
     *
     * @param $type
     * @return array
     */
    public function loadFields($type);

    /**
     * Return the configuration to access and retrieve the pictures associated with the given type.
     *
     * @param string $type
     * @return array
     */
    public function loadPictureConfiguration($type);

    /**
     * Return the optional configuration to access and retrieve the sound files for the audio guide associated with
     * the given type.
     *
     * @param string $type
     * @return array
     * @throws \ErrorException When the type is missing
     */
    public function loadSoundConfiguration($type);

    /**
     * Return all glossary entries for the given type.
     *
     * @param $type
     * @return array
     * @throws \ErrorException
     */
    public function loadGlossary($type);
}
 