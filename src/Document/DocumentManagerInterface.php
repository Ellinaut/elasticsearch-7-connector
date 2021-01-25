<?php

namespace Ellinaut\ElasticsearchConnector\Document;

/**
 * @author Philipp Marien <philipp@ellinaut.dev>
 */
interface DocumentManagerInterface
{
    /**
     * @param object $object
     * @return array
     */
    public function buildSourceFromObject(object $object): array;

    /***
     * @param string $id
     * @param array $source
     * @return object
     */
    public function buildObjectFromSource(string $id, array $source): object;

    /**
     * @param array $previousSource
     * @return array
     */
    public function migrateSourceFromPreviousSource(array $previousSource): array;
}
