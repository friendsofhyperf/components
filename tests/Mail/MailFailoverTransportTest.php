<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Mail;

use FriendsOfHyperf\Mail\Contract\Factory;
use FriendsOfHyperf\Tests\Mail\Stubs\ContainerStub;
use Hyperf\Context\ApplicationContext;
use Hyperf\ViewEngine\Contract\FactoryInterface;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Transport\FailoverTransport;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\Group('mail')]
class MailFailoverTransportTest extends TestCase
{
    protected function setUp(): void
    {
        ContainerStub::getContainer();
    }

    protected function tearDown(): void
    {
        ContainerStub::clear();
        Mockery::close();
    }

    public function testGetFailoverTransportWithConfiguredTransports(): void
    {
        /** @var Container $container */
        $container = ApplicationContext::getContainer();
        $config = $container->get(\Hyperf\Contract\ConfigInterface::class);
        $config->set('mail.default', 'failover');
        $config->set('mail.mailers', [
            'failover' => [
                'transport' => 'failover',
                'mailers' => [
                    'sendmail',
                    'array',
                ],
            ],

            'sendmail' => [
                'transport' => 'sendmail',
                'path' => '/usr/sbin/sendmail -bs',
            ],

            'array' => [
                'transport' => 'array',
            ],
        ]);
        $container->set(FactoryInterface::class, Mockery::mock(FactoryInterface::class));
        $mailerManager = $container->get(Factory::class);
        $transport = $mailerManager->mailer()->getSymfonyTransport();
        $this->assertInstanceOf(FailoverTransport::class, $transport);
    }
}
