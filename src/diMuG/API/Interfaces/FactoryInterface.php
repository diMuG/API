<?php
namespace diMuG\API\Interfaces;

/**
 * Interface to create the necessary FinderInterface and GlossaryInterface instances.
 *
 * @author  Benjamin Geißler <benjamin.geissler@gmail.com>
 * @package diMuG\API\Interfaces
 * @licence GPL v3
 */
interface FactoryInterface
{
    /**
     * Create a instance of a FinderInterface or a GlossaryInterface.
     *
     * @param array $config Part of the configuration array containing the class name
     * @return FinderInterface|GlossaryInterface
     * @throws \ErrorException
     */
    public function create(array $config);
}
 