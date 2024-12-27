# OpenAI Client

------

**OpenAI PHP** for Laravel is a robust community PHP API client that allows you to interact with the [Open AI API](https://beta.openai.com/docs/api-reference/introduction).

> **Note:** This repository contains the Hyperf integration code for **OpenAI PHP**. If you want to use the **OpenAI PHP** client in a framework-agnostic way, please refer to the [openai-php/client](https://github.com/openai-php/client) repository.

## Quick Start

> **Requires [PHP 8.1+](https://php.net/releases/)**

First, install OpenAI via the [Composer](https://getcomposer.org/) package manager:

```shell
composer require friendsofhyperf/openai-client
```

Next, publish the configuration file:

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/openai-client
```

This will create a `config/autoload/openai.php` configuration file in your project, which you can modify using environment variables as needed:

```env
OPENAI_API_KEY=sk-...
```

Finally, you can access the OpenAI API using the `OpenAI\Client` instance from the container:

```php
use OpenAI\Client;

$result = di(OpenAI\Client::class)->completions()->create([
    'model' => 'text-davinci-003',
    'prompt' => 'PHP is',
]);

echo $result['choices'][0]['text']; // an open-source, widely-used, server-side scripting language.
```

## Azure

To use the Azure OpenAI service, you need to manually build the client using a factory.

```php
$client = OpenAI::factory()
    ->withBaseUri('{your-resource-name}.openai.azure.com/openai/deployments/{deployment-id}')
    ->withHttpHeader('api-key', '{your-api-key}')
    ->withQueryParam('api-version', '{version}')
    ->make();
```

When using Azure, you must deploy a model identified by `{deployment-id}`, which is integrated into the API calls. Therefore, you don't need to specify a model during the call because it is already included in the `BaseUri`.

For example, a basic completion call would look like this:

```php
$result = $client->completions()->create([
    'prompt' => 'PHP is'
]);
```

## Official Guide

For usage examples, please refer to the [openai-php/client](https://github.com/openai-php/client) repository.