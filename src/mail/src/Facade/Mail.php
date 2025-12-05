<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Mail\Facade;

use FriendsOfHyperf\Mail\Contract\Mailable;
use FriendsOfHyperf\Mail\Contract\Mailer as MailerContract;
use FriendsOfHyperf\Mail\Mailer;
use FriendsOfHyperf\Mail\MailManager;
use FriendsOfHyperf\Mail\PendingMail;
use FriendsOfHyperf\Mail\SentMessage;
use Hyperf\Context\ApplicationContext;
use Hyperf\ViewEngine\Contract\FactoryInterface;
use RuntimeException;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * @method static MailerContract mailer(string|null $name = null)
 * @method static Mailer driver(string|null $driver = null)
 * @method static TransportInterface createSymfonyTransport(array $config)
 * @method static string getDefaultDriver()
 * @method static void setDefaultDriver(string $name)
 * @method static void purge(string|null $name = null)
 * @method static MailManager extend(string $driver, \Closure $callback)
 * @method static MailManager forgetMailers()
 * @method static void alwaysFrom(string $address, string|null $name = null)
 * @method static void alwaysReplyTo(string $address, string|null $name = null)
 * @method static void alwaysReturnPath(string $address)
 * @method static void alwaysTo(string $address, string|null $name = null)
 * @method static PendingMail to(mixed $users, string|null $name = null)
 * @method static PendingMail cc(mixed $users, string|null $name = null)
 * @method static PendingMail bcc(mixed $users, string|null $name = null)
 * @method static SentMessage|null html(string $html, mixed $callback)
 * @method static SentMessage|null raw(string $text, mixed $callback)
 * @method static SentMessage|null plain(string $view, array $data, mixed $callback)
 * @method static string render(string|array $view, array $data = [])
 * @method static SentMessage|null send(Mailable|string|array $view, array $data = [], \Closure|string|null $callback = null)
 * @method static SentMessage|null sendNow(Mailable|string|array $mailable, array $data = [], \Closure|string|null $callback = null)
 * @method static TransportInterface getSymfonyTransport()
 * @method static FactoryInterface getViewFactory()
 * @method static void setSymfonyTransport(TransportInterface $transport)
 * @method static void macro(string $name, object|callable $macro, object|callable $macro = null)
 * @method static void mixin(object $mixin, bool $replace = true)
 * @method static bool hasMacro(string $name)
 * @method static void flushMacros()
 * @method static void assertSent(string|\Closure $mailable, callable|int|null $callback = null)
 * @method static void assertNotOutgoing(string|\Closure $mailable, callable|null $callback = null)
 * @method static void assertNotSent(string|\Closure $mailable, callable|null $callback = null)
 * @method static void assertNothingOutgoing()
 * @method static void assertNothingSent()
 * @method static void assertSentCount(int $count)
 * @method static void assertOutgoingCount(int $count)
 *
 * @see MailManager
 */
class Mail
{
    /**
     * Handle dynamic, static calls to the object.
     *
     * @throws RuntimeException
     */
    public static function __callStatic(string $method, array $arguments): mixed
    {
        $container = ApplicationContext::getContainer();
        $instance = $container->get(MailManager::class);

        return $instance->{$method}(...$arguments);
    }
}
