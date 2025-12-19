# Changelog

## v3.1.78 - 2025-12-19

### What's Changed

* fix(sentry): remove duplicate code and improve singleton instance reset in SingletonAspect by @huangdijia in https://github.com/friendsofhyperf/components/pull/1048

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.77...v3.1.78

## v3.1.77 - 2025-12-18

### What's Changed

* fix(telescope): register RequestHandledListener and SetRequestLifecycleListener in ConfigProvider by @Copilot in https://github.com/friendsofhyperf/components/pull/1043
* optimize(telescope): refine GuzzleHttpClientAspect client request recording by @guandeng in https://github.com/friendsofhyperf/components/pull/1044
* AbstractLock use Macroable by @tw2066 in https://github.com/friendsofhyperf/components/pull/1045
* refactor(sentry): improve timer cleanup in metrics listeners by @huangdijia in https://github.com/friendsofhyperf/components/pull/1046
* Add Annotation for Sentry Safe Caller by @xuanyanwow in https://github.com/friendsofhyperf/components/pull/1047

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.76...v3.1.77

## v3.1.76 - 2025-12-12

### What's Changed

* Update .github/copilot-instructions.md to reflect latest project code by @Copilot in https://github.com/friendsofhyperf/components/pull/1039

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.2.0-alpha.6...v3.2.0-beta.1

### What's Changed

* refactor: centralize context management in Sentry integration by @huangdijia in https://github.com/friendsofhyperf/components/pull/1035
* feat: 添加多种Backoff重试策略实现 by @huangdijia in https://github.com/friendsofhyperf/components/pull/1036
* docs: update copilot-instructions.md with latest project information by @Copilot in https://github.com/friendsofhyperf/components/pull/1038
* docs: add rate-limit component documentation by @Copilot in https://github.com/friendsofhyperf/components/pull/1040
* fix: improve handling of binary responses in GuzzleHttpClientAspect by @huangdijia in https://github.com/friendsofhyperf/components/pull/1041

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.75...v3.1.76

## v3.1.75 - 2025-12-03

### What's Changed

* feat: add server address tracing for Redis connections by @huangdijia in https://github.com/friendsofhyperf/components/pull/1021
* feat: add server address tracing for database connections by @huangdijia in https://github.com/friendsofhyperf/components/pull/1024
* fix: improve database read/write connection tracing by @huangdijia in https://github.com/friendsofhyperf/components/pull/1025
* feat: add server address tracing for RedisCluster connections by @huangdijia in https://github.com/friendsofhyperf/components/pull/1026
* refactor: optimize database server address tracing with WeakMap by @huangdijia in https://github.com/friendsofhyperf/components/pull/1027
* feat: enhance DB connection tracing with real-time server address detection by @huangdijia in https://github.com/friendsofhyperf/components/pull/1028
* feat: add feature flag support for tracing aspects by @huangdijia in https://github.com/friendsofhyperf/components/pull/1029
* Add refresh, isExpired, getRemainingLifetime methods to lock drivers by @Copilot in https://github.com/friendsofhyperf/components/pull/1031
* feat: Support Trace Metrics by @huangdijia in https://github.com/friendsofhyperf/components/pull/1032
* fix: replace filter with contains method in ClassAliasAutoloader by @huangdijia in https://github.com/friendsofhyperf/components/pull/1033
* feat: enhance Sentry metrics with configurable default metrics collection by @huangdijia in https://github.com/friendsofhyperf/components/pull/1034

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.74...v3.1.75

## v3.1.74 - 2025-11-21

### What's Changed

* Add rate-limit component with 4 algorithms, annotations, AOP, and middleware by @Copilot in https://github.com/friendsofhyperf/components/pull/1014
* feat: add IS_REPEATABLE support to RateLimit annotation by @huangdijia in https://github.com/friendsofhyperf/components/pull/1015
* feat: add SmartOrder annotation for intelligent RateLimit prioritization by @huangdijia in https://github.com/friendsofhyperf/components/pull/1016
* feat: add socket tracing support for RPC calls by @huangdijia in https://github.com/friendsofhyperf/components/pull/1017
* refactor: improve server mutex implementation and configuration by @huangdijia in https://github.com/friendsofhyperf/components/pull/1019
* feat: add server address and HTTP method tracing for Elasticsearch operations by @huangdijia in https://github.com/friendsofhyperf/components/pull/1022

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.73...v3.1.74

