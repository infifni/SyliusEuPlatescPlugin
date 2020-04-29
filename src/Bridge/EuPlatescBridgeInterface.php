<?php

/**
 * This file was created by the developers from Infifni.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://infifnisoftware.ro and write us
 * an email on contact@infifnisoftware.ro.
 */

declare(strict_types=1);

namespace Infifni\SyliusEuPlatescPlugin\Bridge;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;

interface EuPlatescBridgeInterface
{
    public const TEST_ENVIRONMENT = 'sandbox';
    public const LIVE_ENVIRONMENT = 'secure';
    public const TEST_MERCHANT_ID = 'testaccount';
    public const TEST_MERCHANT_KEY = '00112233445566778899AABBCCDDEEFF';
    public const PAYMENT_URL = 'https://secure.euplatesc.ro/tdsprocess/tranzactd.php';
    public const COMPLETED_STATUS = 'completed';
    public const FAILED_STATUS = 'failed';
    public const CANCELLED_STATUS = 'cancelled';
    public const CREATED_STATUS = 'created';

    public function setAuthorizationData(string $merchantId, string $merchantKey): void;
    public function hmacFormat(array $data): string;
    public function getDataForHmac(OrderInterface $order, PaymentInterface $payment): array;
}
