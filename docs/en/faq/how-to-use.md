# How to Use Components

## Install a Package

Choose a package from the [component catalog](../components/index.md), then install it with
Composer:

```shell
composer require friendsofhyperf/cache
```

## Review Optional Dependencies

Read the component's `composer.json` and documentation. Optional features may require packages
listed under Composer's `suggest` section.

## Publish Configuration

When a component provides publishable configuration, use its documented command:

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/cache
```

Do not run this command blindly: not every component has publishable configuration.

## Verify the Integration

Start the application, exercise the integrated feature, and add an application-level test. When
troubleshooting, include the component version and Hyperf version in the issue report.
