<?php

namespace Ellinaut\ElasticsearchConnector\IndexName;

/**
 * @author Philipp Marien <philipp@ellinaut.dev>
 */
class ChainedIndexNameProvider implements IndexNameProviderInterface
{
    /**
     * @var IndexNameProviderInterface[]
     */
    private $indexNameProviderChain;

    /**
     * ChainedIndexNameProvider constructor.
     * @param IndexNameProviderInterface[] $indexNameProviderChain
     */
    public function __construct(array $indexNameProviderChain)
    {
        $this->indexNameProviderChain = $indexNameProviderChain;
    }

    /**
     * @param string $internalIndexName
     * @return string
     */
    public function getExternalIndexName(string $internalIndexName): string
    {
        $externalName = $internalIndexName;
        foreach ($this->indexNameProviderChain as $indexNameProvider) {
            $externalName = $indexNameProvider->getExternalIndexName($externalName);
        }

        return $externalName;
    }

    /**
     * @param string $externalIndexName
     * @return string
     */
    public function getInternalIndexName(string $externalIndexName): string
    {
        $internalName = $externalIndexName;
        foreach ($this->indexNameProviderChain as $indexNameProvider) {
            $internalName = $indexNameProvider->getExternalIndexName($internalName);
        }

        return $internalName;
    }
}