## v3.1.73 - 2025-11-17

### What's Changed

* feat(sentry): improve coroutine backtrace filtering by @huangdijia in https://github.com/friendsofhyperf/components/pull/1000
* refactor: remove unused PendingClosureDispatch class by @huangdijia in https://github.com/friendsofhyperf/components/pull/1001
* fix: correct pool property initialization in dispatch classes by @huangdijia in https://github.com/friendsofhyperf/components/pull/1002
* refactor: consolidate CallQueuedClosure to support package by @huangdijia in https://github.com/friendsofhyperf/components/pull/1003
* Add type tests for AMQP and Kafka dispatch return types by @Copilot in https://github.com/friendsofhyperf/components/pull/1005
* Remove pest-plugin-hyperf component by @huangdijia in https://github.com/friendsofhyperf/components/pull/1008
* Remove http-logger component by @huangdijia in https://github.com/friendsofhyperf/components/pull/1009
* fix: standardize pool parameter naming in AsyncQueue and Kafka facades by @huangdijia in https://github.com/friendsofhyperf/components/pull/1010

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.72...v3.1.73

## v3.1.72 - 2025-11-14

### What's Changed

* refactor(sentry): remove deprecated features and unused configuration by @huangdijia in https://github.com/friendsofhyperf/components/pull/990
* refactor(sentry): simplify configuration structure and improve API naming by @huangdijia in https://github.com/friendsofhyperf/components/pull/991
* feat(sentry): add backwards compatibility for legacy configuration keys by @huangdijia in https://github.com/friendsofhyperf/components/pull/992
* Update Swoole test matrix to v6.1.2 by @Copilot in https://github.com/friendsofhyperf/components/pull/994
* Enable composer repo:pending by configuring Symfony Finder autoloader by @Copilot in https://github.com/friendsofhyperf/components/pull/996
* feat: introduce fluent API for dispatch() helper function by @huangdijia in https://github.com/friendsofhyperf/components/pull/999

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.71...v3.1.72

## v3.1.71 - 2025-11-08

### What's Changed

* Add missing documentation for co-phpunit and command-benchmark components by @Copilot in https://github.com/friendsofhyperf/components/pull/978
* Enhance README.md with comprehensive component documentation and add Chinese version by @Copilot in https://github.com/friendsofhyperf/components/pull/980
* refactor(co-phpunit): improve ClassLoader detection using registered autoloaders by @huangdijia in https://github.com/friendsofhyperf/components/pull/985
* Refactor PHPUnit autoloader discovery mechanism to optimize loader selection process by @huangdijia in https://github.com/friendsofhyperf/components/pull/986
* feat(sentry): upgrade to v4.18.0 and refactor LogsHandler by @huangdijia in https://github.com/friendsofhyperf/components/pull/988
* feat(co-phpunit): add NonCoroutine attribute to skip coroutine execution by @huangdijia in https://github.com/friendsofhyperf/components/pull/989

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.70...v3.1.71

## v3.1.70 - 2025-10-30

### What's Changed

