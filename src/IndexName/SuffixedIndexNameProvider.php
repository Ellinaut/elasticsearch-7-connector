<?php

namespace Ellinaut\IndexName;

/**
 * @author Philipp Marien <philipp@ellinaut.dev>
 */
class SuffixedIndexNameProvider implements IndexNameProviderInterface
{
    /**
     * @var string
     */
    private $suffix;

    /**
     * SuffixedIndexNameProvider constructor.
     * @param string $suffix
     */
    public function __construct(string $suffix)
    {
        $this->suffix = $suffix;
    }

    /**
     * @param string $internalIndexName
     * @return string
     */
    public function getExternalIndexName(string $internalIndexName): string
    {
        return $internalIndexName . $this->suffix;
    }

    /**
     * @param string $externalIndexName
     * @return string
     */
    public function getInternalIndexName(string $externalIndexName): string
    {
        return substr($externalIndexName, 0, strlen($externalIndexName) - strlen($this->suffix));
    }
}
