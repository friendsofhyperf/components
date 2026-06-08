# OpenAI Client

This component integrates [openai-php/client](https://github.com/openai-php/client) with Hyperf. It
registers the upstream client in the dependency injection container and provides a static facade.

## Requirements

The package targets Hyperf 3.2 and requires `hyperf/config`, `hyperf/di`, `hyperf/guzzle`, and
`openai-php/client` version 0.10.0 or later. It does not declare any optional dependencies.

## Installation

```shell
composer require friendsofhyperf/openai-client
```

Publish the configuration file:

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/openai-client
```

This creates `config/autoload/openai.php`.

## Configuration

The published configuration reads these environment variables:

```env
OPENAI_BASE_URI=api.openai.com/v1
OPENAI_API_KEY=sk-...
OPENAI_ORGANIZATION=
OPENAI_REQUEST_TIMEOUT=30
```

| Configuration key | Default | Description |
| --- | --- | --- |
| `openai.base_uri` | `api.openai.com/v1` | Base URI passed to the upstream client factory. |
| `openai.api_key` | Empty string | API key used for bearer authentication. |
| `openai.organization` | `null` | Optional OpenAI organization identifier. |
| `openai.request_timeout` | `30` | Timeout, in seconds, passed to the Hyperf Guzzle client. |

## Container Bindings and Runtime Behavior

The component binds both `OpenAI\Client` and `OpenAI\Contracts\ClientContract` to the same client
instance. The factory:

- creates the HTTP client through `Hyperf\Guzzle\ClientFactory`;
- applies the configured base URI, API key, organization, and request timeout;
- sends the `OpenAI-Beta: assistants=v2` header.

The API key must be a string, and the organization must be either `null` or a string. Invalid types
throw `FriendsOfHyperf\OpenAi\Exception\ApiKeyIsMissing`. An empty API key is still a string, so it
will not trigger this exception; the API request will fail authentication instead.

## Usage

### Container

Resolve either the contract or the concrete client from the container. Resource methods, request
parameters, and response objects are provided by the installed `openai-php/client` version.

```php
use OpenAI\Contracts\ClientContract;

$response = di(ClientContract::class)->chat()->create([
    'model' => 'YOUR_MODEL',
    'messages' => [
        ['role' => 'user', 'content' => 'Explain dependency injection briefly.'],
    ],
]);

echo $response->choices[0]->message->content;
```

### Facade

`FriendsOfHyperf\OpenAi\Facade\OpenAI` forwards static calls to the container-bound
`ClientContract`.

```php
use FriendsOfHyperf\OpenAi\Facade\OpenAI;

$models = OpenAI::models()->list();
```

## Azure and Custom Clients

The component factory always configures bearer authentication and does not expose configuration for
custom headers or query parameters. Services such as Azure OpenAI that require an `api-key` header
and an `api-version` query parameter must be created manually with the upstream factory:

```php
use OpenAI;

$client = OpenAI::factory()
    ->withBaseUri('{your-resource-name}.openai.azure.com/openai/deployments/{deployment-id}')
    ->withHttpHeader('api-key', '{your-api-key}')
    ->withQueryParam('api-version', '{version}')
    ->make();
```

This manually created client is not automatically registered in the Hyperf container. Because the
deployment is part of the Azure base URI, calls for that deployment do not need a `model` parameter.

## Upstream API Guide

For supported resources and usage examples, refer to the
[openai-php/client documentation](https://github.com/openai-php/client) that matches your installed
version.