* refactor(sentry): centralize trace header constants for improved maintainability by @huangdijia in https://github.com/friendsofhyperf/components/pull/946
* ✨ Enhance Copilot instructions with coding agent best practices by @Copilot in https://github.com/friendsofhyperf/components/pull/952
* docs(sentry): update documentation to use LogsHandler instead of deprecated SentryHandler by @Copilot in https://github.com/friendsofhyperf/components/pull/950
* feat(sentry): align GrpcAspect with OpenTelemetry semantic conventions by @huangdijia in https://github.com/friendsofhyperf/components/pull/955
* feat(sentry): support nested spans for console commands by @huangdijia in https://github.com/friendsofhyperf/components/pull/956
* feat(validated-dto): support Cast attribute for DTOCast type by @huangdijia in https://github.com/friendsofhyperf/components/pull/958
* fix(confd): correct NacosClient instantiation parameter format by @huangdijia in https://github.com/friendsofhyperf/components/pull/959
* feat(sentry): add database connection pool metrics to breadcrumbs by @huangdijia in https://github.com/friendsofhyperf/components/pull/960
* refactor(sentry): extract setup logic to dedicated listener by @huangdijia in https://github.com/friendsofhyperf/components/pull/961
* fix(sentry): correct origin semantic convention for Monolog handler by @huangdijia in https://github.com/friendsofhyperf/components/pull/965
* feat(sentry): add producer identification to AMQP tracing by @huangdijia in https://github.com/friendsofhyperf/components/pull/966
* feat(sentry): improve coroutine context handling and exception capture by @huangdijia in https://github.com/friendsofhyperf/components/pull/967
* feat: extract co-phpunit package for coroutine testing support by @huangdijia in https://github.com/friendsofhyperf/components/pull/968
* feat(sentry): add @Ignore annotation for exception filtering by @huangdijia in https://github.com/friendsofhyperf/components/pull/969
* feat: Add PHPStan type testing by @huangdijia in https://github.com/friendsofhyperf/components/pull/970

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.69...v3.1.70

## v3.1.69 - 2025-10-11

### What's Changed

* feat(sentry): add custom Monolog LogsHandler implementation by @huangdijia in https://github.com/friendsofhyperf/components/pull/909
* feat(sentry): add integer log level support to LogsHandler by @huangdijia in https://github.com/friendsofhyperf/components/pull/910
* chore(deps): update huangdijia/php-coding-standard to ^2.4 by @huangdijia in https://github.com/friendsofhyperf/components/pull/911
* refactor(sentry): consolidate event listeners into unified EventListener by @huangdijia in https://github.com/friendsofhyperf/components/pull/913
* refactor(sentry): improve span lifecycle management and code clarity by @huangdijia in https://github.com/friendsofhyperf/components/pull/914
* feat(sentry): add filesystem operations tracking with FilesystemAspect by @huangdijia in https://github.com/friendsofhyperf/components/pull/915
* feat(sentry): improve cache breadcrumb messages with operation-specific descriptions by @huangdijia in https://github.com/friendsofhyperf/components/pull/916
* refactor(sentry): improve span null safety and consistency in tracing aspects by @huangdijia in https://github.com/friendsofhyperf/components/pull/918
* Feat/sentry coroutine context propagation improvements by @huangdijia in https://github.com/friendsofhyperf/components/pull/919
* refactor(sentry): replace SpanStarter trait with global functions by @huangdijia in https://github.com/friendsofhyperf/components/pull/920
* refactor(sentry): centralize coroutine ID handling in tracing system by @huangdijia in https://github.com/friendsofhyperf/components/pull/921
* feat(sentry): improve transaction handling in coroutine contexts by @huangdijia in https://github.com/friendsofhyperf/components/pull/922
* refactor(sentry): replace Switcher class with Feature class by @huangdijia in https://github.com/friendsofhyperf/components/pull/923
* Generate copilot-instructions.md for better GitHub Copilot integration by @Copilot in https://github.com/friendsofhyperf/components/pull/927
* Add comprehensive unit tests for Cache Repository class by @Copilot in https://github.com/friendsofhyperf/components/pull/929
* Add comprehensive unit test suite for lock component by @Copilot in https://github.com/friendsofhyperf/components/pull/931
* Refactor tests/Cache/RepositoryTest.php to use Pest testing framework by @Copilot in https://github.com/friendsofhyperf/components/pull/933
* refactor(sentry): simplify hub initialization in Tracer by @huangdijia in https://github.com/friendsofhyperf/components/pull/934
* feat(sentry): add view rendering tracing support by @huangdijia in https://github.com/friendsofhyperf/components/pull/935
* refactor(sentry): optimize hub management and listener priorities by @huangdijia in https://github.com/friendsofhyperf/components/pull/936
* refactor(sentry): optimize event data setting in EventHandleListener by @huangdijia in https://github.com/friendsofhyperf/components/pull/937
* refactor(sentry): replace try-finally with defer pattern in CoroutineAspect by @huangdijia in https://github.com/friendsofhyperf/components/pull/938
* refactor(sentry): remove exception message and stack trace data from tracing spans by @huangdijia in https://github.com/friendsofhyperf/components/pull/939
* refactor(sentry): optimize event flushing strategy by moving to tracing components by @huangdijia in https://github.com/friendsofhyperf/components/pull/940
* refactor(sentry): simplify tracing data collection and remove sensitive exception details by @huangdijia in https://github.com/friendsofhyperf/components/pull/941
* refactor(sentry): simplify transaction null checks using nullsafe operator by @huangdijia in https://github.com/friendsofhyperf/components/pull/942
* Add extensible DTO export functionality with TypeScript and multi-format support by @Copilot in https://github.com/friendsofhyperf/components/pull/925
* refactor(sentry): standardize message ID generation using SentryUid in tracing aspects by @huangdijia in https://github.com/friendsofhyperf/components/pull/943
* fix(mail): add missing `scheme` configuration to SMTP mailer by @andreluizmicro in https://github.com/friendsofhyperf/components/pull/944

