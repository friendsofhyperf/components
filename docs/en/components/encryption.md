# Encryption

Hyperf Encryption Component.

## Installation

```shell
composer require friendsofhyperf/encryption
```

## Publish Configuration

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/encryption
```

## Usage

```php
$encryptString = encrypt($string);
$decryptString = decrypt($encryptString);
```