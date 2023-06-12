# Hyperf OpenAI Client

[![Latest Test](https://github.com/friendsofhyperf/openai-client/workflows/tests/badge.svg)](https://github.com/friendsofhyperf/openai-client/actions)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/friendsofhyperf/openai-client.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/openai-client)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/openai-client.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/openai-client)
[![GitHub license](https://img.shields.io/github/license/friendsofhyperf/openai-client)](https://github.com/friendsofhyperf/openai-client)

------
**OpenAI PHP** for Laravel is a supercharged community PHP API client that allows you to interact with the [Open AI API](https://beta.openai.com/docs/api-reference/introduction).

> **Note:** This repository contains the integration code of the **OpenAI PHP** for Hyperf. If you want to use the **OpenAI PHP** client in a framework-agnostic way, take a look at the [openai-php/client](https://github.com/openai-php/client) repository.

## Get Started

> **Requires [PHP 8.1+](https://php.net/releases/)**

First, install OpenAI via the [Composer](https://getcomposer.org/) package manager:

```bash
composer require friendsofhyperf/openai-client
```

Next, publish the configuration file:

```bash
php bin/hyperf.php vendor:publish friendsofhyperf/openai-client
```

This will create a `config/autoload/openai.php` configuration file in your project, which you can modify to your needs
using environment variables:

```env
OPENAI_API_KEY=sk-...
```

Finally, you may use the `OpenAI\Client` instance from container to access the OpenAI API:

```php
use OpenAI\Client;

$result = di(OpenAI\Client::class)->completions()->create([
    'model' => 'text-davinci-003',
    'prompt' => 'PHP is',
]);

echo $result['choices'][0]['text']; // an open-source, widely-used, server-side scripting language.
```

## Azure

In order to use the Azure OpenAI Service, it is necessary to construct the client manually using the factory.

```php
$client = OpenAI::factory()
    ->withBaseUri('{your-resource-name}.openai.azure.com/openai/deployments/{deployment-id}')
    ->withHttpHeader('api-key', '{your-api-key}')
    ->withQueryParam('api-version', '{version}')
    ->make();
```

To use Azure, you must deploy a model, identified by the {deployment-id}, which is already incorporated into the API calls. As a result, you do not have to provide the model during the calls since it is included in the BaseUri.

Therefore, a basic sample completion call would be:

```php
$result = $client->completions()->create([
    'prompt' => 'PHP is'
]);
```

## Usage

For usage examples, take a look at the [openai-php/client](https://github.com/openai-php/client) repository.

---

OpenAI PHP for Hyperf is an open-sourced software licensed under the **[MIT license](https://opensource.org/licenses/MIT)**.
