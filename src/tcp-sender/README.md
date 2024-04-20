# Support

[![Latest Stable Version](https://poser.pugx.org/friendsofhyperf/tcp-sender/version.png)](https://packagist.org/packages/friendsofhyperf/tcp-sender)
[![Total Downloads](https://poser.pugx.org/friendsofhyperf/tcp-sender/d/total.png)](https://packagist.org/packages/friendsofhyperf/tcp-sender)
[![GitHub license](https://img.shields.io/github/license/friendsofhyperf/tcp-sender)](https://github.com/friendsofhyperf/tcp-sender)

Another support component for Hyperf.

## Installation

- Installation

```shell
composer require friendsofhyperf/tcp-sender
```
## Usage

```php
namespace App\Service;

use Hyperf\Di\Annotation\Inject;
use Friendsofhyperf\TcpSender\Sender;

class YourService {
    #[Inject]
    private Sender $sender; 
    
    public function send(): void {
        $fd = 1;
        $data = 'hello';
        $this->sender->send($fd,$data);
    }
}

```
## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
