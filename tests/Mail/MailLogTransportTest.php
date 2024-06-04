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

use FriendsOfHyperf\Mail\Attachment;
use FriendsOfHyperf\Mail\Contract\Factory;
use FriendsOfHyperf\Mail\Contract\Mailer;
use FriendsOfHyperf\Mail\Message;
use FriendsOfHyperf\Mail\Transport\LogTransport;
use FriendsOfHyperf\Tests\Mail\Stubs\ContainerStub;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\ViewEngine\Contract\FactoryInterface;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Stringable;
use Symfony\Component\Mime\Email;

/**
 * @internal
 * @coversNothing
 */
class MailLogTransportTest extends TestCase
{
    protected function setUp(): void
    {
        ContainerStub::getContainer(static function (ContainerInterface $container) {});
    }

    protected function tearDown(): void
    {
        ContainerStub::clear();
    }

    public function testGetLogTransportWithConfiguredChannel()
    {
        $container = ApplicationContext::getContainer();
        $container->set(FactoryInterface::class, Mockery::mock(FactoryInterface::class));
        $config = ApplicationContext::getContainer()->get(ConfigInterface::class);
        $config->set('mail.driver', 'log');
        $config->set('mail.log.group', 'mail');
        $config->set('mail.log.name', 'test');
        $mailerManager = $container->get(Factory::class);
        $container->set(Mailer::class, $mailerManager->mailer());

        $transport = $container->get(Mailer::class)->getSymfonyTransport();
        $this->assertInstanceOf(LogTransport::class, $transport);

        $logger = $transport->logger();
        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }

    public function testItDecodesTheMessageBeforeLogging()
    {
        $message = (new Message(new Email()))
            ->from('noreply@example.com', 'no-reply')
            ->to('taylor@example.com', 'Taylor')
            ->html(<<<'BODY'
            Hi,

            <a href="https://example.com/reset-password=5e113c71a4c210aff04b3fa66f1b1299">Click here to reset your password</a>.

            All the best,

            Burt & Irving
            BODY)
            ->text('A text part');

        $actualLoggedValue = $this->getLoggedEmailMessage($message);

        $this->assertStringNotContainsString("=\r\n", $actualLoggedValue);
        $this->assertStringContainsString('href=', $actualLoggedValue);
        $this->assertStringContainsString('Burt & Irving', $actualLoggedValue);
        $this->assertStringContainsString('https://example.com/reset-password=5e113c71a4c210aff04b3fa66f1b1299', $actualLoggedValue);
    }

    public function testItOnlyDecodesQuotedPrintablePartsOfTheMessageBeforeLogging()
    {
        $message = (new Message(new Email()))
            ->from('noreply@example.com', 'no-reply')
            ->to('taylor@example.com', 'Taylor')
            ->html(<<<'BODY'
            Hi,

            <a href="https://example.com/reset-password=5e113c71a4c210aff04b3fa66f1b1299">Click here to reset your password</a>.

            All the best,

            Burt & Irving
            BODY)
            ->text('A text part')
            ->attach(Attachment::fromData(fn () => 'My attachment', 'attachment.txt'));

        $actualLoggedValue = $this->getLoggedEmailMessage($message);

        $this->assertStringContainsString('href=', $actualLoggedValue);
        $this->assertStringContainsString('Burt & Irving', $actualLoggedValue);
        $this->assertStringContainsString('https://example.com/reset-password=5e113c71a4c210aff04b3fa66f1b1299', $actualLoggedValue);
        $this->assertStringContainsString('name=attachment.txt', $actualLoggedValue);
        $this->assertStringContainsString('filename=attachment.txt', $actualLoggedValue);
    }

    public function testGetLogTransportWithPsrLogger()
    {
        $container = ApplicationContext::getContainer();

        $container->set(FactoryInterface::class, Mockery::mock(FactoryInterface::class));
        $config = $container->get(ConfigInterface::class);
        $config->set('mail.driver', 'log');
        $config->set('mail.log.group', 'mail1');
        $config->set('mail.log.name', 'test1');
        $container->set('log', $logger = new NullLogger());
        $mailerManager = $container->get(Factory::class);
        $transportLogger = $mailerManager->mailer()->getSymfonyTransport()->logger();

        $this->assertEquals($logger, $transportLogger);
    }

    private function getLoggedEmailMessage(Message $message): string
    {
        $logger = new class() extends NullLogger {
            public string $loggedValue = '';

            public function log($level, string|Stringable $message, array $context = []): void
            {
                $this->loggedValue = (string) $message;
            }
        };

        (new LogTransport($logger))->send(
            $message->getSymfonyMessage()
        );

        return $logger->loggedValue;
    }
}
