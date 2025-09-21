# 代码改进建议 (Code Improvement Recommendations)

这个文档提供了针对发现的代码坏味道的具体改进建议和实现方案。

## 1. 大类重构建议 (Large Class Refactoring)

### PendingRequest 类重构

**当前问题**: 1504行，124个方法，职责过多

**重构方案**:

```php
// 建议的新结构:

class PendingRequest
{
    private RequestBuilder $builder;
    private AuthenticationHandler $auth;
    private RetryHandler $retry;
    private ResponseProcessor $processor;
    
    public function __construct(
        RequestBuilder $builder,
        AuthenticationHandler $auth,
        RetryHandler $retry,
        ResponseProcessor $processor
    ) {
        $this->builder = $builder;
        $this->auth = $auth;
        $this->retry = $retry;
        $this->processor = $processor;
    }
    
    // 只保留核心的协调方法
    public function send(): Response
    {
        $request = $this->builder->build();
        $authenticatedRequest = $this->auth->authenticate($request);
        
        return $this->retry->execute(function() use ($authenticatedRequest) {
            $response = $this->sendRequest($authenticatedRequest);
            return $this->processor->process($response);
        });
    }
}

class RequestBuilder
{
    // 负责构建请求的所有方法
    public function withHeaders(array $headers): self;
    public function withQuery(array $query): self;
    public function withBody($body): self;
    // ... 其他构建方法
}

class AuthenticationHandler
{
    // 负责各种认证方式
    public function withBasicAuth(string $username, string $password): self;
    public function withBearerToken(string $token): self;
    public function withDigestAuth(string $username, string $password): self;
    // ... 其他认证方法
}
```

### Mailable 类重构

**当前问题**: 1490行，105个方法

**重构方案**:

```php
class Mailable
{
    private MailBuilder $builder;
    private MailValidator $validator;
    private MailRenderer $renderer;
    
    // 只保留核心方法
    public function build(): Mail;
    public function send(): void;
}

class MailBuilder
{
    // 负责邮件构建
    public function to($address): self;
    public function subject(string $subject): self;
    public function view(string $view): self;
    // ... 其他构建方法
}

class MailValidator
{
    // 负责邮件验证
    public function validateRecipients(): bool;
    public function validateContent(): bool;
}

class MailRenderer
{
    // 负责邮件渲染
    public function renderText(): string;
    public function renderHtml(): string;
    public function renderMarkdown(): string;
}
```

## 2. 异常处理改进 (Exception Handling Improvements)

### 当前问题示例:

```php
// 不好的做法
try {
    $this->doSomething();
} catch (Exception $e) {
    // 捕获了太宽泛的异常
}
```

### 改进方案:

```php
// 好的做法
try {
    $this->doSomething();
} catch (DatabaseConnectionException $e) {
    // 处理数据库连接问题
    $this->handleDatabaseError($e);
} catch (ValidationException $e) {
    // 处理验证错误
    $this->handleValidationError($e);
} catch (NetworkException $e) {
    // 处理网络问题
    $this->handleNetworkError($e);
} catch (Throwable $e) {
    // 最后的防护：记录未预期的错误
    $this->logger->error('Unexpected error', ['exception' => $e]);
    throw $e;
}
```

## 3. 配置重复消除 (Config Duplication Elimination)

### 当前问题:
三个语言的 sidebar 配置几乎完全重复

### 解决方案:

```typescript
// 创建 shared/sidebar-generator.ts
interface ComponentInfo {
    name: string;
    link: string;
    translations: {
        'zh-cn': string;
        'zh-hk': string;
        'zh-tw': string;
        'en': string;
    };
}

const components: ComponentInfo[] = [
    {
        name: 'amqp-job',
        link: '/components/amqp-job.md',
        translations: {
            'zh-cn': 'Amqp Job',
            'zh-hk': 'Amqp Job',
            'zh-tw': 'Amqp Job',
            'en': 'Amqp Job'
        }
    },
    // ... 其他组件
];

export function generateSidebar(locale: string): DefaultTheme.Sidebar {
    const prefix = locale === 'root' ? '' : `/${locale}`;
    
    return {
        [`${prefix}/faq/`]: generateFaqSection(locale),
        [`${prefix}/components/`]: generateComponentsSection(locale)
    };
}

function generateComponentsSection(locale: string) {
    return [{
        text: getTranslation('components', locale),
        items: components.map(component => ({
            text: component.translations[locale] || component.name,
            link: `${prefix}${component.link}`
        }))
    }];
}
```