### New Contributors

* @Copilot made their first contribution in https://github.com/friendsofhyperf/components/pull/927
* @andreluizmicro made their first contribution in https://github.com/friendsofhyperf/components/pull/944

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.68...v3.1.69

## v3.1.68 - 2025-09-18

### What's Changed

* fix: improve cache key handling in CacheAspect by @huangdijia in https://github.com/friendsofhyperf/components/pull/893
* fix: change default values to true in Sentry Switcher by @huangdijia in https://github.com/friendsofhyperf/components/pull/894
* Improve async queue tracing by @huangdijia in https://github.com/friendsofhyperf/components/pull/895
* feat(tracing): enhance AMQP and Kafka messaging tracing with detailed metadata by @huangdijia in https://github.com/friendsofhyperf/components/pull/896
* refactor(sentry): replace CarrierPacker with new Carrier utility class by @huangdijia in https://github.com/friendsofhyperf/components/pull/897
* feat(tracing): comprehensive improvements to tracing aspects and listeners by @huangdijia in https://github.com/friendsofhyperf/components/pull/898
* fix(tracing): standardize HTTP response field names in GuzzleHttpClientAspect by @huangdijia in https://github.com/friendsofhyperf/components/pull/899
* fix(tracing): optimize HTTP response body content reading in GuzzleHttpClientAspect by @huangdijia in https://github.com/friendsofhyperf/components/pull/900
* refactor(tracing): streamline transaction context configuration in SpanStarter by @huangdijia in https://github.com/friendsofhyperf/components/pull/902
* fix(tracing): use routing key for AMQP destination name instead of exchange by @huangdijia in https://github.com/friendsofhyperf/components/pull/903
* fix(sentry): optimize HttpClient memory usage and error handling by @huangdijia in https://github.com/friendsofhyperf/components/pull/905
* refactor(trigger): improve TriggerSubscriber event handling architecture by @huangdijia in https://github.com/friendsofhyperf/components/pull/906
* feat(sentry): add AsyncHttpTransport for non-blocking error reporting by @huangdijia in https://github.com/friendsofhyperf/components/pull/907
* feat(sentry): improve event flushing and expand listener coverage by @huangdijia in https://github.com/friendsofhyperf/components/pull/908

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.67...v3.1.68

## v3.1.67 - 2025-09-01

### What's Changed

