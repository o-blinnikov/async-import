<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousImportSourceDataRetrieving\Model\SourceDataRetrievingStrategy;

use Magento\AsynchronousImportSourceDataRetrievingApi\Api\Data\SourceInterface;
use Magento\AsynchronousImportSourceDataRetrievingApi\Api\Data\SourceInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Base64EncodedDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Base64EncodedData
     */
    private $model;

    /** @var SourceInterfaceFactory */
    private $sourceInterfaceFactory;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->model = $this->objectManager->create(
            Base64EncodedData::class
        );

        $this->sourceInterfaceFactory = $this->objectManager->create(
            SourceInterfaceFactory::class
        );
    }

    /**
     * @throws \Magento\AsynchronousImportSourceDataRetrievingApi\Api\SourceDataRetrievingException
     */
    public function testExecute()
    {
        $csvString = "value1\nvalue2\nvalue3\nvalue4\nvalue5";

        /** @var SourceInterface $sourceInterface */
        $sourceInterface = $this->sourceInterfaceFactory->create(
            [
                'sourceType' => 'base64_encoded_data',
                'sourceDefinition' => base64_encode($csvString),
                'sourceDataFormat' => 'CSV'
            ]
        );

        $iterator = $this->model->execute($sourceInterface);
        $this->assertCount(2, $iterator);
    }
}
