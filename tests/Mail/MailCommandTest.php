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

use FriendsOfHyperf\Mail\Command\MailCommand;
use FriendsOfHyperf\Tests\Concerns\InteractsWithContainer;
use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Support\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
#[Group('mail')]
class MailCommandTest extends TestCase
{
    use InteractsWithContainer;

    private string $directory;

    private Filesystem $files;

    private Config $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshContainer();
        $this->config = new Config([]);
        $this->swap(ConfigInterface::class, $this->config);
        $this->files = new Filesystem();
        $this->directory = tempnam(sys_get_temp_dir(), 'mail-command-');
        unlink($this->directory);
        $this->files->makeDirectory($this->directory);
    }

    protected function tearDown(): void
    {
        $this->files->deleteDirectory($this->directory);
        $this->flushContainer();
        parent::tearDown();
    }

    #[DataProvider('mailOptions')]
    public function testGeneratesMailable(string $name, array $options, string $namespace, string $content): void
    {
        $tester = new CommandTester(new MailCommand($this->files, $this->config));

        $tester->execute(['name' => $name, '--path' => $this->directory] + $options);

        $tester->assertCommandIsSuccessful();
        $class = $this->files->get($this->directory . '/WelcomeMail.php');
        $this->assertStringContainsString('namespace ' . $namespace . ';', $class);
        $this->assertStringContainsString('class WelcomeMail extends Mailable', $class);
        $this->assertStringContainsString("subject: 'Welcome Mail'", $class);
        $this->assertStringContainsString($content, $class);
        $this->assertStringNotContainsString('{{', $class);
    }

    public static function mailOptions(): array
    {
        return [
            'view mailable' => ['WelcomeMail', [], 'App\Mail', "view: 'view.name'"],
            'markdown flag' => ['WelcomeMail', ['--markdown' => true], 'App\Mail', "markdown: 'mail.welcome-mail'"],
            'short markdown flag' => ['WelcomeMail', ['-m' => true], 'App\Mail', "markdown: 'mail.welcome-mail'"],
            'slash namespace' => ['Orders/WelcomeMail', ['--markdown' => true], 'App\Mail\Orders', "markdown: 'mail.orders.welcome-mail'"],
            'backslash namespace' => ['Orders\WelcomeMail', ['--markdown' => true], 'App\Mail\Orders', "markdown: 'mail.orders.welcome-mail'"],
        ];
    }

    public function testRespectsConfiguredStub(): void
    {
        $stub = $this->directory . '/custom.stub';
        $this->files->put($stub, '<?php namespace %NAMESPACE%; class %CLASS% { /* custom {{ view }} */ }');
        $this->config->set('devtool.generator.mail.stub', $stub);
        $tester = new CommandTester(new MailCommand($this->files, $this->config));

        $tester->execute(['name' => 'WelcomeMail', '--path' => $this->directory, '--markdown' => true]);

        $tester->assertCommandIsSuccessful();
        $this->assertSame(
            '<?php namespace App\Mail; class WelcomeMail { /* custom mail.welcome-mail */ }',
            $this->files->get($this->directory . '/WelcomeMail.php')
        );
    }
}
