# Changelog

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
