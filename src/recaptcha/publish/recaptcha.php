<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/recaptcha.
 *
 * @link     https://github.com/friendsofhyperf/recaptcha
 * @document https://github.com/friendsofhyperf/recaptcha/blob/main/README.md
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