* Refactor blank() function to use match expression by @huangdijia in https://github.com/friendsofhyperf/components/pull/888
* Use PHP 8.4 array functions and add polyfill support by @huangdijia in https://github.com/friendsofhyperf/components/pull/889
* Optimize RedisCommand formatting with caching by @huangdijia in https://github.com/friendsofhyperf/components/pull/890
* fix(macros): throw original ValidationException with error bag in RequestMixin by @huangdijia in https://github.com/friendsofhyperf/components/pull/891
* chore: fix naming/spelling; rename DatabaseMessages→DatabaseMessage (BC alias) by @huangdijia in https://github.com/friendsofhyperf/components/pull/892

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.66...v3.1.67

## v3.1.66 - 2025-08-21

### What's Changed

* Update OAuth2 server implementation to use factory pattern by @zds-s in https://github.com/friendsofhyperf/components/pull/879
* Add Arr::every() and Arr::some() methods by @huangdijia in https://github.com/friendsofhyperf/components/pull/882
* Upgrade sentry/sentry from 4.4.0 to 4.15.0 by @huangdijia in https://github.com/friendsofhyperf/components/pull/886
* Fix StringableMixin formatting and improve function definitions by @huangdijia in https://github.com/friendsofhyperf/components/pull/887

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.65...v3.1.66

## v3.1.65 - 2025-08-01

### What's Changed

* Add doesntEndWith and doesntStartWith macros to Str and Stringable by @huangdijia in https://github.com/friendsofhyperf/components/pull/874
* fix the error "Class "FriendsOfHyperf\Support\RedisCommand" not found " by @rookiexxk in https://github.com/friendsofhyperf/components/pull/876
* Add initial implementation of OAuth2 server components by @zds-s in https://github.com/friendsofhyperf/components/pull/875
* Add comprehensive OAuth2 server documentation by @huangdijia in https://github.com/friendsofhyperf/components/pull/877
* Fix Sentry documentation URL anchors in configuration by @huangdijia in https://github.com/friendsofhyperf/components/pull/878

### New Contributors

* @rookiexxk made their first contribution in https://github.com/friendsofhyperf/components/pull/876

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.64...v3.1.65

## v3.1.64 - 2025-06-20

### What's Changed

* Add encrypt and decrypt Str helper methods by @huangdijia in https://github.com/friendsofhyperf/components/pull/871
* Fix issue of missing nested DTO properties when transforming DTO by @huangdijia in https://github.com/friendsofhyperf/components/pull/872
* Add serialization support to SimpleDTO by @huangdijia in https://github.com/friendsofhyperf/components/pull/873

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.63...v3.1.64

## v3.1.63 - 2025-06-06

### What's Changed

* Fix incorrect repository class in CacheManager by @huangdijia in https://github.com/friendsofhyperf/components/pull/865
* Fix event dispatch on cache forget failure by @huangdijia in https://github.com/friendsofhyperf/components/pull/866
* Fix telescope heading in docs by @huangdijia in https://github.com/friendsofhyperf/components/pull/867
* Add repository forget event tests by @huangdijia in https://github.com/friendsofhyperf/components/pull/868
* Fix docs tagline by @huangdijia in https://github.com/friendsofhyperf/components/pull/869

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.62...v3.1.63

## v3.1.62 - 2025-05-26

### What's Changed

* feat(Stringable): 添加 hash 方法及相应测试 by @huangdijia in https://github.com/friendsofhyperf/components/pull/862
* Introducing `Arr::hasAll` by @huangdijia in https://github.com/friendsofhyperf/components/pull/863

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.61...v3.1.62

## v3.1.61 - 2025-05-14

### What's Changed

* Typed getters for Arr helper by @huangdijia in https://github.com/friendsofhyperf/components/pull/857
* feat(Number): 添加 fileSize 方法的二进制前缀选项及相应测试 by @huangdijia in https://github.com/friendsofhyperf/components/pull/859
* feat(tests): 添加 arrayable 和 from 方法的测试用例及相关辅助类 by @huangdijia in https://github.com/friendsofhyperf/components/pull/860
* feat(Support): Add number parsing methods to Number class by @huangdijia in https://github.com/friendsofhyperf/components/pull/861

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.60...v3.1.61

