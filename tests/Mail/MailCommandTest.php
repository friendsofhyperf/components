<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Mail\Command\MailCommand;
use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Support\Filesystem\Filesystem;
use Symfony\Component\Console\Tester\CommandTester;

beforeEach(function () {
    $this->config = new Config([]);
    $this->swap(ConfigInterface::class, $this->config);
    $this->files = new Filesystem();
    $this->directory = tempnam(sys_get_temp_dir(), 'mail-command-');
    unlink($this->directory);
    $this->files->makeDirectory($this->directory);
});

afterEach(function () {
    $this->files->deleteDirectory($this->directory);
});

it('generates a mailable with the correct content definition', function (string $name, array $options, string $namespace, string $content) {
    $tester = new CommandTester(new MailCommand($this->files, $this->config));

    $tester->execute(['name' => $name, '--path' => $this->directory] + $options);

    $tester->assertCommandIsSuccessful();
    $class = $this->files->get($this->directory . '/WelcomeMail.php');
    expect($class)
        ->toContain('namespace ' . $namespace . ';')
        ->toContain('class WelcomeMail extends Mailable')
        ->toContain("subject: 'Welcome Mail'")
        ->toContain($content)
        ->not->toContain('{{');
})->with([
    'view mailable' => ['WelcomeMail', [], 'App\Mail', "view: 'view.name'"],
    'markdown flag' => ['WelcomeMail', ['--markdown' => true], 'App\Mail', "markdown: 'mail.welcome-mail'"],
    'short markdown flag' => ['WelcomeMail', ['-m' => true], 'App\Mail', "markdown: 'mail.welcome-mail'"],
    'slash namespace' => ['Orders/WelcomeMail', ['--markdown' => true], 'App\Mail\Orders', "markdown: 'mail.orders.welcome-mail'"],
    'backslash namespace' => ['Orders\WelcomeMail', ['--markdown' => true], 'App\Mail\Orders', "markdown: 'mail.orders.welcome-mail'"],
]);

it('respects the configured mail stub', function () {
    $stub = $this->directory . '/custom.stub';
    $this->files->put($stub, '<?php namespace %NAMESPACE%; class %CLASS% { /* custom {{ view }} */ }');
    $this->config->set('devtool.generator.mail.stub', $stub);
    $tester = new CommandTester(new MailCommand($this->files, $this->config));

    $tester->execute(['name' => 'WelcomeMail', '--path' => $this->directory, '--markdown' => true]);

    $tester->assertCommandIsSuccessful();
    expect($this->files->get($this->directory . '/WelcomeMail.php'))
        ->toBe('<?php namespace App\Mail; class WelcomeMail { /* custom mail.welcome-mail */ }');
});
