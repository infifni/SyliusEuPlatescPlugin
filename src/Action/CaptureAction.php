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
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Request\Capture;

final class CaptureAction implements ActionInterface, GenericTokenFactoryAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /** @var GenericTokenFactoryInterface */
    private $tokenFactory;

    public function setGenericTokenFactory(GenericTokenFactoryInterface $genericTokenFactory = null): void
    {
        $this->tokenFactory = $genericTokenFactory;
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $paymentDetails = $request->getModel();

        if (isset($paymentDetails['euplatesc_status']) && EuPlatescBridgeInterface::CREATED_STATUS !== $paymentDetails['euplatesc_status']) {
            return;
        }

        /** @var TokenInterface $token */
        $token = $request->getToken();
        $notifyToken = $this->tokenFactory->createNotifyToken($token->getGatewayName(), $token->getDetails());
        $paymentDetails['ExtraData'] = $notifyToken->getHash();
        $paymentDetails['euplatesc_status'] = EuPlatescBridgeInterface::CREATED_STATUS;

        throw new HttpPostRedirect(
            EuPlatescBridgeInterface::PAYMENT_URL,
            $paymentDetails->toUnsafeArray()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request): bool
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof ArrayAccess;
    }
}