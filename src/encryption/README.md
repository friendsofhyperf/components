# Encryption

[![Latest Stable Version](https://poser.pugx.org/friendsofhyperf/encryption/version.png)](https://packagist.org/packages/friendsofhyperf/encryption)
[![Total Downloads](https://poser.pugx.org/friendsofhyperf/encryption/d/total.png)](https://packagist.org/packages/friendsofhyperf/encryption)
[![GitHub license](https://img.shields.io/github/license/friendsofhyperf/encryption)](https://github.com/friendsofhyperf/encryption)

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
