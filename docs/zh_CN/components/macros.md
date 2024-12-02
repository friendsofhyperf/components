# Macros

## 安装

```shell
composer require friendsofhyperf/macros
```

## 支持方法

### Hyperf\Collection\Arr

- `Arr::sortByMany`

### Hyperf\Collection\Collection

- `Collection::isSingle`
- `Collection::collapseWithKeys`

### Hyperf\Collection\LazyCollection

- `LazyCollection::collapseWithKeys`

### Hyperf\HttpServer\Request

- `Request::allFiles`
- `Request::anyFilled`
- `Request::boolean`
- `Request::collect`
- `Request::date`
- `Request::except`
- `Request::fake`
- `Request::filled`
- `Request::hasAny`
- `Request::isEmptyString`
- `Request::isNotFilled`
- `Request::keys`
- `Request::host`
- `Request::getHost`
- `Request::httpHost`
- `Request::getHttpHost`
- `Request::getPort`
- `Request::getPsrRequest`
- `Request::getScheme`
- `Request::isSecure`
- `Request::getSchemeAndHttpHost`
- `Request::schemeAndHttpHost`
- `Request::merge`
- `Request::mergeIfMissing`
- `Request::missing`
- `Request::only`
- `Request::wantsJson`
- `Request::whenFilled`
- `Request::whenHas`
- `Request::isJson`
- `Request::enum`
- `Request::exists`
- `Request::str`
- `Request::string`
- `Request::integer`
- `Request::validate`
- `Request::validateWithBag`

### Hyperf\Stringable\Str

- `Str::createUuidsUsing`
- `Str::createUuidsNormally`
- `Str::deduplicate`
- `Str::markdown`
- `Str::inlineMarkdown`
- `Str::transliterate`

### Hyperf\Stringable\Stringable

- `Stringable::deduplicate`
- `Stringable::markdown`
- `Stringable::inlineMarkdown`
- `Stringable::toHtmlString`
- `Stringable::whenIsAscii`
