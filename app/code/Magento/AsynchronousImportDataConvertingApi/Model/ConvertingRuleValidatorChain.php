<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousImportDataConvertingApi\Model;

use Magento\AsynchronousImportDataConvertingApi\Api\ApplyConvertingRulesException;
use Magento\AsynchronousImportDataConvertingApi\Api\Data\ConvertingRuleInterface;
use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;

/**
 * Extension point for adding converting rule validators via DI configuration
 *
 * @api
 */
class ConvertingRuleValidatorChain implements ConvertingRuleValidatorInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @var ConvertingRuleValidatorInterface[]
     */
    private $validators;

    /**
     * @param ValidationResultFactory $validationResultFactory
     * @param array $validators
     * @throws ApplyConvertingRulesException
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        array $validators = []
    ) {
        $this->validationResultFactory = $validationResultFactory;
        foreach ($validators as $validator) {
            if (!$validator instanceof ConvertingRuleValidatorInterface) {
                throw new ApplyConvertingRulesException(
                    __('Validator must implement %1.', ConvertingRuleValidatorInterface::class)
                );
            }
        }
        $this->validators = $validators;
    }

    /**
     * @inheritdoc
     */
    public function validate(ConvertingRuleInterface $convertingRule): ValidationResult
    {
        $errors = [];
        foreach ($this->validators as $validator) {
            $validationResult = $validator->validate($convertingRule);

            if (!$validationResult->isValid()) {
                $errors[] = $validationResult->getErrors();
            }
        }
        $errors = count($errors) ? array_merge(...$errors) : [];
        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