## v3.1.60 - 2025-04-25

### What's Changed

* fix(sentry): 参数类型处理异常 by @xuanyanwow in https://github.com/friendsofhyperf/components/pull/854
* fix(sentry): prevent crash on non-scalar redis parameters using RedisCommand by @alexsyvolap in https://github.com/friendsofhyperf/components/pull/856

### New Contributors

* @alexsyvolap made their first contribution in https://github.com/friendsofhyperf/components/pull/856

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.59...v3.1.60

## v3.1.59 - 2025-04-11

### What's Changed

* fix(Listener): 在BeforeHandle事件时提前设置telescope.recording by @guandeng in https://github.com/friendsofhyperf/components/pull/852
* refactor(Listener): 优化FetchRecordingOnBootListener的状态通知处理 by @guandeng in https://github.com/friendsofhyperf/components/pull/853

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.58...v3.1.59

## v3.1.58 - 2025-04-04

### What's Changed

* Bumps swoole version to v6.0.1 by @huangdijia in https://github.com/friendsofhyperf/components/pull/846
* Improved redis tracing by @huangdijia in https://github.com/friendsofhyperf/components/pull/847
* Added `CacheFlushed` Event by @huangdijia in https://github.com/friendsofhyperf/components/pull/850
* refactor(redis): Update the namespace of the RedisCommand class and remove deprecated implementations by @huangdijia in https://github.com/friendsofhyperf/components/pull/851

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.57...v3.1.58

## v3.1.57 - 2025-02-27

### What's Changed

* Added docs for validated-dto by @xuanyanwow in https://github.com/friendsofhyperf/components/pull/840
* fix: handle false return value for Channel::pop in TriggerSubscriber by @guandeng in https://github.com/friendsofhyperf/components/pull/841
* Removed custom transformModelToArray function by @huangdijia in https://github.com/friendsofhyperf/components/pull/842
* feat: add rescue function for exception handling with default values by @huangdijia in https://github.com/friendsofhyperf/components/pull/843
* Add `command-benchmark` component by @huangdijia in https://github.com/friendsofhyperf/components/pull/844

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.56...v3.1.57

## v3.1.56 - 2025-02-14

### What's Changed

* fix: Optimize content encoding logic in DatabaseEntries Repository using the with function by @xuanyanwow in https://github.com/friendsofhyperf/components/pull/836
* Optimized `GuzzleHttpClientAspect` by @huangdijia in https://github.com/friendsofhyperf/components/pull/837
* feat: add RedisCommandExecutedListener for enhanced Redis command tracking by @huangdijia in https://github.com/friendsofhyperf/components/pull/838
* feature: Added `afterValidatorResolving` method for `validated-dto`. by @xuanyanwow in https://github.com/friendsofhyperf/components/pull/839

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.55.3...v3.1.56

## v3.1.55.3 - 2025-02-07

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.55.2...v3.1.55.3

## v3.1.55.2 - 2025-02-07

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.55.1...v3.1.55.2

## v3.1.54 - 2025-01-22

### What's Changed

* Add `confd.watch` option, default as `true`. by @huangdijia in https://github.com/friendsofhyperf/components/pull/826
* Update nav.ts by @zds-s in https://github.com/friendsofhyperf/components/pull/827
* fix: collapseWithKeys on empty collection by @huangdijia in https://github.com/friendsofhyperf/components/pull/829

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.53...v3.1.54

## v3.1.53 - 2025-01-08

### What's Changed

* Improves phpstan docs for `FriendsOfHyperf\Sentry\Util\SafeCaller` by @xuanyanwow in https://github.com/friendsofhyperf/components/pull/819
* Support auto translate to english by @huangdijia in https://github.com/friendsofhyperf/components/pull/821
* Optimize English translation by @huangdijia in https://github.com/friendsofhyperf/components/pull/822
* ci: add step to get changes in docs translation workflow by @huangdijia in https://github.com/friendsofhyperf/components/pull/823
* Clientside output purifying by @huangdijia in https://github.com/friendsofhyperf/components/pull/824

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.52.1...v3.1.53

