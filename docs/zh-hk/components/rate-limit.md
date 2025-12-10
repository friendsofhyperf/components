# Rate Limit

Hyperf 的限流組件，支援多種演算法（固定視窗、滑動視窗、令牌桶、漏桶）。

## 安裝

```bash
composer require friendsofhyperf/rate-limit
```

## 環境需求

- Hyperf ~3.1.0
- Redis

## 特性

- **多種限流演算法**
  - 固定視窗
  - 滑動視窗
  - 令牌桶
  - 漏桶
- **靈活的使用方式**
  - 基於註解的限流（透過切面實現）
  - 自訂中間件支援
- **多註解智慧排序**
  - 自動對多個 RateLimit 註解進行優先級排序
  - 根據嚴格程度智慧排序（maxAttempts/decay 比率）
  - 更嚴格的限制優先檢查，提升效能
- **靈活的鍵生成**
  - 預設基於方法/類別的鍵
  - 支援自訂鍵和佔位符
  - 支援陣列鍵
  - 支援可呼叫鍵
- **自訂回應**
  - 自訂回應訊息
  - 自訂 HTTP 回應碼
- **多 Redis 連接池支援**

## 使用方式

### 方式一：使用註解

最簡單的方式是使用 `#[RateLimit]` 屬性：

```php
<?php

namespace App\Controller;

use FriendsOfHyperf\RateLimit\Annotation\RateLimit;
use FriendsOfHyperf\RateLimit\Algorithm;

class UserController
{
    /**
     * 基礎限流：60 秒內最多 60 次請求
     */
    #[RateLimit(maxAttempts: 60, decay: 60)]
    public function index()
    {
        // 你的代碼
    }

    /**
     * 使用滑動視窗演算法
     */
    #[RateLimit(
        maxAttempts: 100,
        decay: 60,
        algorithm: Algorithm::SLIDING_WINDOW
    )]
    public function api()
    {
        // 你的代碼
    }

    /**
     * 自訂鍵，支援用戶 ID 佔位符
     */
    #[RateLimit(
        key: 'user:{userId}:action',
        maxAttempts: 10,
        decay: 3600
    )]
    public function create($userId)
    {
        // 你的代碼
    }

    /**
     * 使用陣列鍵
     */
    #[RateLimit(
        key: ['user', '{userId}', 'create'],
        maxAttempts: 5,
        decay: 60
    )]
    public function update($userId)
    {
        // 你的代碼
    }

    /**
     * 自訂回應訊息和狀態碼
     */
    #[RateLimit(
        maxAttempts: 5,
        decay: 60,
        response: 'Too many requests, please try again later.',
        responseCode: 429
    )]
    public function login()
    {
        // 你的代碼
    }

    /**
     * 使用指定的 Redis 連接池
     */
    #[RateLimit(
        maxAttempts: 60,
        decay: 60,
        pool: 'rate_limit'
    )]
    public function heavyOperation()
    {
        // 你的代碼
    }
}
```

### 註解參數

| 參數 | 類型 | 預設值 | 說明 |
|-----------|------|---------|-------------|
| `key` | `string\|array` | `''` | 限流鍵。支援：'user:{user_id}', ['user', '{user_id}'], 或可呼叫函數 |
| `maxAttempts` | `int` | `60` | 允許的最大請求次數 |
| `decay` | `int` | `60` | 時間視窗（秒） |
| `algorithm` | `Algorithm` | `Algorithm::FIXED_WINDOW` | 演算法：fixed_window, sliding_window, token_bucket, leaky_bucket |
| `pool` | `?string` | `null` | 使用的 Redis 連接池 |
| `response` | `string` | `'Too Many Attempts.'` | 超出限流時的自訂回應 |
| `responseCode` | `int` | `429` | 超出限流時的 HTTP 狀態碼 |

### 使用 AutoSort 實現多限流規則智慧排序

