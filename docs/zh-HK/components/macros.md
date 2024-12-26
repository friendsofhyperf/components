# Macros

## 安裝

```shell
composer require friendsofhyperf/macros
```

## 支持方法

### Hyperf\Collection\Arr

- `Arr::sortByMany`

### Hyperf\Collection\Collection

- `Collection::collapseWithKeys`
- `Collection::isSingle`

### Hyperf\Collection\LazyCollection

- `LazyCollection::collapseWithKeys`

### Hyperf\HttpServer\Request

- `Request::allFiles`
- `Request::anyFilled`
- `Request::boolean`
- `Request::collect`
- `Request::date`
- `Request::enum`
- `Request::except`
- `Request::exists`
- `Request::fake`
- `Request::filled`
- `Request::float`
- `Request::fluent`
- `Request::getHost`
- `Request::getHttpHost`
- `Request::getPort`
- `Request::getPsrRequest`
- `Request::getScheme`
- `Request::getSchemeAndHttpHost`
- `Request::hasAny`
- `Request::host`
- `Request::httpHost`
- `Request::integer`
- `Request::isEmptyString`
- `Request::isJson`
- `Request::isNotFilled`
- `Request::isSecure`
- `Request::keys`
- `Request::merge`
- `Request::mergeIfMissing`
- `Request::missing`
- `Request::only`
- `Request::schemeAndHttpHost`
- `Request::str`
- `Request::string`
- `Request::validate`
- `Request::validateWithBag`
- `Request::wantsJson`
- `Request::whenFilled`
- `Request::whenHas`

### Hyperf\Stringable\Str

- `Str::createUuidsNormally`
- `Str::createUuidsUsing`
- `Str::deduplicate`
- `Str::inlineMarkdown`
- `Str::markdown`
- `Str::transliterate`

### Hyperf\Stringable\Stringable

- `Stringable::deduplicate`
- `Stringable::inlineMarkdown`
- `Stringable::markdown`
- `Stringable::toHtmlString`
- `Stringable::whenIsAscii`
