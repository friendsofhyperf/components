<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Oauth2\Server\Repository;

use FriendsOfHyperf\Oauth2\Server\Converter\ClientConverterInterface;
use FriendsOfHyperf\Oauth2\Server\Converter\ScopeConverterInterface;
use FriendsOfHyperf\Oauth2\Server\Entity\DeviceCode as DeviceCodeEntity;
use FriendsOfHyperf\Oauth2\Server\Manager\ClientManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Manager\DeviceCodeManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Model\Device as DeviceCodeModel;
use FriendsOfHyperf\Oauth2\Server\Model\DeviceCodeInterface;
use League\OAuth2\Server\Entities\DeviceCodeEntityInterface;
use League\OAuth2\Server\Repositories\DeviceCodeRepositoryInterface;
use RuntimeException;

use function Hyperf\Tappable\tap;

final class DeviceCodeRepository implements DeviceCodeRepositoryInterface
{
    public function __construct(
        private readonly DeviceCodeManagerInterface $manager,
        private readonly ClientManagerInterface $clientManager,
        private readonly ScopeConverterInterface $scopeConverter,
        private readonly ClientConverterInterface $clientConverter
    ) {
    }

    public function getNewDeviceCode(): DeviceCodeEntityInterface
    {
        return new DeviceCodeEntity();
    }

    public function persistDeviceCode(DeviceCodeEntityInterface $deviceCodeEntity): void
    {
        $this->manager->save($this->buildDeviceCodeModel($deviceCodeEntity));
    }

    public function getDeviceCodeEntityByDeviceCode(string $deviceCodeEntity): ?DeviceCodeEntityInterface
    {
        $deviceCodeModel = $this->manager->findByDeviceCode($deviceCodeEntity);

        if ($deviceCodeModel === null) {
            return null;
        }

        return $this->buildDeviceCodeEntity($deviceCodeModel);
    }

    public function revokeDeviceCode(string $codeId): void
    {
        $deviceCodeModel = $this->manager->findByDeviceCode($codeId);
        if ($deviceCodeModel === null) {
            return;
        }
        $deviceCodeModel->revoke();

        $this->manager->save($deviceCodeModel);
    }

    public function isDeviceCodeRevoked(string $codeId): bool
    {
        $deviceCodeModel = $this->manager->findByDeviceCode($codeId);
        if ($deviceCodeModel === null) {
            return true;
        }

        return $deviceCodeModel->isRevoked();
    }

    private function buildDeviceCodeEntity(DeviceCodeInterface $deviceCodeModel): DeviceCodeEntityInterface
    {
        $deviceCodeEntity = new DeviceCodeEntity();
        $deviceCodeEntity->setIdentifier($deviceCodeModel->getDeviceCode());
        $deviceCodeEntity->setUserIdentifier($deviceCodeModel->getUserIdentifier());
        $deviceCodeEntity->setUserCode($deviceCodeModel->getUserCode());
        $deviceCodeEntity->setExpiryDateTime($deviceCodeModel->getExpiry());
        foreach ($deviceCodeModel->getScopes() as $scope) {
            $deviceCodeEntity->addScope($this->scopeConverter->toLeague($scope));
        }
        $client = $this->clientManager->find($deviceCodeModel->getClientIdentifier());
        if ($client !== null) {
            $deviceCodeEntity->setClient($this->clientConverter->toEntity($client));
        }
        $deviceCodeEntity->setLastPolledAt($deviceCodeModel->getLastPoll());
        $deviceCodeEntity->setUserApproved($deviceCodeModel->getStatus()->isApproved());
        return $deviceCodeEntity;
    }

    private function buildDeviceCodeModel(DeviceCodeEntityInterface $deviceCodeEntity): DeviceCodeInterface
    {
        $client = $this->clientManager->find($deviceCodeEntity->getClient()->getIdentifier());
        if ($client === null) {
            throw new RuntimeException(sprintf('Client with identifier %s not found', $deviceCodeEntity->getClient()->getIdentifier()));
        }
        return tap(new DeviceCodeModel(), function (DeviceCodeModel $model) use ($deviceCodeEntity, $client) {
            $model->device_code = $deviceCodeEntity->getIdentifier();
            $model->setDeviceCode($deviceCodeEntity->getIdentifier());
            $model->setUserCode($deviceCodeEntity->getUserCode());
            $model->setScopes(...$this->scopeConverter->toDomainArray($deviceCodeEntity->getScopes()));
            $model->setClientIdentifier($client->getIdentifier());
            $model->setUserIdentifier($deviceCodeEntity->getUserIdentifier());
            $model->setExpiry($deviceCodeEntity->getExpiryDateTime());
        });
    }
}
