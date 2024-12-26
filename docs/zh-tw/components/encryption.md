# Encryption

The encryption component for Hyperf.

## 安裝

```shell
composer require friendsofhyperf/encryption
```

## 釋出配置

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/encryption
```

## 使用

```shell
$encryptString = encrypt($string);
$decryptString = decrypt($encryptString);
```
