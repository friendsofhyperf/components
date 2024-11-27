# Hyperf OpenAI Client

------

**OpenAI PHP** for Laravel 是一个功能强大的社区 PHP API 客户端，允许您与 [Open AI API](https://beta.openai.com/docs/api-reference/introduction) 进行交互。

> **注意：** 此仓库包含 **OpenAI PHP** 的 Hyperf 集成代码。如果您想在与框架无关的方式中使用 **OpenAI PHP** 客户端，请查看 [openai-php/client](https://github.com/openai-php/client) 仓库。

## 快速开始

> **Requires [PHP 8.1+](https://php.net/releases/)**

首先，通过 [Composer](https://getcomposer.org/) 包管理器安装 OpenAI：

```shell
composer require friendsofhyperf/openai-client
```

接下来，发布配置文件：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/openai-client
```

这将在您的项目中创建一个 `config/autoload/openai.php` 配置文件，您可以使用环境变量根据需要进行修改：

```env
OPENAI_API_KEY=sk-...
```

最后，您可以使用容器中的 `OpenAI\Client` 实例来访问 OpenAI API：

```php
use OpenAI\Client;

$result = di(OpenAI\Client::class)->completions()->create([
    'model' => 'text-davinci-003',
    'prompt' => 'PHP is',
]);

echo $result['choices'][0]['text']; // an open-source, widely-used, server-side scripting language.
```

## Azure

要使用 Azure OpenAI 服务，必须使用工厂手动构建客户端。

```php
$client = OpenAI::factory()
    ->withBaseUri('{your-resource-name}.openai.azure.com/openai/deployments/{deployment-id}')
    ->withHttpHeader('api-key', '{your-api-key}')
    ->withQueryParam('api-version', '{version}')
    ->make();
```

要使用 Azure，您必须部署一个模型，该模型由 {deployment-id} 标识，已集成到 API 调用中。因此，您不必在调用期间提供模型，因为它已包含在 BaseUri 中。

因此，一个基本的示例完成调用将是：

```php
$result = $client->completions()->create([
    'prompt' => 'PHP is'
]);
```

## 官方指南

有关使用示例，请查看 [openai-php/client](https://github.com/openai-php/client) 仓库。
