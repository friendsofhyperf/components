<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Mail;

use Aws\Ses\SesClient;
use Aws\SesV2\SesV2Client;
use Closure;
use FriendsOfHyperf\Mail\Contract\Factory;
use FriendsOfHyperf\Mail\Transport\ArrayTransport;
use FriendsOfHyperf\Mail\Transport\LogTransport;
use FriendsOfHyperf\Mail\Transport\SesTransport;
use FriendsOfHyperf\Mail\Transport\SesV2Transport;
use Hyperf\Collection\Arr;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Stringable\Str;
use Hyperf\ViewEngine\Contract\FactoryInterface;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mailer\Bridge\Mailgun\Transport\MailgunTransportFactory;
use Symfony\Component\Mailer\Bridge\Postmark\Transport\PostmarkApiTransport;
use Symfony\Component\Mailer\Bridge\Postmark\Transport\PostmarkTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\FailoverTransport;
use Symfony\Component\Mailer\Transport\RoundRobinTransport;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Hyperf\Tappable\tap;

class MailManager implements Factory
{
    /**
     * The array of resolved mailers.
     * @var Mailer[]
     */
    protected array $mailers = [];

    /**
     * The registered custom driver creators.
     */
    protected array $customCreators = [];

    public function __construct(
        protected ContainerInterface $container,
        protected ConfigInterface $config
    ) {
    }

    /**
     * Dynamically call the default driver instance.
     * @param mixed $method
     * @param mixed $parameters
     */
    public function __call($method, $parameters)
    {
        return $this->mailer()->{$method}(...$parameters);
    }