## v3.1.52.1 - 2024-12-27

### What's Changed

* Adds translate support by @huangdijia in https://github.com/friendsofhyperf/components/pull/815
* Update the "Command Signal" document, translate the component description into Chinese by @huangdijia in https://github.com/friendsofhyperf/components/pull/816
* Patch docs by @huangdijia in https://github.com/friendsofhyperf/components/pull/817
* Add file translation feature, use OpenAI API to translate Chinese Markdown documents into English by @huangdijia in https://github.com/friendsofhyperf/components/pull/818
* Improve trigger config by @huangdijia in https://github.com/friendsofhyperf/components/pull/813
* refactor(lock): improve CoroutineLock implementation and manage channels with WeakMap by @huangdijia in https://github.com/friendsofhyperf/components/pull/814

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.52...v3.1.52.1

## v3.1.52 - 2024-12-23

### What's Changed

* feat: introduce Once and Onceable classes, deprecate Cache and Backtrace by @huangdijia in https://github.com/friendsofhyperf/components/pull/808
* refactor(cache): refactor Cache class by @huangdijia in https://github.com/friendsofhyperf/components/pull/809
* refactor(cache): update CacheInterface references and remove deprecated Repository interface by @huangdijia in https://github.com/friendsofhyperf/components/pull/810
* Use Str::wrap() instead of nesting Str::start() inside Str::finish() by @huangdijia in https://github.com/friendsofhyperf/components/pull/811
* Prevent HTML injection by @huangdijia in https://github.com/friendsofhyperf/components/pull/812

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.50.1...v3.1.52

## v3.1.50.1 - 2024-12-20

### What's Changed

* Added schedule record for telescope by @guandeng in https://github.com/friendsofhyperf/components/pull/799
* ci: add Code Rabbit configuration by @zds-s in https://github.com/friendsofhyperf/components/pull/801
* docs(zh_CN): add introduction to Amqp Job component by @zds-s in https://github.com/friendsofhyperf/components/pull/802
* docs(cache): add introduction for friendsofhyperf/cache component by @zds-s in https://github.com/friendsofhyperf/components/pull/803
* fix(helper): exclude Model instances from blank check by @huangdijia in https://github.com/friendsofhyperf/components/pull/804
* Update Chinese Documentation for TcpSender Component with Code Examples by @zds-s in https://github.com/friendsofhyperf/components/pull/805
* fix(tests): streamline workflow by combining PHP info commands and adding Redis setup script by @huangdijia in https://github.com/friendsofhyperf/components/pull/806
* docs(amqp-job): add introduction section to clarify component functionality by @huangdijia in https://github.com/friendsofhyperf/components/pull/807

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.50...v3.1.50.1

## v3.1.50 - 2024-12-13

### What's Changed

* refactor(fast-paginate): optimize paginator items retrieval method by @huangdijia in https://github.com/friendsofhyperf/components/pull/788
* refactor(telescope): improve database unique constraint handling by @huangdijia in https://github.com/friendsofhyperf/components/pull/789
* Bumps `hyperf/database` to v3.1.48 by @huangdijia in https://github.com/friendsofhyperf/components/pull/790
* feat(telescope-elasticsearch): implement elasticsearch index management by @huangdijia in https://github.com/friendsofhyperf/components/pull/791
* refactor(telescope): enhance storage driver configuration by @huangdijia in https://github.com/friendsofhyperf/components/pull/792
* Support both v7 and v8 elasticsearch clients (^7.17.0||^8.8.0) by @huangdijia in https://github.com/friendsofhyperf/components/pull/793
* refactor(telescope): improve storage driver configuration by @huangdijia in https://github.com/friendsofhyperf/components/pull/794
* feat(SimpleDTO): implement JsonSerializable interface by @huangdijia in https://github.com/friendsofhyperf/components/pull/795
* docs(telescope-elasticsearch): update component documentation by @huangdijia in https://github.com/friendsofhyperf/components/pull/797
* fix(telescope): replace Laravel branding with Hyperf in UI by @huangdijia in https://github.com/friendsofhyperf/components/pull/796
* docs(Up Sidebars): Add Telescope Elasticsearch Driver by @zds-s in https://github.com/friendsofhyperf/components/pull/798

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.49...v3.1.50

