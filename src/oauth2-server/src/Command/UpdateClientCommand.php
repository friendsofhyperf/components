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
use FriendsOfHyperf\Oauth2\Server\ValueObject\Grant;
use FriendsOfHyperf\Oauth2\Server\ValueObject\RedirectUri;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Scope;
use Hyperf\Command\Command;
use RuntimeException;
use Stringable;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

final class UpdateClientCommand extends Command
{
    protected ?string $name = 'oauth2-server:update-client';

    public function __construct(
        private readonly ClientManagerInterface $clientManager
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $input = $this->input;
        $output = $this->output;
        $io = new SymfonyStyle($input, $output);
        if (null === $client = $this->clientManager->find($input->getArgument('identifier'))) {
            $io->error(\sprintf('OAuth2 client identified as "%s" does not exist.', $input->getArgument('identifier')));

            return Command::FAILURE;
        }

        $client->setActive($this->getClientActiveFromInput($input, $client->isActive()));
        $client->setRedirectUris(...$this->getClientRelatedModelsFromInput($input, RedirectUri::class, $client->getRedirectUris(), 'redirect-uri'));
        $client->setGrants(...$this->getClientRelatedModelsFromInput($input, Grant::class, $client->getGrants(), 'grant-type'));
        $client->setScopes(...$this->getClientRelatedModelsFromInput($input, Scope::class, $client->getScopes(), 'scope'));

        $this->clientManager->save($client);

        $io->success('OAuth2 client updated successfully.');
        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Updates an OAuth2 client')

            ->addOption('add-redirect-uri', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Add allowed redirect uri to the client.', [])
            ->addOption('remove-redirect-uri', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Remove allowed redirect uri to the client.', [])

            ->addOption('add-grant-type', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Add allowed grant type to the client.', [])
            ->addOption('remove-grant-type', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Remove allowed grant type to the client.', [])

            ->addOption('add-scope', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Add allowed scope to the client.', [])
            ->addOption('remove-scope', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Remove allowed scope to the client.', [])

            ->addOption('activate', null, InputOption::VALUE_NONE, 'Activate the client.')
            ->addOption('deactivate', null, InputOption::VALUE_NONE, 'Deactivate the client.')

            ->addArgument('identifier', InputArgument::REQUIRED, 'The client identifier');
    }

    private function getClientActiveFromInput(InputInterface $input, bool $actual): bool
    {
        $active = $actual;

        if ($input->getOption('activate') && $input->getOption('deactivate')) {
            throw new RuntimeException('Cannot specify "--activate" and "--deactivate" at the same time.');
        }

        if ($input->getOption('activate')) {
            $active = true;
        }

        if ($input->getOption('deactivate')) {
            $active = false;
        }

        return $active;
    }

    /**
     * @template T of RedirectUri|Grant|Scope
     *
     * @param list<Stringable> $actual
     * @param class-string<T> $modelFqcn
     *
     * @return list<T>
     */
    private function getClientRelatedModelsFromInput(InputInterface $input, string $modelFqcn, array $actual, string $argument): array
    {
        /** @var list<non-empty-string> $toAdd */
        $toAdd = $input->getOption($addArgument = \sprintf('add-%s', $argument));

        /** @var list<non-empty-string> $toRemove */
        $toRemove = $input->getOption($removeArgument = \sprintf('remove-%s', $argument));

        if ([] !== $colliding = array_intersect($toAdd, $toRemove)) {
            throw new RuntimeException(\sprintf('Cannot specify "%s" in either "--%s" and "--%s".', implode('", "', $colliding), $addArgument, $removeArgument));
        }

        $filtered = array_filter($actual, static function ($model) use ($toRemove): bool {
            return ! \in_array((string) $model, $toRemove);
        });

        /* @var list<T> */
        return array_merge($filtered, array_map(static function (string $value) use ($modelFqcn) {
            return new $modelFqcn($value);
        }, $toAdd));
    }
}