    /**
     * Get a mailer instance by name.
     */
    public function mailer(?string $name = null): Mailer
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->mailers[$name] = $this->get($name);
    }

    /**
     * Get a mailer driver instance.
     */
    public function driver(?string $driver = null): Mailer
    {
        return $this->mailer($driver);
    }

    /**
     * Create a new transport instance.
     */
    public function createSymfonyTransport(array $config): TransportInterface
    {
        // Here we will check if the "transport" key exists and if it doesn't we will
        // assume an application is still using the legacy mail configuration file
        // format and use the "mail.driver" configuration option instead for BC.
        $transport = $config['transport'] ?? $this->config->get('mail.driver');

        if (isset($this->customCreators[$transport])) {
            return call_user_func($this->customCreators[$transport], $config);
        }

        if (trim($transport ?? '') === ''
            || ! method_exists($this, $method = 'create' . ucfirst(Str::camel($transport)) . 'Transport')) {
            throw new InvalidArgumentException("Unsupported mail transport [{$transport}].");
        }

        return $this->{$method}($config);
    }

    /**
     * Get the default mail driver name.
     */
    public function getDefaultDriver(): string
    {
        // Here we will check if the "driver" key exists and if it does we will use
        // that as the default driver in order to provide support for old styles
        // of the Laravel mail configuration file for backwards compatibility.
        return $this->config->get('mail.driver') ??
            $this->config->get('mail.default');
    }

    /**
     * Set the default mail driver name.
     */
    public function setDefaultDriver(string $name): void
    {
        if ($this->config->has('mail.driver')) {
            $this->config->set('mail.driver', $name);
        }
        $this->config->set('mail.default', $name);
    }

    /**
     * Disconnect the given mailer and remove from local cache.
     */
    public function purge(?string $name = null): void
    {
        $name = $name ?: $this->getDefaultDriver();

        unset($this->mailers[$name]);
    }

    /**
     * Register a custom transport creator Closure.
     */
    public function extend(string $driver, Closure $callback): static
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * Forget all of the resolved mailer instances.
     */
    public function forgetMailers(): static
    {
        $this->mailers = [];

        return $this;
    }

    /**
     * Attempt to get the mailer from the local cache.
     */
    protected function get(string $name): Mailer
    {
        return $this->mailers[$name] ?? $this->resolve($name);
    }

    /**
     * Resolve the given mailer.
     */
    protected function resolve(string $name): Mailer
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Mailer [{$name}] is not defined.");
        }

        // Once we have created the mailer instance we will set a container instance
        // on the mailer. This allows us to resolve mailer classes via containers
        // for maximum testability on said classes instead of passing Closures.
        $mailer = new Mailer(
            $name,
            $this->container->get(FactoryInterface::class),
            $this->createSymfonyTransport($config),
            $this->container->get(EventDispatcherInterface::class)
        );

        // Next we will set all of the global addresses on this mailer, which allows
        // for easy unification of all "from" addresses as well as easy debugging
        // of sent messages since these will be sent to a single email address.
        foreach (['from', 'reply_to', 'to', 'return_path'] as $type) {
            $this->setGlobalAddress($mailer, $config, $type);
        }

        return $mailer;
    }

    /**
     * Create an instance of the Symfony SMTP Transport driver.
     */
    protected function createSmtpTransport(array $config): EsmtpTransport
    {
        $factory = new EsmtpTransportFactory();

        $scheme = $config['scheme'] ?? null;

        if (! $scheme) {
            $scheme = ! empty($config['encryption']) && $config['encryption'] === 'tls'
                ? (($config['port'] == 465) ? 'smtps' : 'smtp')
                : '';
        }

        /** @var EsmtpTransport $transport */
        $transport = $factory->create(new Dsn(
            $scheme,
            $config['host'],
            $config['username'] ?? null,
            $config['password'] ?? null,
            $config['port'] ?? null,
            $config
        ));

        return $this->configureSmtpTransport($transport, $config);
    }

    /**
     * Configure the additional SMTP driver options.
     */
    protected function configureSmtpTransport(EsmtpTransport $transport, array $config): EsmtpTransport
    {
        $stream = $transport->getStream();

        if ($stream instanceof SocketStream) {
            if (isset($config['source_ip'])) {
                $stream->setSourceIp($config['source_ip']);
            }

            if (isset($config['timeout'])) {
                $stream->setTimeout($config['timeout']);
            }
        }

        return $transport;
    }

    /**
     * Create an instance of the Symfony Sendmail Transport driver.
     */
    protected function createSendmailTransport(array $config): SendmailTransport
    {
        return new SendmailTransport(
            $config['path'] ?? $this->config->get('mail.sendmail')
        );
    }

    /**
     * Create an instance of the Symfony Amazon SES Transport driver.
     */
    protected function createSesTransport(array $config): SesTransport
    {
        $config = array_merge(
            $this->config->get('services.ses', []),
            ['version' => 'latest', 'service' => 'email'],
            $config
        );

        $config = Arr::except($config, ['transport']);

        return new SesTransport(
            new SesClient($this->addSesCredentials($config)),
            $config['options'] ?? []
        );
    }

    /**
     * Create an instance of the Symfony Amazon SES V2 Transport driver.
     */
    protected function createSesV2Transport(array $config): SesV2Transport
    {
        $config = array_merge(
            $this->config->get('services.ses', []),
            ['version' => 'latest'],
            $config
        );

        $config = Arr::except($config, ['transport']);

        return new SesV2Transport(
            new SesV2Client($this->addSesCredentials($config)),
            $config['options'] ?? []
        );
    }

    /**
     * Add the SES credentials to the configuration array.
     */
    protected function addSesCredentials(array $config): array
    {
        if (! empty($config['key']) && ! empty($config['secret'])) {
            $config['credentials'] = Arr::only($config, ['key', 'secret', 'token']);
        }

        return Arr::except($config, ['token']);
    }

    /**
     * Create an instance of the Symfony Mail Transport driver.
     */
    protected function createMailTransport(): SendmailTransport
    {
        return new SendmailTransport();
    }

    /**
     * Create an instance of the Symfony Mailgun Transport driver.
     */
    protected function createMailgunTransport(array $config): TransportInterface
    {
        $factory = new MailgunTransportFactory(null, $this->getHttpClient($config));

        if (! isset($config['secret'])) {
            $config = $this->config->get('services.mailgun', []);
        }

        return $factory->create(new Dsn(
            'mailgun+' . ($config['scheme'] ?? 'https'),
            $config['endpoint'] ?? 'default',
            $config['secret'],
            $config['domain']
        ));
    }

    /**
     * Create an instance of the Symfony Postmark Transport driver.
     */
    protected function createPostmarkTransport(array $config): PostmarkApiTransport
    {
        $factory = new PostmarkTransportFactory(null, $this->getHttpClient($config));
        $options = isset($config['message_stream_id'])
            ? ['message_stream' => $config['message_stream_id']]
            : [];
        /** @var PostmarkApiTransport $transport */
        $transport = $factory->create(new Dsn(
            'postmark+api',
            'default',
            $config['token'] ?? $this->config->get('services.postmark.token'),
            null,
            null,
            $options
        ));

        return tap($transport);
    }

    /**
     * Create an instance of the Symfony Failover Transport driver.
     */
    protected function createFailoverTransport(array $config): FailoverTransport
    {
        $transports = [];

        foreach ($config['mailers'] as $name) {
            $config = $this->getConfig($name);

            if (is_null($config)) {
                throw new InvalidArgumentException("Mailer [{$name}] is not defined.");
            }

            // Now, we will check if the "driver" key exists and if it does we will set
            // the transport configuration parameter in order to offer compatibility
            // with any Laravel <= 6.x application style mail configuration files.
            $transports[] = $this->config->has('mail.driver')
                ? $this->createSymfonyTransport(array_merge($config, ['transport' => $name]))
                : $this->createSymfonyTransport($config);
        }

        return new FailoverTransport($transports);
    }

    /**
     * Create an instance of the Symfony Roundrobin Transport driver.
     */
    protected function createRoundrobinTransport(array $config): RoundRobinTransport
    {
        $transports = [];

        foreach ($config['mailers'] as $name) {
            $config = $this->getConfig($name);

            if (is_null($config)) {
                throw new InvalidArgumentException("Mailer [{$name}] is not defined.");
            }

            // Now, we will check if the "driver" key exists and if it does we will set
            // the transport configuration parameter in order to offer compatibility
            // with any Laravel <= 6.x application style mail configuration files.
            $transports[] = $this->config->has('mail.driver')
                ? $this->createSymfonyTransport(array_merge($config, ['transport' => $name]))
                : $this->createSymfonyTransport($config);
        }

        return new RoundRobinTransport($transports);
    }

    /**
     * Create an instance of the Log Transport driver.
     */
    protected function createLogTransport(array $config): LogTransport
    {
        $loggerFactory = $this->container->get(LoggerFactory::class);

        $logger = $loggerFactory->make(
            $config['name'] ?: $this->config->get('mail.log.name'),
            $config['group'] ?: $this->config->get('mail.log.group')
        );

        return new LogTransport($logger);
    }

    /**
     * Create an instance of the Array Transport Driver.
     */
    protected function createArrayTransport(): ArrayTransport
    {
        return new ArrayTransport();
    }

    /**
     * Get a configured Symfony HTTP client instance.
     */
    protected function getHttpClient(array $config): ?HttpClientInterface
    {
        if ($options = ($config['client'] ?? false)) {
            $maxHostConnections = Arr::pull($options, 'max_host_connections', 6);
            $maxPendingPushes = Arr::pull($options, 'max_pending_pushes', 50);

            return HttpClient::create($options, $maxHostConnections, $maxPendingPushes);
        }
        return null;
    }

    /**
     * Set a global address on the mailer by type.
     */
    protected function setGlobalAddress(Mailer $mailer, array $config, string $type): void
    {
        $address = Arr::get($config, $type, $this->config->get('mail.' . $type));

        if (is_array($address) && isset($address['address'])) {
            $mailer->{'always' . Str::studly($type)}($address['address'], $address['name']);
        }
    }

    /**
     * Get the mail connection configuration.
     */
    protected function getConfig(string $name): ?array
    {
        // Here we will check if the "driver" key exists and if it does we will use
        // the entire mail configuration file as the "driver" config in order to
        // provide "BC" for any Laravel <= 6.x style mail configuration files.
        $config = $this->config->has('mail.driver') ? $this->config->get('mail') : $this->config->get("mail.mailers.{$name}");
        if (isset($config['url'])) {
            $config = array_merge($config, (new ConfigurationUrlParser())->parseConfiguration($config));

            $config['transport'] = Arr::pull($config, 'driver');
        }

        return $config;
    }
}
