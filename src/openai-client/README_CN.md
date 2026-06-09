# OpenAI Client

[English](README.md)

此组件将 [openai-php/client](https://github.com/openai-php/client) 集成到 Hyperf，
向依赖注入容器注册上游客户端，并提供静态门面。

## 依赖要求

此包面向 Hyperf 3.2，依赖 `hyperf/config`、`hyperf/di`、`hyperf/guzzle` 和 0.10.0 或更高版本的
`openai-php/client`，未声明可选依赖。

## 安装

```shell
composer require friendsofhyperf/openai-client
```

发布配置文件：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/openai-client
```

此命令会创建 `config/autoload/openai.php`。

## 配置

发布的配置文件会读取以下环境变量：

```env
OPENAI_BASE_URI=api.openai.com/v1
OPENAI_API_KEY=sk-...
OPENAI_ORGANIZATION=
OPENAI_REQUEST_TIMEOUT=30
```

| 配置项 | 默认值 | 说明 |
| --- | --- | --- |
| `openai.base_uri` | `api.openai.com/v1` | 传给上游客户端工厂的基础 URI。 |
| `openai.api_key` | 空字符串 | 用于 Bearer 身份验证的 API 密钥。 |
| `openai.organization` | `null` | 可选的 OpenAI 组织标识。 |
| `openai.request_timeout` | `30` | 传给 Hyperf Guzzle 客户端的超时时间，单位为秒。 |

## 容器绑定与运行行为

组件会将 `OpenAI\Client` 和 `OpenAI\Contracts\ClientContract` 绑定到同一个
客户端实例。工厂会：

- 通过 `Hyperf\Guzzle\ClientFactory` 创建 HTTP 客户端；
- 应用配置的基础 URI、API 密钥、组织和请求超时时间；
- 发送 `OpenAI-Beta: assistants=v2` 请求头。

API 密钥必须是字符串，组织必须是 `null` 或字符串。类型不正确时会抛出
`FriendsOfHyperf\OpenAi\Exception\ApiKeyIsMissing`。空 API 密钥仍是字符串，因此不会
触发此异常；API 请求会改为因身份验证失败而报错。

## 使用

### 容器

可以从容器解析契约或具体客户端。资源方法、请求参数和响应对象由已安装的
`openai-php/client` 版本提供。

```php
use OpenAI\Contracts\ClientContract;

$response = di(ClientContract::class)->chat()->create([
    'model' => 'YOUR_MODEL',
    'messages' => [
        ['role' => 'user', 'content' => 'Briefly explain dependency injection.'],
    ],
]);

echo $response->choices[0]->message->content;
```

### 门面

`FriendsOfHyperf\OpenAi\Facade\OpenAI` 会将静态调用转发给容器绑定的 `ClientContract`。

```php
use FriendsOfHyperf\OpenAi\Facade\OpenAI;

$models = OpenAI::models()->list();
```

## Azure 与自定义客户端

组件工厂始终配置 Bearer 身份验证，且未提供自定义请求头或查询参数的配置。
Azure OpenAI 等需要 `api-key` 请求头和 `api-version` 查询参数的服务，必须使用
上游工厂手动创建客户端：

```php
use OpenAI;

$client = OpenAI::factory()
    ->withBaseUri('{your-resource-name}.openai.azure.com/openai/deployments/{deployment-id}')
    ->withHttpHeader('api-key', '{your-api-key}')
    ->withQueryParam('api-version', '{version}')
    ->make();
```

手动创建的客户端不会自动注册到 Hyperf 容器。由于 Azure 基础 URI 已包含部署，
因此针对该部署的调用无需传入 `model` 参数。

## 上游 API 指南

有关支持的资源和使用示例，请参阅与已安装版本匹配的
[openai-php/client 文档](https://github.com/openai-php/client)。
