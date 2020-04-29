<?php

/**
 * This file was created by the developers from Infifni.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://infifnisoftware.ro and write us
 * an email on contact@infifnisoftware.ro.
 */

declare(strict_types=1);

namespace Tests\Infifni\SyliusEuPlatescPlugin\Behat\Page\External;

use Behat\Mink\Exception\DriverException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Mink\Session;
use Doctrine\ORM\EntityManagerInterface;
use FriendsOfBehat\PageObjectExtension\Page\Page;
use FriendsOfBehat\SymfonyExtension\Mink\MinkParameters;
use Infifni\SyliusEuPlatescPlugin\Bridge\EuPlatescBridgeInterface;
use Payum\Core\Security\TokenInterface;
use RuntimeException;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\BrowserKit\AbstractBrowser as Client;

final class EuPlatescCheckoutPage extends Page implements EuPlatescCheckoutPageInterface
{
    /** @var RepositoryInterface */
    private $securityTokenRepository;

    /** @var EntityRepository */
    private $paymentRepository;

    /** @var Client */
    private $client;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EuPlatescBridgeInterface
     */
    private $euPlatescBridge;

    /**
     * @var array
     */
    private $notifyData = [
        'amount'=>  '1',
        'curr'=> 'USD',
        'invoice_id'=>  '2',
        'ep_id'=>  'E6C1EDA4BD6062FF87579C4C9788AF16540FE6AF',
        'merch_id'=> EuPlatescBridgeInterface::TEST_MERCHANT_ID,
        'action' => '0',
        'message' => 'Approved',
        'approval' => '133228',
        'timestamp' => '20200424191044',
        'nonce' => '09f641b9717c4e61e97c40ebc7988222',
    ];

    public function __construct(
        Session $session,
        MinkParameters $parameters,
        RepositoryInterface $securityTokenRepository,
        EntityRepository $paymentRepository,
        EntityManagerInterface $entityManager,
        Client $client,
        EuPlatescBridgeInterface $euPlatescBridge
    ) {
        parent::__construct($session, $parameters);

        $this->paymentRepository = $paymentRepository;
        $this->securityTokenRepository = $securityTokenRepository;
        $this->client = $client;
        $this->entityManager = $entityManager;
        $this->euPlatescBridge = $euPlatescBridge;
    }

    /**
     * @throws DriverException
     * @throws UnsupportedDriverActionException
     */
    public function pay(): void
    {
        $token = $this->findToken();

        /** @var PaymentInterface $payment */
        $payment = $this->paymentRepository->find($token->getDetails()->getId());
        $paymentDetails = $payment->getDetails();
        $paymentDetails['euplatesc_status'] = EuPlatescBridgeInterface::COMPLETED_STATUS;
        $payment->setDetails($paymentDetails);
        $this->entityManager->merge($payment);
        $this->entityManager->flush();

        $this->getDriver()->visit($token->getTargetUrl());
    }

    /**
     * @throws DriverException
     * @throws UnsupportedDriverActionException
     */
    public function cancel(): void
    {
        $token = $this->findToken();

        /** @var PaymentInterface $payment */
        $payment = $this->paymentRepository->find($token->getDetails()->getId());
        $paymentDetails = $payment->getDetails();
        $paymentDetails['euplatesc_status'] = EuPlatescBridgeInterface::CANCELLED_STATUS;
        $payment->setDetails($paymentDetails);
        $this->entityManager->merge($payment);
        $this->entityManager->flush();

        $this->getDriver()->visit($token->getTargetUrl());
    }

    public function failedPayment(): void
    {
        $captureToken = $this->findToken('after');

        $this->getDriver()->visit($captureToken->getTargetUrl() . '&' . http_build_query(['status' => EuPlatescBridgeInterface::CANCELLED_STATUS]));
    }

    public function successNotify(): void
    {
        $token = $this->findToken('notify');
        /** @var PaymentInterface $payment */
        $payment = $this->paymentRepository->find($token->getDetails()->getId());

        $data = $this->notifyData;
        $paymentDetails = $payment->getDetails();
        $data['invoice_id'] = $paymentDetails['invoice_id'];
        $this->euPlatescBridge->setAuthorizationData(
            EuPlatescBridgeInterface::TEST_MERCHANT_ID,
            EuPlatescBridgeInterface::TEST_MERCHANT_KEY
        );
        $data['fp_hash'] = $this->euPlatescBridge->hmacFormat($data);
        $data['ExtraData'] = $token->getHash();

        $this->client->request('POST', '/payment/euplatesc/notify', $data);
    }

    /**
     * @param array $urlParameters
     * @return string
     */
    protected function getUrl(array $urlParameters = []): string
    {
        return EuPlatescBridgeInterface::PAYMENT_URL;
    }

    /**
     * @param string $type
     *
     * @return TokenInterface
     */
    private function findToken(string $type = 'capture'): TokenInterface
    {
        $tokens = $this->securityTokenRepository->findAll();

        /** @var TokenInterface $token */
        foreach ($tokens as $token) {
            if (strpos($token->getTargetUrl(), $type)) {
                return $token;
            }
        }

        throw new RuntimeException('Cannot find capture token, check if you are after proper checkout steps');
    }
}