當同一個方法需要多個限流規則時（例如每分鐘和每小時的限制），可以使用 `AutoSort` 註解自動按嚴格程度排序：

```php
<?php

use FriendsOfHyperf\RateLimit\Annotation\RateLimit;
use FriendsOfHyperf\RateLimit\Annotation\AutoSort;

class ApiController
{
    /**
     * 多個限流規則智慧排序
     * 更嚴格的限制（maxAttempts/decay 比率更小）優先檢查
     */
    #[AutoSort]
    #[RateLimit(maxAttempts: 10, decay: 60)]      // 每分鐘 10 次 - 優先檢查
    #[RateLimit(maxAttempts: 100, decay: 3600)]    // 每小時 100 次 - 其次檢查
    public function expensiveOperation()
    {
        // 你的代碼
    }

    /**
     * 不使用 AutoSort 時，按聲明順序檢查
     */
    #[RateLimit(maxAttempts: 100, decay: 3600)]    // 優先檢查
    #[RateLimit(maxAttempts: 10, decay: 60)]       // 其次檢查
    public function anotherOperation()
    {
        // 你的代碼
    }
}
```

**AutoSort 的優勢：**

- **效能**：嚴格的限制優先檢查，避免不必要的寬鬆限制檢查
- **智慧**：自動根據限制嚴格程度（maxAttempts/decay 比率）計算優先級
- **可選**：僅在顯式使用 `AutoSort` 的方法上生效
- **向後兼容**：現有代碼無需修改即可繼續工作

### 鍵佔位符

`key` 參數支援動態佔位符，會被方法參數替換：

```php
// 命名佔位符
#[RateLimit(key: 'user:{userId}:{action}')]
public function action($userId, $action)

// 陣列格式（自動用 ':' 連接）
#[RateLimit(key: ['user', '{userId}', '{action}'])]
public function action($userId, $action)
```

### 方式二：使用中間件

對於 HTTP 請求，可以創建繼承 `RateLimitMiddleware` 的自訂中間件：

```php
<?php

namespace App\Middleware;

use FriendsOfHyperf\RateLimit\Middleware\RateLimitMiddleware;
use FriendsOfHyperf\RateLimit\Algorithm;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApiRateLimitMiddleware extends RateLimitMiddleware
{
    // 重寫預設屬性
    protected int $maxAttempts = 100;
    protected int $decay = 60;
    protected Algorithm $algorithm = Algorithm::SLIDING_WINDOW;
    protected string $responseMessage = 'API rate limit exceeded';
    protected int $responseCode = 429;

    // 或自訂鍵解析
    protected function resolveKey(ServerRequestInterface $request): string
    {
        return 'api:' . $this->getClientIp();
    }
}
```

然後在配置中註冊中間件：

```php
// config/autoload/middlewares.php
return [
    'http' => [
        App\Middleware\ApiRateLimitMiddleware::class,
    ],
];
```

## 限流演算法

### 固定視窗（預設）

最簡單的演算法，在固定時間視窗內計數請求。

```php
#[RateLimit(algorithm: Algorithm::FIXED_WINDOW)]
```

**優點**：簡單，內存高效  
**缺點**：可能在視窗邊界處允許突發請求

### 滑動視窗

比固定視窗更準確，均勻分佈請求。

```php
#[RateLimit(algorithm: Algorithm::SLIDING_WINDOW)]
```

**優點**：平滑突發流量，更準確  
**缺點**：稍微複雜一些

### 令牌桶

允許突發流量，同時保持平均速率。

```php
#[RateLimit(algorithm: Algorithm::TOKEN_BUCKET)]
```

**優點**：允許突發流量，靈活  
**缺點**：需要更多配置

### 漏桶

以恆定速率處理請求，排隊突發流量。

```php
#[RateLimit(algorithm: Algorithm::LEAKY_BUCKET)]
```

**優點**：平滑輸出速率，防止突發  
**缺點**：可能延遲請求

## 自訂限流器

