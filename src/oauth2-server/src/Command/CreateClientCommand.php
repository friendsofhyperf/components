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

use FriendsOfHyperf\Oauth2\Server\Manager\ClientManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Model\ClientInterface;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Grant;
use FriendsOfHyperf\Oauth2\Server\ValueObject\RedirectUri;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Scope;
use Hyperf\Command\Command;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

final class CreateClientCommand extends Command
{
    protected ?string $name = 'oauth2-server:create-client';

    public function __construct(
        private readonly ClientManagerInterface $clientManager,
        private readonly ClientInterface $client
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Creates a new OAuth2 client')
            ->addOption(
                'redirect-uri',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Sets redirect uri for client. Use this option multiple times to set multiple redirect URIs.',
                []
            )
            ->addOption(
                'grant-type',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Sets allowed grant type for client. Use this option multiple times to set multiple grant types.',
                []
            )
            ->addOption(
                'scope',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Sets allowed scope for client. Use this option multiple times to set multiple scopes.',
                []
            )
            ->addOption(
                'public',
                null,
                InputOption::VALUE_NONE,
                'Create a public client.'
            )
            ->addOption(
                'allow-plain-text-pkce',
                null,
                InputOption::VALUE_NONE,
                'Create a client who is allowed to use plain challenge method for PKCE.'
            )
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'The client name'
            )
            ->addArgument(
                'identifier',
                InputArgument::OPTIONAL,
                'The client identifier'
            )
            ->addArgument(
                'secret',
                InputArgument::OPTIONAL,
                'The client secret'
            );
    }

    protected function handle(): int
    {
        $input = $this->input;
        $output = $this->output;
        $io = new SymfonyStyle($input, $output);
        try {
            $client = $this->buildClientFromInput($input);
        } catch (InvalidArgumentException $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }
        $this->clientManager->save($client);
        $io->success('New OAuth2 client created successfully.');

        $headers = ['Identifier', 'Secret'];
        $rows = [
            [$client->getIdentifier(), $client->getSecret()],
        ];
        $io->table($headers, $rows);
        return Command::SUCCESS;
    }

    private function buildClientFromInput(InputInterface $input): ClientInterface
    {
        $name = $input->getArgument('name');
        $identifier = (string) $input->getArgument('identifier') ?: hash('md5', random_bytes(16));
        $isPublic = $input->getOption('public');
        if ($isPublic && $input->getArgument('secret') !== null) {
            throw new InvalidArgumentException('The client cannot have a secret and be public.');
        }
        $secret = $isPublic ? null : $input->getArgument('secret') ?? hash('sha512', random_bytes(32));
        $client = $this->client->newClientInstance($name, $identifier, $secret);
        $client->setActive(true);
        $client->setAllowPlainTextPkce($input->getOption('allow-plain-text-pkce'));

        /** @var list<non-empty-string> $redirectUriStrings */
        $redirectUriStrings = $input->getOption('redirect-uri');
        /** @var list<non-empty-string> $grantStrings */
        $grantStrings = $input->getOption('grant-type');
        /** @var list<non-empty-string> $scopeStrings */
        $scopeStrings = $input->getOption('scope');
        return $client
            ->setRedirectUris(...array_map(static function (string $redirectUri): RedirectUri {
                return new RedirectUri($redirectUri);
            }, $redirectUriStrings))
            ->setGrants(...array_map(static function (string $grant): Grant {
                return new Grant($grant);
            }, $grantStrings))
            ->setScopes(...array_map(static function (string $scope): Scope {
                return new Scope($scope);
            }, $scopeStrings));
    }
}
