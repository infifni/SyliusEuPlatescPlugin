<?php

/**
 * This file was created by the developers from Infifni.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://infifnisoftware.ro and write us
 * an email on contact@infifnisoftware.ro.
 */

declare(strict_types=1);

namespace spec\Infifni\SyliusEuPlatescPlugin;

use Infifni\SyliusEuPlatescPlugin\EuPlatescGatewayFactory;
use Payum\Core\GatewayFactory;
use PhpSpec\ObjectBehavior;

final class EuPlatescGatewayFactorySpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(EuPlatescGatewayFactory::class);
        $this->shouldHaveType(GatewayFactory::class);
    }

    public function it_populateConfig_run(): void
    {
        $this->createConfig([]);
    }
}
