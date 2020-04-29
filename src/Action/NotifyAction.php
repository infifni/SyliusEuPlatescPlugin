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

use ArrayAccess;
use Infifni\SyliusEuPlatescPlugin\Bridge\EuPlatescBridgeInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;

final class NotifyAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

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

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        if (false === $this->verifySign($httpRequest)) {
            throw new InvalidArgumentException('Invalid sign.');
        }

        $details['ep_id'] = $httpRequest->request['ep_id'];
        $details['action'] = $httpRequest->request['action'];
        $details['message'] = $httpRequest->request['message'];
        $details['approval'] = $httpRequest->request['approval'];

        if (0 === (int) $httpRequest->request['action']) {
            $details['euplatesc_status'] = EuPlatescBridgeInterface::COMPLETED_STATUS;

            return;
        }

        $details['euplatesc_status'] = EuPlatescBridgeInterface::FAILED_STATUS;
    }

    public function supports($request): bool
    {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof ArrayAccess
        ;
    }

    private function verifySign(GetHttpRequest $request): bool
    {
        $hashData = [
            'amount'     => addslashes(trim(@$request->request['amount'])), // original amount
            'curr'       => addslashes(trim(@$request->request['curr'])), // original currency
            'invoice_id' => addslashes(trim(@$request->request['invoice_id'])), // original invoice id
            'ep_id'      => addslashes(trim(@$request->request['ep_id'])), // EuPlatesc unique id
            'merch_id'   => addslashes(trim(@$request->request['merch_id'])), // your merchant id
            'action'     => addslashes(trim(@$request->request['action'])), // if action == 0 transaction ok
            'message'    => addslashes(trim(@$request->request['message'])), // transaction response message
            'approval'   => addslashes(trim(@$request->request['approval'])), // if action!=0 empty
            'timestamp'  => addslashes(trim(@$request->request['timestamp'])), // message timestamp
            'nonce'      => addslashes(trim(@$request->request['nonce'])),
        ];
        $hashData['fp_hash'] = $this->euPlatescBridge->hmacFormat($hashData);
        $fpHash = addslashes(trim(@$request->request['fp_hash']));

        return $fpHash === $hashData['fp_hash'];
    }
}
