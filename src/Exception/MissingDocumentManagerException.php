<?php

namespace Ellinaut\Exception;

/**
 * @author Philipp Marien <philipp@ellinaut.dev>
 */
class MissingDocumentManagerException extends \RuntimeException
{
    /**
     * MissingIndexManagerException constructor.
     * @param string $internalIndexName
     */
    public function __construct(string $internalIndexName)
    {
        parent::__construct('No document manager found for index "' . $internalIndexName . '".', 220120210002);
    }
}
