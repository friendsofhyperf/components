# OpenAI Client

------

**OpenAI PHP** for Laravel 是一個功能強大的社群 PHP API 客戶端，允許您與 [Open AI API](https://beta.openai.com/docs/api-reference/introduction) 進行互動。

> **注意：** 此倉庫包含 **OpenAI PHP** 的 Hyperf 整合程式碼。如果您想在與框架無關的方式中使用 **OpenAI PHP** 客戶端，請檢視 [openai-php/client](https://github.com/openai-php/client) 倉庫。

## 快速開始

> **Requires [PHP 8.1+](https://php.net/releases/)**

首先，透過 [Composer](https://getcomposer.org/) 包管理器安裝 OpenAI：

```shell
composer require friendsofhyperf/openai-client
```

接下來，釋出配置檔案：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/openai-client
```

這將在您的專案中建立一個 `config/autoload/openai.php` 配置檔案，您可以使用環境變數根據需要進行修改：

```env
OPENAI_API_KEY=sk-...
```

最後，您可以使用容器中的 `OpenAI\Client` 例項來訪問 OpenAI API：

```php
use OpenAI\Client;

$result = di(OpenAI\Client::class)->completions()->create([
    'model' => 'text-davinci-003',
    'prompt' => 'PHP is',
]);

echo $result['choices'][0]['text']; // an open-source, widely-used, server-side scripting language.
```

## Azure

要使用 Azure OpenAI 服務，必須使用工廠手動構建客戶端。

```php
$client = OpenAI::factory()
    ->withBaseUri('{your-resource-name}.openai.azure.com/openai/deployments/{deployment-id}')
    ->withHttpHeader('api-key', '{your-api-key}')
    ->withQueryParam('api-version', '{version}')
    ->make();
```

要使用 Azure，您必須部署一個模型，該模型由 {deployment-id} 標識，已整合到 API 呼叫中。因此，您不必在呼叫期間提供模型，因為它已包含在 BaseUri 中。

因此，一個基本的示例完成呼叫將是：

```php
$result = $client->completions()->create([
    'prompt' => 'PHP is'
]);
```

## 官方指南

有關使用示例，請檢視 [openai-php/client](https://github.com/openai-php/client) 倉庫。
