<?php

/**
 * This file was created by the developers from Infifni.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://infifnisoftware.ro and write us
 * an email on contact@infifnisoftware.ro.
 */

declare(strict_types=1);

namespace Infifni\SyliusEuPlatescPlugin;

use Infifni\SyliusEuPlatescPlugin\Bridge\EuPlatescBridgeInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

final class EuPlatescGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults(
            [
                'euplatesc.factory_name' => 'euplatesc',
                'euplatesc.factory_title' => 'EuPlătesc',
            ]
        );

        if (false === (bool) $config['payum.api']) {
            $config['payum.default_options'] = [
                'environment' => EuPlatescBridgeInterface::TEST_ENVIRONMENT,
                'merchantId' => EuPlatescBridgeInterface::TEST_MERCHANT_ID,
                'merchantKey' => EuPlatescBridgeInterface::TEST_MERCHANT_KEY,
            ];
            $config->defaults($config['payum.default_options']);

            $config['payum.required_options'] = ['environment', 'merchantId', 'merchantKey'];

            $config['payum.api'] = static function (ArrayObject $config): array {
                $config->validateNotEmpty($config['payum.required_options']);

                return [
                    'environment' => $config['environment'],
                    'merchantId' => $config['merchantId'],
                    'merchantKey' => $config['merchantKey']
                ];
            };
        }
    }
}