## v3.1.49 - 2024-12-11

### What's Changed

* Updated docs by @guandeng in https://github.com/friendsofhyperf/components/pull/766
* Add purifier component by @trigold1218 in https://github.com/friendsofhyperf/components/pull/768
* Updated docs by @guandeng in https://github.com/friendsofhyperf/components/pull/769
* chore(docs):update purifier markdown files. by @trigold1218 in https://github.com/friendsofhyperf/components/pull/770
* Fix the bug that data loss caused by merging data. by @guandeng in https://github.com/friendsofhyperf/components/pull/771
* Use `new Collection()` instead of `collect` to simplify the call stack. by @huangdijia in https://github.com/friendsofhyperf/components/pull/772
* Auto registers routes for telescope by @huangdijia in https://github.com/friendsofhyperf/components/pull/767
* refactor(telescope): optimize code structure and add authorization by @huangdijia in https://github.com/friendsofhyperf/components/pull/773
* feat(telescope): make telescope enabled by default by @huangdijia in https://github.com/friendsofhyperf/components/pull/774
* refactor(telescope): rename save_mode to record_mode and use enum by @huangdijia in https://github.com/friendsofhyperf/components/pull/775
* feat(telescope): add default authorization middleware by @huangdijia in https://github.com/friendsofhyperf/components/pull/776
* refactor(telescope): optimize controllers and models by @huangdijia in https://github.com/friendsofhyperf/components/pull/778
* Renamed entry creation method from `create` to `store` for better semantics by @huangdijia in https://github.com/friendsofhyperf/components/pull/779
* Add ability to transform `Http\Client\Response` into `Fluent` by @huangdijia in https://github.com/friendsofhyperf/components/pull/781
* refactor(telescope): enhance entry storage and querying by @huangdijia in https://github.com/friendsofhyperf/components/pull/780
* [telescope] refactor: improve configuration and storage handling by @huangdijia in https://github.com/friendsofhyperf/components/pull/783
* Add exceptions as resolved by @guandeng in https://github.com/friendsofhyperf/components/pull/782
* [telescope] refactor: improve repository management architecture by @huangdijia in https://github.com/friendsofhyperf/components/pull/784
* feat: add `telescope-elasticsearch` component by @huangdijia in https://github.com/friendsofhyperf/components/pull/785
* Make the Mailable class tappable. by @huangdijia in https://github.com/friendsofhyperf/components/pull/786

### New Contributors

* @trigold1218 made their first contribution in https://github.com/friendsofhyperf/components/pull/768

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.48...v3.1.49

## v3.1.48 - 2024-12-04

### What's Changed

* 更新文档 by @guandeng in https://github.com/friendsofhyperf/components/pull/759
* Optimize messages are serialized only once by @guandeng in https://github.com/friendsofhyperf/components/pull/761
* Add `web-tinker` component by @huangdijia in https://github.com/friendsofhyperf/components/pull/760
* Add `Request::validate` and `Request::validateWithBag` macros by @huangdijia in https://github.com/friendsofhyperf/components/pull/762
* Update nav.ts by @zds-s in https://github.com/friendsofhyperf/components/pull/763
* Support bind server by @huangdijia in https://github.com/friendsofhyperf/components/pull/764
* fix: 增强 RedisBinLogCurrentSnapshot 的错误处理，清除无效缓存 by @huangdijia in https://github.com/friendsofhyperf/components/pull/765

**Full Changelog**: https://github.com/friendsofhyperf/components/compare/v3.1.47...v3.1.48
