<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousImportDataConverting\Model\RuleApplyingStrategy;

use Magento\AsynchronousImportDataConvertingApi\Api\Data\ConvertingRuleInterface;
use Magento\AsynchronousImportDataConvertingApi\Model\ApplyConvertingRuleStrategyInterface;

/**
 * Converts "yes"/"no" to the boolean equivalent
 */
class YesNoToBool implements ApplyConvertingRuleStrategyInterface
{
    /**
     * Maps values to their boolean equivalents
     *
     * @var array
     */
    private $conversionMap = [];

    /**
     * @param array $conversionMap
     */
    public function __construct(array $conversionMap)
    {
        $this->conversionMap = $conversionMap;
    }

    /**
     * @inheritdoc
     */
    public function execute(
        array $importData,
        ConvertingRuleInterface $convertingRule
    ): array {
        $applyTo = $convertingRule->getApplyTo();

        foreach ($importData as &$row) {
            foreach ($applyTo as $columnName) {
                if (isset($row[$columnName])) {
                    $row[$columnName] = $this->conversionMap[strtolower($row[$columnName])] ?? $row[$columnName];
                }
            }
        }
        unset($row);

        return $importData;
    }
}
