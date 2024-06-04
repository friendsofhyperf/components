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

use FriendsOfHyperf\Mail\MailManager;
use Hyperf\Context\ApplicationContext;
use RuntimeException;

/**
 * @method static \FriendsOfHyperf\Contract\Mail\Mailer mailer(string|null $name = null)
 * @method static \FriendsOfHyperf\Mail\Mailer driver(string|null $driver = null)
 * @method static \Symfony\Component\Mailer\Transport\TransportInterface createSymfonyTransport(array $config)
 * @method static string getDefaultDriver()
 * @method static void setDefaultDriver(string $name)
 * @method static void purge(string|null $name = null)
 * @method static \FriendsOfHyperf\Mail\MailManager extend(string $driver, \Closure $callback)
 * @method static \FriendsOfHyperf\Mail\MailManager forgetMailers()
 * @method static void alwaysFrom(string $address, string|null $name = null)
 * @method static void alwaysReplyTo(string $address, string|null $name = null)
 * @method static void alwaysReturnPath(string $address)
 * @method static void alwaysTo(string $address, string|null $name = null)
 * @method static \FriendsOfHyperf\Mail\PendingMail to(mixed $users, string|null $name = null)
 * @method static \FriendsOfHyperf\Mail\PendingMail cc(mixed $users, string|null $name = null)
 * @method static \FriendsOfHyperf\Mail\PendingMail bcc(mixed $users, string|null $name = null)
 * @method static \FriendsOfHyperf\Mail\SentMessage|null html(string $html, mixed $callback)
 * @method static \FriendsOfHyperf\Mail\SentMessage|null raw(string $text, mixed $callback)
 * @method static \FriendsOfHyperf\Mail\SentMessage|null plain(string $view, array $data, mixed $callback)
 * @method static string render(string|array $view, array $data = [])
 * @method static \FriendsOfHyperf\Mail\SentMessage|null send(\FriendsOfHyperf\Contract\Mail\Mailable|string|array $view, array $data = [], \Closure|string|null $callback = null)
 * @method static \FriendsOfHyperf\Mail\SentMessage|null sendNow(\FriendsOfHyperf\Contract\Mail\Mailable|string|array $mailable, array $data = [], \Closure|string|null $callback = null)
 * @method static \Symfony\Component\Mailer\Transport\TransportInterface getSymfonyTransport()
 * @method static \FriendsOfHyperf\Contract\View\Factory getViewFactory()
 * @method static void setSymfonyTransport(\Symfony\Component\Mailer\Transport\TransportInterface $transport)
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
 * @method static \FriendsOfHyperf\Support\Collection sent(string|\Closure $mailable, callable|null $callback = null)
 * @method static bool hasSent(string $mailable)
 *
 * @see \FriendsOfHyperf\Mail\MailManager
 */
class Mail
{
    /**
     * Handle dynamic, static calls to the object.
     *
     * @param string $method
     * @param array $args
     * @return mixed
     *
     * @throws RuntimeException
     */
    public static function __callStatic($method, $args)
    {
        $container = ApplicationContext::getContainer();
        $instance = $container->get(MailManager::class);

        return $instance->{$method}(...$args);
    }
}
