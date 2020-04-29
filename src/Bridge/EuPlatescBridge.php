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

final class EuPlatescBridge implements EuPlatescBridgeInterface
{
    /** @var string */
    private $merchantId;

    /** @var string */
    private $merchantKey;

    public function setAuthorizationData(
        string $merchantId,
        string $merchantKey
    ): void {
        $this->merchantId = $merchantId;
        $this->merchantKey = $merchantKey;
    }

    public function hmacFormat(array $data): string
    {
        $str = '';
        foreach ($data as $d) {
            if(null === $d || '' === $d) {
                $str .= '-';
            } else {
                $str .= strlen((string) $d) . $d;
            }
        }

        $key = pack('H*', $this->merchantKey);

        return strtoupper(self::hmacSha1($key, $str));
    }

    private static function hmacSha1(string $key, string $data): string
    {
        $blockSize = 64;
        $hashFunc  = 'md5';

        if(strlen($key) > $blockSize) {
            $key = pack('H*', $hashFunc($key));
        }

        $key  = str_pad($key, $blockSize, chr(0x00));
        $ipad = str_repeat(chr(0x36), $blockSize);
        $opad = str_repeat(chr(0x5c), $blockSize);

        $hmac = pack('H*', $hashFunc(($key ^ $opad) . pack('H*', $hashFunc(($key ^ $ipad) . $data))));

        return bin2hex($hmac);
    }

    public function getDataForHmac(OrderInterface $order, PaymentInterface $payment): array
    {
        return [
            'amount' => (string) ($payment->getAmount() / 100),
            'curr' => $payment->getCurrencyCode(),
            'invoice_id' => (string) $payment->getId(),
            'order_desc' => 'Order ' . $order->getNumber() . ' with ' . $order->getItems()->count() . ' items.',
            'merch_id' => $this->merchantId,
            'timestamp' => gmdate('YmdHis'),
            'nonce' => md5(microtime() . mt_rand()),
        ];
    }
}