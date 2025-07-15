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

use FriendsOfHyperf\Oauth2\Server\Manager\ClientFilter;
use FriendsOfHyperf\Oauth2\Server\Manager\ClientManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Model\ClientInterface;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Grant;
use FriendsOfHyperf\Oauth2\Server\ValueObject\RedirectUri;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Scope;
use Hyperf\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ListClientsCommand extends Command
{
    private const ALLOWED_COLUMNS = ['name', 'identifier', 'secret', 'scope', 'redirect uri', 'grant type'];

    protected ?string $name = 'oauth2-server:list-clients';

    public function __construct(
        private readonly ClientManagerInterface $clientManager
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $criteria = $this->getFindByCriteria($this->input);
        $clients = $this->clientManager->list($criteria);
        $this->drawTable($this->input, $this->output, $clients);
        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Lists existing OAuth2 clients')
            ->addOption(
                'columns',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Determine which columns are shown. Can be used multiple times to specify multiple columns.',
                self::ALLOWED_COLUMNS
            )
            ->addOption(
                'redirect-uri',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Finds by redirect uri for client. Use this option multiple times to filter by multiple redirect URIs.',
                []
            )
            ->addOption(
                'grant-type',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Finds by allowed grant type for client. Use this option multiple times to filter by multiple grant types.',
                []
            )
            ->addOption(
                'scope',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Finds by allowed scope for client. Use this option multiple times to find by multiple scopes.',
                []
            );
    }

    private function getFindByCriteria(InputInterface $input): ClientFilter
    {
        /** @var list<non-empty-string> $grantStrings */
        $grantStrings = $input->getOption('grant-type');
        /** @var list<non-empty-string> $redirectUriStrings */
        $redirectUriStrings = $input->getOption('redirect-uri');
        /** @var list<non-empty-string> $scopeStrings */
        $scopeStrings = $input->getOption('scope');

        return ClientFilter::create()
            ->addGrantCriteria(...array_map(static function (string $grant): Grant {
                return new Grant($grant);
            }, $grantStrings))
            ->addRedirectUriCriteria(...array_map(static function (string $redirectUri): RedirectUri {
                return new RedirectUri($redirectUri);
            }, $redirectUriStrings))
            ->addScopeCriteria(...array_map(static function (string $scope): Scope {
                return new Scope($scope);
            }, $scopeStrings));
    }

    /**
     * @param array<ClientInterface> $clients
     */
    private function drawTable(InputInterface $input, OutputInterface $output, array $clients): void
    {
        $io = new SymfonyStyle($input, $output);
        $columns = $this->getColumns($input);
        $rows = $this->getRows($clients, $columns);
        $io->table($columns, $rows);
    }

    /**
     * @param array<ClientInterface> $clients
     * @param array<string> $columns
     *
     * @return array<array<string>>
     */
    private function getRows(array $clients, array $columns): array
    {
        return array_map(static function (ClientInterface $client) use ($columns): array {
            $values = [
                'name' => $client->getName(),
                'identifier' => $client->getIdentifier(),
                'secret' => $client->getSecret(),
                'scope' => implode(', ', $client->getScopes()),
                'redirect uri' => implode(', ', $client->getRedirectUris()),
                'grant type' => implode(', ', $client->getGrants()),
            ];

            return array_map(static function (string $column) use ($values): string {
                return $values[$column] ?? '';
            }, $columns);
        }, $clients);
    }

    /**
     * @return array<string>
     */
    private function getColumns(InputInterface $input): array
    {
        $requestedColumns = $input->getOption('columns');
        $requestedColumns = array_map(static function (string $column): string {
            return strtolower(trim($column));
        }, $requestedColumns);

        return array_intersect($requestedColumns, self::ALLOWED_COLUMNS);
    }
}
