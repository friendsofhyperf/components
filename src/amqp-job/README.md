# Amqp Job

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/amqp-job)](https://packagist.org/packages/friendsofhyperf/amqp-job)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/amqp-job)](https://packagist.org/packages/friendsofhyperf/amqp-job)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/amqp-job)](https://github.com/friendsofhyperf/amqp-job)

The amqp job component for Hyperf.

## Installation

```shell
composer require friendsofhyperf/amqp-job
```

## Usage

### Dispatch

```php
use FriendsOfHyperf\AmqpJob\Job;
use FriendsOfHyperf\AmqpJob\Annotation\AmqpJob
use function FriendsOfHyperf\AmqpJob\dispatch;

#[AmqpJob(exchange: "hyperf", routingKey: "hyperf", enable: true, nums: 1, pool: "default", maxConsumption: 1)]
class FooJob extends Job
{
    
    public function __construct(public $data) {}

    public function handle()
    {
        var_dump($this->data);
    }
}

dispatch(new FooJob());

```

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
