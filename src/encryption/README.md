# Encryption

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/encryption)](https://packagist.org/packages/friendsofhyperf/encryption)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/encryption)](https://packagist.org/packages/friendsofhyperf/encryption)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/encryption)](https://github.com/friendsofhyperf/encryption)

## Installation

```bash
composer require friendsofhyperf/encryption
```

## Publish Config

```bash
php bin/hyperf.php vendor:publish friendsofhyperf/encryption
```

## Usage

```bash
$encryptString = encrypt($string);
$decryptString = decrypt($encryptString);
```
