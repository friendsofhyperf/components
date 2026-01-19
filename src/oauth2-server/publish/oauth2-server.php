<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Oauth2\Server\Enums\EncryptionKeyType;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;

use function Hyperf\Support\env;

return [
    'authorization_server' => [
        # Full path to the private key file.
        # How to generate a private key: https://oauth2.thephpleague.com/installation/#generating-public-and-private-keys
        'private_key' => env('OAUTH2_PRIVATE_KEY'),
        # Passphrase of the private key, if any
        'private_key_passphrase' => env('OAUTH2_PRIVATE_KEY_PASSPHRASE'),
        # The plain string or the ascii safe string used to create a Defuse\Crypto\Key to be used as an encryption key.
        # How to generate an encryption key: https://oauth2.thephpleague.com/installation/#string-password
        'encryption_key' => env('OAUTH2_ENCRYPTION_KEY'),
        # The encryption key type.
        'encryption_key_type' => EncryptionKeyType::from(env('OAUTH2_ENCRYPTION_KEY_TYPE', 'plain')),
        'response_type' => BearerTokenResponse::class,
        # Whether to revoke refresh tokens after they were used for all grant types (default to true)
        'revoke_refresh_tokens' => true,
        # How long the issued access token should be valid for.
        # The value should be a valid interval: http://ph
        'access_token_ttl' => new DateInterval('PT1H'),
        # How long the issued auth code should be valid for.
        # The value should be a valid interval: http://php.net/manual/en/dateinterval.construct.php#refsect1-dateinterval.construct-parameters
        'auth_code_ttl' => new DateInterval('PT10M'),
        # How long the issued refresh token should be valid for.
        # The value should be a valid interval: http://php.net/manual/en/dateinterval.construct.php#refsect1-dateinterval.construct-parameters
        'refresh_token_ttl' => new DateInterval('P1M'),
        # Whether to enable the client credentials grant
        'enable_client_credentials_grant' => true,
        # Whether to enable the password grant
        'enable_password_grant' => true,
        # Whether to enable the refresh token grant
        'enable_refresh_token_grant' => true,
        # Whether to enable the authorization code grant
        'enable_auth_code_grant' => true,
        'enable_implicit_grant' => false,
        # Whether to require code challenge for public clients for the auth code grant
        'require_code_challenge_for_public_clients' => true,
        # Whether to enable access token saving to persistence layer (default to true)
        'persist_access_token' => true,
    ],
    'resource_server' => [
        # Full path to the public key file
        # How to generate a public key: https://oauth2.thephpleague.com/installation/#generating-public-and-private-keys
        'public_key' => env('OAUTH2_PUBLIC_KEY'),
        # The leeway in seconds to allow for clock skew in JWT verification. Default PT0S (no leeway).
        'jwt_leeway' => null,
    ],

    'scopes' => [
        # Scopes that you wish to utilize in your application.
        # This should be a simple array of strings.
        'available' => [],
        # Scopes that will be assigned when no scope given.
        # This should be a simple array of strings.
        'default' => [],
    ],
];
