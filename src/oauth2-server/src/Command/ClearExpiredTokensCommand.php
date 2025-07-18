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

use FriendsOfHyperf\Oauth2\Server\Manager\AccessTokenManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Manager\AuthorizationCodeManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Manager\RefreshTokenManagerInterface;
use Hyperf\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ClearExpiredTokensCommand extends Command
{
    protected ?string $name = 'oauth2-server:clear-expired-tokens';

    public function __construct(
        private readonly AccessTokenManagerInterface $accessTokenManager,
        private readonly RefreshTokenManagerInterface $refreshTokenManager,
        private readonly AuthorizationCodeManagerInterface $authorizationCodeManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Clears all expired access and/or refresh tokens and/or auth codes')
            ->addOption(
                'access-tokens',
                'a',
                InputOption::VALUE_NONE,
                'Clear expired access tokens.'
            )
            ->addOption(
                'refresh-tokens',
                'r',
                InputOption::VALUE_NONE,
                'Clear expired refresh tokens.'
            )
            ->addOption(
                'auth-codes',
                'c',
                InputOption::VALUE_NONE,
                'Clear expired auth codes.'
            );
    }

    protected function handle(): int
    {
        $input = $this->input;
        $output = $this->output;
        $io = new SymfonyStyle($input, $output);
        $clearExpiredAccessTokens = $input->getOption('access-tokens');
        $clearExpiredRefreshTokens = $input->getOption('refresh-tokens');
        $clearExpiredAuthCodes = $input->getOption('auth-codes');
        if (! $clearExpiredAccessTokens && ! $clearExpiredRefreshTokens && ! $clearExpiredAuthCodes) {
            $this->clearExpiredAccessTokens($io);
            $this->clearExpiredRefreshTokens($io);
            $this->clearExpiredAuthCodes($io);

            return 0;
        }

        if ($clearExpiredAccessTokens) {
            $this->clearExpiredAccessTokens($io);
        }

        if ($clearExpiredRefreshTokens) {
            $this->clearExpiredRefreshTokens($io);
        }

        if ($clearExpiredAuthCodes) {
            $this->clearExpiredAuthCodes($io);
        }
        return Command::SUCCESS;
    }

    private function clearExpiredAccessTokens(SymfonyStyle $io): void
    {
        $numOfClearedAccessTokens = $this->accessTokenManager->clearExpired();
        $io->success(\sprintf(
            'Cleared %d expired access token%s.',
            $numOfClearedAccessTokens,
            $numOfClearedAccessTokens === 1 ? '' : 's'
        ));
    }

    private function clearExpiredRefreshTokens(SymfonyStyle $io): void
    {
        $numOfClearedRefreshTokens = $this->refreshTokenManager->clearExpired();
        $io->success(\sprintf(
            'Cleared %d expired refresh token%s.',
            $numOfClearedRefreshTokens,
            $numOfClearedRefreshTokens === 1 ? '' : 's'
        ));
    }

    private function clearExpiredAuthCodes(SymfonyStyle $io): void
    {
        $numOfClearedAuthCodes = $this->authorizationCodeManager->clearExpired();
        $io->success(\sprintf(
            'Cleared %d expired auth code%s.',
            $numOfClearedAuthCodes,
            $numOfClearedAuthCodes === 1 ? '' : 's'
        ));
    }
}
