# Encryption

Hyperf 加密組件。

## 安裝

```shell
composer require friendsofhyperf/encryption
```

## 發佈配置

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/encryption
```

## 使用

```shell
$encryptString = encrypt($string);
$decryptString = decrypt($encryptString);
```
