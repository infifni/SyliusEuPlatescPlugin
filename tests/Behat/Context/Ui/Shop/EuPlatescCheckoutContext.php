<?php

/**
 * This file was created by the developers from Infifni.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://infifnisoftware.ro and write us
 * an email on contact@infifnisoftware.ro.
 */

declare(strict_types=1);

namespace Tests\Infifni\SyliusEuPlatescPlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Behat\Mink\Exception\DriverException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Sylius\Behat\Page\Shop\Checkout\CompletePageInterface;
use Sylius\Behat\Page\Shop\Order\ShowPageInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\PaymentInterface;
use Tests\Infifni\SyliusEuPlatescPlugin\Behat\Page\External\EuPlatescCheckoutPageInterface;
use Webmozart\Assert\Assert;

final class EuPlatescCheckoutContext implements Context
{
    /** @var CompletePageInterface */
    private $summaryPage;

    /** @var EuPlatescCheckoutPageInterface */
    private $euplatescCheckoutPage;

    /** @var ShowPageInterface */
    private $orderDetails;
    /**
     * @var EntityRepository
     */
    private $paymentRepository;

    /**
     * @param CompletePageInterface $summaryPage
     * @param EuPlatescCheckoutPageInterface $euplatescCheckoutPage
     * @param ShowPageInterface $orderDetails
     * @param EntityRepository $paymentRepository
     */
    public function __construct(
        CompletePageInterface $summaryPage,
        EuPlatescCheckoutPageInterface $euplatescCheckoutPage,
        ShowPageInterface $orderDetails,
        EntityRepository $paymentRepository
    ) {
        $this->summaryPage = $summaryPage;
        $this->euplatescCheckoutPage = $euplatescCheckoutPage;
        $this->orderDetails = $orderDetails;
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * @When I confirm my order with EuPlatesc payment
     * @Given I have confirmed my order with EuPlatesc payment
     */
    public function iConfirmMyOrderWithEuPlatescPayment(): void
    {
        $this->summaryPage->confirmOrder();
    }

    /**
     * @When I sign in to EuPlatesc and pay successfully
     *
     * @throws DriverException
     * @throws UnsupportedDriverActionException
     */
    public function iSignInToEuPlatescAndPaySuccessfully(): void
    {
        $this->euplatescCheckoutPage->pay();
    }

    /**
     * @When I cancel my EuPlatesc payment
     * @Given I have cancelled EuPlatesc payment
     */
    public function iCancelMyEuPlatescPayment(): void
    {
        $this->euplatescCheckoutPage->cancel();
    }

    /**
     * @When I try to pay again with EuPlatesc payment
     */
    public function iTryToPayAgainEuPlatescPayment(): void
    {
        $this->orderDetails->pay();
    }

    /**
     * @Then I should get a notification of a successful transaction
     */
    public function iShouldGetANotificationOfASuccessfulTransaction(): void
    {
        $this->euplatescCheckoutPage->successNotify();
    }

    /**
     * @Then Payment status should have been completed
     */
    public function paymentStatusShouldHasBeenCompleted(): void
    {
        /** @var PaymentInterface[] $payments */
        $payments = $this->paymentRepository->findAll();

        Assert::true(0 < count($payments));

        foreach ($payments as $payment) {
            Assert::true(PaymentInterface::STATE_COMPLETED === $payment->getState());
        }
    }
}
