<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Oauth2Server;

use FriendsOfHyperf\Oauth2\Server\Manager\InMemory\ScopeManager;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Scope;

uses()->group('oauth2');

it('can find scope', function () {
    $scopeManager = new ScopeManager();
    $scope = new Scope('test_scope');
    $scopeManager->save($scope);

    $foundScope = $scopeManager->find('test_scope');
    expect($foundScope)->toBeInstanceOf(Scope::class)
        ->and((string) $foundScope)->toBe('test_scope');
});