### 使用方式:

```typescript
// zh-cn/sidebars.ts
import { generateSidebar } from '../shared/sidebar-generator';
export default generateSidebar('zh-cn');

// zh-hk/sidebars.ts
import { generateSidebar } from '../shared/sidebar-generator';
export default generateSidebar('zh-hk');
```

## 4. PHPUnit Patch 问题解决 (PHPUnit Patch Issue Resolution)

### 当前问题:
```php
// tests/phpunit-patch.php - 危险的运行时代码修改
if (strpos($content, $find = 'final ' . $replace) !== false) {
    $content = str_replace($find, $replace, $content);
    file_put_contents($file, $content);
}
```

### 解决方案:

#### 方案 1: 使用 Composer Patches
```json
// composer.json
{
    "require-dev": {
        "cweagans/composer-patches": "^1.7"
    },
    "extra": {
        "patches": {
            "phpunit/phpunit": {
                "Remove final from runBare method": "patches/phpunit-remove-final.patch"
            }
        }
    }
}
```

#### 方案 2: 创建自定义 TestCase 基类
```php
// tests/Support/TestCase.php
abstract class TestCase extends PHPUnitTestCase
{
    // 重写需要的方法而不是修改源代码
    public function runBare(): void
    {
        // 自定义的实现
        $this->setUpBeforeClass();
        $this->setUp();
        
        try {
            $this->runTest();
        } finally {
            $this->tearDown();
            $this->tearDownAfterClass();
        }
    }
}
```

#### 方案 3: 使用继承而不是修改
```php
// 如果确实需要修改 PHPUnit 行为，创建包装类
class CustomTestCase extends PHPUnitTestCase
{
    final public function runBare(): void
    {
        // 实现自定义逻辑
        parent::runBare();
    }
}
```

## 5. 立即行动计划 (Immediate Action Plan)

### 第一阶段 (紧急 - 1周内):
1. **移除危险的 PHPUnit patch**: 实施上述方案之一
2. **添加 .gitignore 规则**: 确保不会意外提交临时文件

### 第二阶段 (高优先级 - 1个月内):
1. **重构 PendingRequest 类**: 按照上述方案分解
2. **重构 Mailable 类**: 按照上述方案分解
3. **统一配置生成**: 实施 sidebar 配置重构

### 第三阶段 (中优先级 - 3个月内):
1. **改进异常处理**: 逐个文件检查和改进
2. **建立代码质量门禁**: 集成 PHPMD, PHPStan 等工具
3. **创建重构指南**: 为团队制定编码标准

### 第四阶段 (低优先级 - 6个月内):
1. **清理技术债务**: 解决所有 TODO 注释
2. **性能优化**: 基于重构后的代码进行性能调优
3. **文档更新**: 更新相关的技术文档

## 6. 代码质量监控 (Code Quality Monitoring)

### 建议的 CI/CD 集成:

```yaml
# .github/workflows/code-quality.yml
name: Code Quality

on: [push, pull_request]

jobs:
  quality:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.1
          
      - name: Install dependencies
        run: composer install
        
      - name: Run PHPStan
        run: vendor/bin/phpstan analyse --memory-limit=1G
        
      - name: Run PHPMD
        run: vendor/bin/phpmd src text cleancode,codesize,controversial,design,naming,unusedcode
        
      - name: Check code style
        run: vendor/bin/php-cs-fixer fix --dry-run --diff
```

这个渐进式的改进计划将帮助逐步提高代码质量，同时不会对现有功能造成破坏性影响。