<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use function Hyperf\Support\env;

return [
    'key' => (string) env('APP_KEY', 'base64:MhEHk72OcV2ttAljUu9Caaam3iP2BnGcwb6GWKkUfV4='),
    'cipher' => (string) env('APP_CIPHER', 'AES-256-CBC'),
];
