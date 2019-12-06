<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousImportDataExchangingApi\Model;

use Magento\AsynchronousImportDataExchangingApi\Api\Data\ImportInterface;
use Magento\AsynchronousImportDataExchangingApi\Api\ImportDataExchangeException;
use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;

/**
 * Extension point for adding import request validators via DI configuration
 *
 * @api
 */
class ImportValidatorChain implements ImportValidatorInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @var ImportValidatorInterface[]
     */
    private $validators;

    /**
     * @param ValidationResultFactory $validationResultFactory
     * @param array $validators
     * @throws ImportDataExchangeException
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        array $validators = []
    ) {
        $this->validationResultFactory = $validationResultFactory;
        foreach ($validators as $validator) {
            if (!$validator instanceof ImportValidatorInterface) {
                throw new ImportDataExchangeException(
                    __('Validator must implement %1.', ImportValidatorInterface::class)
                );
            }
        }
        $this->validators = $validators;
    }

    /**
     * @inheritdoc
     */
    public function validate(ImportInterface $import): ValidationResult
    {
        $errors = [];
        foreach ($this->validators as $validator) {
            $validationResult = $validator->validate($import);

            if (!$validationResult->isValid()) {
                $errors[] = $validationResult->getErrors();
            }
        }
        $errors = count($errors) ? array_merge(...$errors) : [];
        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
