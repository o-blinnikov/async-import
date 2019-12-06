<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousImportSourceDataRetrieving\Model;

use Magento\AsynchronousImportSourceDataRetrievingApi\Api\Data\SourceDataInterface;
use Magento\AsynchronousImportSourceDataRetrievingApi\Api\Data\SourceDataInterfaceFactory;
use Magento\AsynchronousImportSourceDataRetrievingApi\Api\Data\SourceInterface;
use Magento\AsynchronousImportSourceDataRetrievingApi\Api\RetrieveSourceDataInterface;
use Magento\AsynchronousImportSourceDataRetrievingApi\Api\SourceDataRetrievingException;
use Magento\AsynchronousImportSourceDataRetrievingApi\Model\RetrieveSourceDataStrategyInterface;
use Magento\AsynchronousImportSourceDataRetrievingApi\Model\SourceValidatorInterface;
use Magento\Framework\Validation\ValidationException;
use Magento\Framework\Validation\ValidationResultFactory;

/**
 * @inheritdoc
 */
class RetrieveSourceData implements RetrieveSourceDataInterface
{
    /**
     * @var SourceValidatorInterface
     */
    private $sourceValidator;

    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @var RetrieveSourceDataStrategyInterface[]
     */
    private $retrievingStrategies;

    /**
     * @var SourceDataInterfaceFactory
     */
    private $sourceDataFactory;

    /**
     * @param SourceValidatorInterface $sourceValidator
     * @param ValidationResultFactory $validationResultFactory
     * @param SourceDataInterfaceFactory $sourceDataFactory
     * @param RetrieveSourceDataStrategyInterface[] $retrievingStrategies
     * @throws SourceDataRetrievingException
     */
    public function __construct(
        SourceValidatorInterface $sourceValidator,
        ValidationResultFactory $validationResultFactory,
        SourceDataInterfaceFactory $sourceDataFactory,
        array $retrievingStrategies = []
    ) {
        $this->sourceValidator = $sourceValidator;
        $this->validationResultFactory = $validationResultFactory;
        $this->sourceDataFactory = $sourceDataFactory;

        foreach ($retrievingStrategies as $retrievingStrategy) {
            if (!$retrievingStrategy instanceof RetrieveSourceDataStrategyInterface) {
                throw new SourceDataRetrievingException(
                    __('Source data retrieving strategy must implement %1.', RetrieveSourceDataStrategyInterface::class)
                );
            }
        }
        $this->retrievingStrategies = $retrievingStrategies;
    }

    /**
     * @inheritdoc
     */
    public function execute(SourceInterface $source): SourceDataInterface
    {
        $validationResult = $this->sourceValidator->validate($source);
        if (false === $validationResult->isValid()) {
            throw new ValidationException(__('Validation Failed.'), null, 0, $validationResult);
        }

        $sourceType = $source->getSourceType();
        if (!isset($this->retrievingStrategies[$sourceType])) {
            $validationResult = $this->validationResultFactory->create(
                [
                    'errors' => [
                        __('Source type "%source_type" is not supported.', ['source_type' => $sourceType]),
                    ],
                ]
            );
            throw new ValidationException(__('Validation Failed.'), null, 0, $validationResult);
        }
        $iterator = $this->retrievingStrategies[$sourceType]->execute($source);

        /** @var SourceDataInterface $sourceData */
        $sourceData = $this->sourceDataFactory->create(
            [
                SourceDataInterface::ITERATOR => $iterator,
            ]
        );
        return $sourceData;
    }
}
