<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
use function Hyperf\Support\env;

return [
    'base_uri' => env('OPENAI_BASE_URI', 'api.openai.com/v1'),
    'api_key' => env('OPENAI_API_KEY', ''),
    'organization' => env('OPENAI_ORGANIZATION'),
    'request_timeout' => (int) env('OPENAI_REQUEST_TIMEOUT', 30),
];
