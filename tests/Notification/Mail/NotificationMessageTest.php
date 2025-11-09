<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Notification\Mail;

use FriendsOfHyperf\Notification\Mail\Message\SimpleMessage as Message;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class NotificationMessageTest extends TestCase
{
    public function testLevelCanBeRetrieved()
    {
        $message = new Message();
        $this->assertSame('info', $message->level);

        $message = new Message();
        $message->level('error');
        $this->assertSame('error', $message->level);
    }

    public function testMessageFormatsMultiLineText()
    {
        $message = new Message();
        $message->with('
            This is a
            single line of text.
        ');

        $this->assertSame('This is a single line of text.', $message->introLines[0]);

        $message = new Message();
        $message->with([
            'This is a',
            'single line of text.',
        ]);

        $this->assertSame('This is a single line of text.', $message->introLines[0]);
    }
}
