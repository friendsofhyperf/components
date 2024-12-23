# Changelog

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
