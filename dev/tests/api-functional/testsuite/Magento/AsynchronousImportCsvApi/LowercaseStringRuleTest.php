<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousImportCsvApi;

use Magento\AsynchronousImportDataConvertingApi\Api\Data\ConvertingRuleInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * LowercaseString converting rule test
 */
class LowercaseStringRuleTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/import/csv';
    /**#@-*/

    public function testStartImportWithConvertingRule()
    {
        if (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP) {
            $this->markTestSkipped('Do not support SOAP for new functionallity.');
        }
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
        ];

        $csvString = "value1\nvalue2\nvalue3\nvalue4\nvalue5";
        $data = [
            'source' => [
                'sourceType' => 'base64_encoded_data',
                'sourceDefinition' => base64_encode($csvString),
                'sourceDataFormat' => 'CSV',
            ],
            'import' => [
                'importType' => 'advanced_pricing',
                'importBehaviour' => 'add',
            ],
            'convertingRules' => [
                [
                    'identifier' => 'lowercase_string',
                    'applyTo' => ['value1'],
                ],
            ],
        ];
        $this->_webApiCall($serviceInfo, $data);
    }

    /**
     * @param array $convertingRule
     * @param array $expectedErrorData
     * @dataProvider dataProviderInvalidData
     * @throws \Exception
     */
    public function testStartImportWithInvalidConvertingRule(array $convertingRule, array $expectedErrorData)
    {
        if (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP) {
            $this->markTestSkipped('Do not support SOAP for new functionallity.');
        }
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
        ];

        $data = [
            'source' => [
                'sourceType' => 'base64_encoded_data',
                'sourceDefinition' => base64_encode("value1\nvalue2\nvalue3\nvalue4\nvalue5"),
                'sourceDataFormat' => 'CSV',
            ],
            'import' => [
                'importType' => 'advanced_pricing',
                'importBehaviour' => 'add',
            ],
            'convertingRules' => [
                $convertingRule
            ]
        ];
        $this->assertWebApiCallErrors($serviceInfo, $data, $expectedErrorData);
    }

    /**
     * @return array
     */
    public function dataProviderInvalidData(): array
    {
        return [
            'missed_converting_rule_apply_to' => [
                [
                    'identifier' => 'lowercase_string',
                ],
                [
                    'message' => 'Validation Failed.',
                    'errors' => [
                        [
                            'message' => '"%field" cannot be empty.',
                            'parameters' => [
                                'field' => ConvertingRuleInterface::APPLY_TO,
                            ],
                        ],
                    ],
                ],
            ],
            'empty_converting_rule_apply_to' => [
                [
                    'identifier' => 'lowercase_string',
                    'applyTo' => [],
                ],
                [
                    'message' => 'Validation Failed.',
                    'errors' => [
                        [
                            'message' => '"%field" cannot be empty.',
                            'parameters' => [
                                'field' => ConvertingRuleInterface::APPLY_TO,
                            ],
                        ],
                    ],
                ],
            ],
            'wrong_format_converting_rule_apply_to' => [
                [
                    'identifier' => 'lowercase_string',
                    'applyTo' => 'string_but_not_array',
                ],
                [
                    'message' => 'The "string" value\'s type is invalid. The "string[]" type was expected. Verify'
                        . ' and try again.',
                ],
            ],
        ];
    }
}
