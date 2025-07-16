<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Oauth2\Server\Command;

use FriendsOfHyperf\Oauth2\Server\Interfaces\ConfigInterface;
use Hyperf\Command\Command;
use Hyperf\Support\Filesystem\Filesystem;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

use const OPENSSL_KEYTYPE_DH;
use const OPENSSL_KEYTYPE_EC;
use const OPENSSL_KEYTYPE_RSA;

final class GenerateKeyPairCommand extends Command
{
    private const ACCEPTED_ALGORITHMS = [
        'RS256',
        'RS384',
        'RS512',
        'HS256',
        'HS384',
        'HS512',
        'ES256',
        'ES384',
        'ES512',
    ];

    protected ?string $name = 'oauth2-server:generate-keypair';

    private string $secretKey;

    private string $publicKey;

    private ?string $passphrase;

    public function __construct(
        private readonly Filesystem $filesystem,
        ConfigInterface $config
    ) {
        parent::__construct();
        $this->secretKey = $config->get('authorization_server.private_key', '');
        $this->publicKey = $config->get('resource_server.public_key', '');
        $this->passphrase = $config->get('authorization_server.private_key_passphrase', '');
    }

    protected function handle(): int
    {
        $input = $this->input;
        $output = $this->output;
        $io = new SymfonyStyle($input, $output);
        $algorithm = $input->getArgument('algorithm');

        if (! \in_array($algorithm, self::ACCEPTED_ALGORITHMS, true)) {
            $io->error(\sprintf('Cannot generate key pair with the provided algorithm `%s`.', $algorithm));
            return Command::FAILURE;
        }

        [$secretKey, $publicKey] = $this->generateKeyPair($this->passphrase, $algorithm);

        if ($input->getOption('dry-run')) {
            $io->success('Your keys have been generated!');
            $io->newLine();
            $io->writeln(\sprintf('Update your private key in <info>%s</info>:', $this->secretKey));
            $io->writeln($secretKey);
            $io->newLine();
            $io->writeln(\sprintf('Update your public key in <info>%s</info>:', $this->publicKey));
            $io->writeln($publicKey);

            return Command::SUCCESS;
        }

        $alreadyExists = $this->filesystem->exists($this->secretKey) || $this->filesystem->exists($this->publicKey);

        if ($alreadyExists) {
            try {
                $this->handleExistingKeys($input);
            } catch (RuntimeException $e) {
                if ($e->getCode() === 0) {
                    $io->comment($e->getMessage());

                    return Command::SUCCESS;
                }

                $io->error($e->getMessage());

                return Command::FAILURE;
            }

            if (! $io->confirm('You are about to replace your existing keys. Are you sure you wish to continue?')) {
                $io->comment('Your action was canceled.');

                return Command::SUCCESS;
            }
        }
        $this->filesystem->put(
            $this->publicKey,
            $publicKey
        );
        $this->filesystem->put(
            $this->secretKey,
            $secretKey
        );
        $io->success('Done!');
        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->setDescription('Generate public/private keys for use in your application.');
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Do not update key files.');
        $this->addOption('skip-if-exists', null, InputOption::VALUE_NONE, 'Do not update key files if they already exist.');
        $this->addOption('overwrite', null, InputOption::VALUE_NONE, 'Overwrite key files if they already exist.');
        $this->addArgument('algorithm', InputArgument::OPTIONAL, \sprintf('The algorithm code, possible values : %s', implode(',', self::ACCEPTED_ALGORITHMS)), 'RS256');
    }

    private function handleExistingKeys(InputInterface $input): void
    {
        if ($input->getOption('skip-if-exists') && $input->getOption('overwrite')) {
            throw new RuntimeException('Both options `--skip-if-exists` and `--overwrite` cannot be combined.', 1);
        }

        if ($input->getOption('skip-if-exists')) {
            throw new RuntimeException('Your key files already exist, they won\'t be overridden.', 0);
        }

        if (! $input->getOption('overwrite')) {
            throw new RuntimeException('Your keys already exist. Use the `--overwrite` option to force regeneration.', 1);
        }
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function generateKeyPair(?string $passphrase, string $algorithm): array
    {
        $config = $this->buildOpenSSLConfiguration($algorithm);

        $resource = openssl_pkey_new($config);
        if ($resource === false) {
            throw new RuntimeException(openssl_error_string() ?: 'Failed to generate key pair');
        }

        $success = openssl_pkey_export($resource, $privateKey, $passphrase);

        if ($success === false) {
            throw new RuntimeException(openssl_error_string() ?: '');
        }

        $publicKeyData = openssl_pkey_get_details($resource);

        if (! \is_array($publicKeyData)) {
            throw new RuntimeException(openssl_error_string() ?: '');
        }

        if (! \array_key_exists('key', $publicKeyData) || ! \is_string($publicKeyData['key'])) {
            throw new RuntimeException('Invalid public key type.');
        }

        return [$privateKey, $publicKeyData['key']];
    }

    /**
     * @return mixed[]
     */
    private function buildOpenSSLConfiguration(string $algorithm): array
    {
        $digestAlgorithms = [
            'RS256' => 'sha256',
            'RS384' => 'sha384',
            'RS512' => 'sha512',
            'HS256' => 'sha256',
            'HS384' => 'sha384',
            'HS512' => 'sha512',
            'ES256' => 'sha256',
            'ES384' => 'sha384',
            'ES512' => 'sha512',
        ];
        $privateKeyBits = [
            'RS256' => 2048,
            'RS384' => 2048,
            'RS512' => 4096,
            'HS256' => 512,
            'HS384' => 512,
            'HS512' => 512,
            'ES256' => 384,
            'ES384' => 512,
            'ES512' => 1024,
        ];
        $privateKeyTypes = [
            'RS256' => OPENSSL_KEYTYPE_RSA,
            'RS384' => OPENSSL_KEYTYPE_RSA,
            'RS512' => OPENSSL_KEYTYPE_RSA,
            'HS256' => OPENSSL_KEYTYPE_DH,
            'HS384' => OPENSSL_KEYTYPE_DH,
            'HS512' => OPENSSL_KEYTYPE_DH,
            'ES256' => OPENSSL_KEYTYPE_EC,
            'ES384' => OPENSSL_KEYTYPE_EC,
            'ES512' => OPENSSL_KEYTYPE_EC,
        ];

        $curves = [
            'ES256' => 'secp256r1',
            'ES384' => 'secp384r1',
            'ES512' => 'secp521r1',
        ];

        $config = [
            'digest_alg' => $digestAlgorithms[$algorithm],
            'private_key_type' => $privateKeyTypes[$algorithm],
            'private_key_bits' => $privateKeyBits[$algorithm],
        ];

        if (isset($curves[$algorithm])) {
            $config['curve_name'] = $curves[$algorithm];
        }

        return $config;
    }
}
