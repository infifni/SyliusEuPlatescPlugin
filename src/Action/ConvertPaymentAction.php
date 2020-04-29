<?php

/**
 * This file was created by the developers from Infifni.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://infifnisoftware.ro and write us
 * an email on contact@infifnisoftware.ro.
 */

declare(strict_types=1);

namespace Infifni\SyliusEuPlatescPlugin\Action;

use Infifni\SyliusEuPlatescPlugin\Bridge\EuPlatescBridgeInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Sylius\Component\Core\Model\OrderInterface;
use Webmozart\Assert\Assert;

final class ConvertPaymentAction implements ActionInterface, ApiAwareInterface
{
    /**
     * @var EuPlatescBridgeInterface
     */
    private $euPlatescBridge;

    public function __construct(EuPlatescBridgeInterface $euPlatescBridge)
    {
        $this->euPlatescBridge = $euPlatescBridge;
    }

    /**
     * {@inheritdoc}
     */
    public function setApi($api): void
    {
        if (false === is_array($api)) {
            throw new UnsupportedApiException('Not supported. Expected to be set as array.');
        }

        $this->euPlatescBridge->setAuthorizationData($api['merchantId'], $api['merchantKey']);
    }

    /**
     * {@inheritDoc}
     *
     * @param Convert $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();
        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        $request->setResult($this->getPaymentData($order, $payment));
    }

    private function getPaymentData(OrderInterface $order, PaymentInterface $payment)
    {
        /** @var CustomerInterface $customer */
        $customer = $order->getCustomer();
        Assert::isInstanceOf(
            $customer,
            CustomerInterface::class,
            sprintf(
                'Make sure the first model is the %s instance.',
                CustomerInterface::class
            )
        );

        /** @var AddressInterface $billingAddress */
        $billingAddress = $order->getBillingAddress();
        Assert::isInstanceOf(
            $billingAddress,
            AddressInterface::class,
            sprintf(
                'Make sure the first model is the %s instance.',
                AddressInterface::class
            )
        );

        $paymentData = [
            'fname' => (string) $billingAddress->getFirstName(),
            'lname' => (string) $billingAddress->getLastName(),
            'country' => (string) $billingAddress->getCountryCode(),
            'company' => (string) $billingAddress->getCompany(),
            'city' => (string) $billingAddress->getCity(),
            'state' => (string) $billingAddress->getProvinceName(),
            'zip_code' => (string) $billingAddress->getPostcode(),
            'add' => (string) $billingAddress->getStreet(),
            'email' => (string) $customer->getEmail(),
            'phone' => (string) $billingAddress->getPhoneNumber(),
            'fax' => '',
        ];

        $hashData = $this->euPlatescBridge->getDataForHmac($order, $payment);
        $hashData['fp_hash'] = $this->euPlatescBridge->hmacFormat($hashData);

        $paymentData = array_merge($paymentData, $hashData);

        /** @var AddressInterface $shippingAddress */
        $shippingAddress = $order->getShippingAddress();
        Assert::isInstanceOf(
            $shippingAddress,
            AddressInterface::class,
            sprintf(
                'Make sure the first model is the %s instance.',
                AddressInterface::class
            )
        );
        $paymentData = array_merge($paymentData, [
            'sfname' => (string) $shippingAddress->getFirstName(),
            'slname' => (string) $shippingAddress->getLastName(),
            'scountry' => (string) $shippingAddress->getCountryCode(),
            'scompany' => (string) $shippingAddress->getCompany(),
            'scity' => (string) $shippingAddress->getCity(),
            'sstate' => (string) $shippingAddress->getProvinceName(),
            'szip_code' => (string) $shippingAddress->getPostcode(),
            'sadd' => (string) $shippingAddress->getStreet(),
            'semail' => (string) $customer->getEmail(),
            'sphone' => (string) $shippingAddress->getPhoneNumber(),
            'sfax' => '',
        ]);

        return $paymentData;
    }

    /**
     * @param mixed $request
     *
     * @return boolean
     */
    public function supports($request): bool
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getTo() === 'array';
    }
}