<?php

namespace FriendsOfHyperf\Tests\Oauth2Server;

use FriendsOfHyperf\Oauth2\Server\Manager\InMemory\ScopeManager;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Scope;
use function Hyperf\Support\make;

uses()->group('oauth2');

it('can find scope', function () {
    $scopeManager = new ScopeManager;
    $scope = new Scope('test_scope');
    $scopeManager->save($scope);

    $foundScope = $scopeManager->find('test_scope');
    expect($foundScope)->toBeInstanceOf(Scope::class)
        ->and((string)$foundScope)->toBe('test_scope');
});