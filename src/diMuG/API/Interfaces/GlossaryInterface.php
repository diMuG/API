<?php
namespace diMuG\API\Interfaces;

/**
 * Interface to retrieve all entries in a glossary for a given type of artefact.
 *
 * @author  Benjamin Geißler <benjamin.geissler@gmail.com>
 * @package diMuG\API\Interfaces
 * @licence GPL v3
 */
interface GlossaryInterface
{
    /**
     * Retrieve all entries of your glossary. The result must be an array of arrays where each entry has the following
     * keys:
     *  title (= heading to be displayed; content must be of type string)
     *  match (= entries for which the glossary entry should be displayed; content must be an array of strings)
     *  content (= description of the entry; content must be of type string)
     *
     * @return array
     */
    public function findAll();
}
