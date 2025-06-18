<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Encryption\Encrypter;
use FriendsOfHyperf\Encryption\EncrypterFactory;
use FriendsOfHyperf\Support\HtmlString;
use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;

test('test deduplicate', function () {
    $this->assertSame(' laravel php framework ', (string) $this->stringable(' laravel   php  framework ')->deduplicate());
    $this->assertSame('what', (string) $this->stringable('whaaat')->deduplicate('a'));
    $this->assertSame('/some/odd/path/', (string) $this->stringable('/some//odd//path/')->deduplicate('/'));
    $this->assertSame('ムだム', (string) $this->stringable('ムだだム')->deduplicate('だ'));
});

test('test hash', function () {
    $this->assertSame(hash('xxh3', 'foo'), (string) $this->stringable('foo')->hash('xxh3'));
    $this->assertSame(hash('xxh3', 'foobar'), (string) $this->stringable('foobar')->hash('xxh3'));
    $this->assertSame(hash('sha256', 'foobarbaz'), (string) $this->stringable('foobarbaz')->hash('sha256'));
});

test('test toHtmlString', function () {
    $this->assertEquals(
        new HtmlString('<h1>Test string</h1>'),
        $this->stringable('<h1>Test string</h1>')->toHtmlString()
    );
});

test('test markdown', function ($expected, $markdown) {
    expect((string) $this->stringable($markdown)->markdown())->toBe($expected);
})->with([
    ["<p><em>hello world</em></p>\n", '*hello world*'],
    ["<h1>hello world</h1>\n", '# hello world'],
]);

test('test inlineMarkdown', function ($expected, $markdown) {
    expect((string) $this->stringable($markdown)->inlineMarkdown())->toBe($expected);
})->with([
    ["<em>hello world</em>\n", '*hello world*'],
    ["<a href=\"https://laravel.com\"><strong>Laravel</strong></a>\n", '[**Laravel**](https://laravel.com)'],
]);

test('test newLine', function () {
    $this->assertSame('Laravel' . PHP_EOL, (string) $this->stringable('Laravel')->newLine());
    $this->assertSame('foo' . PHP_EOL . PHP_EOL . 'bar', (string) $this->stringable('foo')->newLine(2)->append('bar'));
});

test('test whenIsAscii', function () {
    $this->assertSame('Ascii: A', (string) $this->stringable('A')->whenIsAscii(function ($stringable) {
        return $stringable->prepend('Ascii: ');
    }, function ($stringable) {
        return $stringable->prepend('Not Ascii: ');
    }));

    $this->assertSame('ù', (string) $this->stringable('ù')->whenIsAscii(function ($stringable) {
        return $stringable->prepend('Ascii: ');
    }));

    $this->assertSame('Not Ascii: ù', (string) $this->stringable('ù')->whenIsAscii(function ($stringable) {
        return $stringable->prepend('Ascii: ');
    }, function ($stringable) {
        return $stringable->prepend('Not Ascii: ');
    }));
});

test('test encryptAndDecrypt', function () {
    $this->instance(ConfigInterface::class, new Config([
        'encryption' => [
            'key' => 'base64:MhEHk72OcV2ttAljUu9Caaam3iP2BnGcwb6GWKkUfV4=',
            'cipher' => 'AES-256-CBC',
        ],
    ]));
    $this->instance(Encrypter::class, (new EncrypterFactory())($this->container));
    $encrypted = $this->stringable('foo')->encrypt();

    $this->assertNotSame('foo', $encrypted->value());
    $this->assertSame('foo', $encrypted->decrypt()->value());
});
