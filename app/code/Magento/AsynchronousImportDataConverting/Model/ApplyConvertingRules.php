<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousImportDataConverting\Model;

use Magento\AsynchronousImportDataConvertingApi\Api\ApplyConvertingRulesException;
use Magento\AsynchronousImportDataConvertingApi\Api\ApplyConvertingRulesInterface;
use Magento\AsynchronousImportDataConvertingApi\Model\ApplyConvertingRuleStrategyInterface;
use Magento\AsynchronousImportDataConvertingApi\Model\ConvertingRuleValidatorInterface;
use Magento\Framework\Validation\ValidationException;
use Magento\Framework\Validation\ValidationResultFactory;

/**
 * @inheritdoc
 */
class ApplyConvertingRules implements ApplyConvertingRulesInterface
{
    /**
     * @var ConvertingRuleValidatorInterface
     */
    private $convertingRuleValidator;

    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @var ApplyConvertingRuleStrategyInterface[]
     */
    private $ruleApplyingStrategies;

    /**
     * @param ConvertingRuleValidatorInterface $convertingRuleValidator
     * @param ValidationResultFactory $validationResultFactory
     * @param ApplyConvertingRuleStrategyInterface[] $ruleApplyingStrategies
     * @throws ApplyConvertingRulesException
     */
    public function __construct(
        ConvertingRuleValidatorInterface $convertingRuleValidator,
        ValidationResultFactory $validationResultFactory,
        array $ruleApplyingStrategies = []
    ) {
        $this->convertingRuleValidator = $convertingRuleValidator;
        $this->validationResultFactory = $validationResultFactory;

        foreach ($ruleApplyingStrategies as $ruleApplyingStrategy) {
            if (!$ruleApplyingStrategy instanceof ApplyConvertingRuleStrategyInterface) {
                throw new ApplyConvertingRulesException(
                    __('Apply converting rule strategy must implement %1.', ApplyConvertingRuleStrategyInterface::class)
                );
            }
        }
        $this->ruleApplyingStrategies = $ruleApplyingStrategies;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $importData, array $convertingRules): array
    {
        usort($convertingRules, function ($previousRule, $nextRule) {
            return $previousRule['sort'] <=> $nextRule['sort'];
        });
        foreach ($convertingRules as $convertingRule) {
            $validationResult = $this->convertingRuleValidator->validate($convertingRule);
            if (false === $validationResult->isValid()) {
                throw new ValidationException(__('Validation Failed.'), null, 0, $validationResult);
            }

            $identifier = $convertingRule->getIdentifier();
            if (!isset($this->ruleApplyingStrategies[$identifier])) {
                $validationResult = $this->validationResultFactory->create(
                    [
                        'errors' => [
                            __(
                                'Converting rule "%identifier" is not supported.',
                                ['identifier' => $identifier]
                            ),
                        ],
                    ]
                );
                throw new ValidationException(__('Validation Failed.'), null, 0, $validationResult);
            }
            $importData = $this->ruleApplyingStrategies[$identifier]
                ->execute($importData, $convertingRule);
        }
        return $importData;
    }
}
