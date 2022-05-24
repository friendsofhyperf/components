<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
return [
    'default' => 'v3',
    'message' => 'Google ReCaptcha Verify Fails',
    'v2' => [
        'secret_key' => env('RECAPTCHA_SECRET_V2_KEY', ''),
    ],
    'v3' => [
        'secret_key' => env('RECAPTCHA_SECRET_V3_KEY', ''),
    ],
];
