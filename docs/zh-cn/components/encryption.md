# Encryption

Hyperf 加密组件。

## 安装

```shell
composer require friendsofhyperf/encryption
```

## 发布配置

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/encryption
```

## 使用

```shell
$encryptString = encrypt($string);
$decryptString = decrypt($encryptString);
```
