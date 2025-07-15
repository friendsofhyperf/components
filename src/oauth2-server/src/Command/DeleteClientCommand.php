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
use Hyperf\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DeleteClientCommand extends Command
{
    protected ?string $name = 'oauth2-server:delete-client';

    public function __construct(
        private readonly ClientManagerInterface $clientManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Deletes an OAuth2 client')
            ->addArgument(
                'identifier',
                InputArgument::REQUIRED,
                'The client ID'
            );
    }

    protected function handle()
    {
        $io = new SymfonyStyle($this->input, $this->output);

        if (null === $client = $this->clientManager->find($this->input->getArgument('identifier'))) {
            $io->error(\sprintf('OAuth2 client identified as "%s" does not exist.', $this->input->getArgument('identifier')));

            return Command::FAILURE;
        }

        $this->clientManager->remove($client);
        $io->success('OAuth2 client deleted successfully.');
        return Command::SUCCESS;
    }
}
