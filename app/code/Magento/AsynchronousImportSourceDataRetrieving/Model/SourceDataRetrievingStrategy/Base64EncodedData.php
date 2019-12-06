<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousImportSourceDataRetrieving\Model\SourceDataRetrievingStrategy;

use Magento\AsynchronousImportSourceDataRetrievingApi\Api\Data\SourceInterface;
use Magento\AsynchronousImportSourceDataRetrievingApi\Model\RetrieveSourceDataStrategyInterface;

/**
 * Base64 encoded data strategy for retrieving source data
 */
class Base64EncodedData implements RetrieveSourceDataStrategyInterface
{

    public const BASE64_STRATEGY_NAME = "base64_encoded_data";

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var string
     */
    private $dataSeparator = "\n";

    /**
     * @param int $batchSize
     * @param string|null $dataSeparator
     */
    public function __construct(
        $batchSize,
        $dataSeparator = null
    ) {
        $this->batchSize = $batchSize;
        if ($dataSeparator !== null) {
            $this->dataSeparator = $dataSeparator;
        }
    }

    /**
     * @inheritdoc
     */
    public function execute(SourceInterface $source): \Traversable
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $data = base64_decode($source->getSourceDefinition());
        $data = explode($this->dataSeparator, $data);

        $resultData = [];
        $offset = 0;
        while ($sub = array_slice($data, $offset, $this->batchSize)) {
            $offset += $this->batchSize;
            $resultData[] = $sub;
        }

        return new \ArrayIterator($resultData);
    }
}
