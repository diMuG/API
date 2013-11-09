<?php
namespace diMuG\API\Interfaces;

/**
 * The FinderInterface which MUST be implemented to find your artefact's.
 *
 * @author  Benjamin Geißler <benjamin.geissler@gmail.com>
 * @package diMuG\API\Interfaces
 * @licence GPL v3
 */
interface FinderInterface
{
    /**
     * Find an artefact by its inventory number. The resulting array MUST contain at least the fields "title" and
     * "inventory". The other returned fields should be declared in your configuration file. If NO artefact is found,
     * return an empty array!
     *
     * Example:
     *  array(
     *      'title' => 'Statue',
     *      'inventory' => '123',
     *      'material' => 'wood',
     *      ...
     * )
     *
     * @param string $inventory
     * @return array
     */
    public function findOne($inventory);

    /**
     * Return all your artefact's as an array of arrays. For the fields each artefact array must implement see findOne.
     * Example:
     *  array(
     *      array(
     *          'title' => 'Statue',
     *          'inventory' => '123',
     *          'material' => 'wood',
     *          ...
     *      ),
     *      array(
     *          'title' => 'Statue',
     *          'inventory' => '456',
     *          'material' => 'gold; silver',
     *          ...
     *      )
     * )
     *
     * @return array
     */
    public function findAll();
}
 