你可以透過實現 `RateLimiterInterface` 來實現自己的限流器：

```php
<?php

namespace App\RateLimit;

use FriendsOfHyperf\RateLimit\Contract\RateLimiterInterface;

class CustomRateLimiter implements RateLimiterInterface
{
    public function tooManyAttempts(string $key, int $maxAttempts, int $decay): bool
    {
        // 你的實現
    }

    public function availableIn(string $key): int
    {
        // 你的實現
    }

    public function remaining(string $key, int $maxAttempts): int
    {
        // 你的實現
    }
}
```

## 異常處理

當超出限流時，會拋出 `RateLimitException`：

```php
<?php

try {
    $userController->index();
} catch (FriendsOfHyperf\RateLimit\Exception\RateLimitException $e) {
    // 超出限流
    $message = $e->getMessage();  // "Too Many Attempts. Please try again in X seconds."
    $code = $e->getCode();        // 429
}
```

## 配置

組件使用 Hyperf 的 Redis 配置。你可以在註解或中間件中指定使用的 Redis 連接池：

```php
// 使用特定的 Redis 連接池
#[RateLimit(pool: 'rate_limit')]
```

確保在 `config/autoload/redis.php` 中配置 Redis 連接池：

```php
return [
    'default' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'port' => env('REDIS_PORT', 6379),
        'auth' => env('REDIS_AUTH', null),
        'db' => 0,
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 30,
        ],
    ],
    'rate_limit' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'port' => env('REDIS_PORT', 6379),
        'auth' => env('REDIS_AUTH', null),
        'db' => 1,
        'pool' => [
            'min_connections' => 5,
            'max_connections' => 50,
        ],
    ],
];
```

## 示例

### 示例 1：登錄限流

限制登錄嘗試以防止暴力破解：

```php
#[RateLimit(
    key: 'login:{email}',
    maxAttempts: 5,
    decay: 300, // 5 分鐘
    response: 'Too many login attempts. Please try again after 5 minutes.',
    responseCode: 429
)]
public function login(string $email, string $password)
{
    // 登錄邏輯
}
```

### 示例 2：API 端點限流

為不同的 API 端點設置不同的限流：

```php
class ApiController
{
    // 公共 API：每分鐘 100 次請求
    #[RateLimit(maxAttempts: 100, decay: 60)]
    public function public()
    {
        // 公共端點
    }

    // 高級 API：每分鐘 1000 次請求
    #[RateLimit(maxAttempts: 1000, decay: 60)]
    public function premium()
    {
        // 高級端點
    }
}
```

### 示例 3：基於用戶的限流

按用戶限流：

```php
#[RateLimit(
    key: ['user', '{userId}', 'action'],
    maxAttempts: 10,
    decay: 3600 // 1 小時
)]
public function performAction(int $userId)
{
    // 操作邏輯
}
```

### 示例 4：基於 IP 的限流

使用中間件按 IP 地址限流：

```php
class IpRateLimitMiddleware extends RateLimitMiddleware
{
    protected function resolveKey(ServerRequestInterface $request): string
    {
        return 'ip:' . $this->getClientIp();
    }
}
```

### 示例 5：使用 AutoSort 的多級限流

使用 AutoSort 高效處理昂貴操作的多級限流：

```php
class ReportController
{
    /**
     * 昂貴的報告生成，多級保護
     * AutoSort 確保優先檢查嚴格的限制，提升效能
     */
    #[AutoSort]
    #[RateLimit(maxAttempts: 5, decay: 60, response: 'Too many requests. Max 5 per minute')]       // 緊急制動
    #[RateLimit(maxAttempts: 30, decay: 3600, response: 'Hourly limit exceeded. Max 30 per hour')] // 持續負載
    #[RateLimit(maxAttempts: 100, decay: 86400, response: 'Daily limit exceeded. Max 100 per day')] // 每日上限
    public function generateReport($reportType)
    {
        // 昂貴的報告生成邏輯
    }
}
```
