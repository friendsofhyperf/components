<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
return [
    'channels' => [
        'sms' => [
            // Timeout for HTTP requests (seconds)
            'timeout' => 5.0,

            // 默认发送配置
            'default' => [
                // Gateway invocation policy, default: sequential invocation
                'strategy' => Overtrue\EasySms\Strategies\OrderStrategy::class,

                // Default available sending gateways
                'gateways' => [
                    'yunpian', 'aliyun',
                ],
            ],
            // Available gateway configurations
            'gateways' => [
                'errorlog' => [
                    'file' => '/tmp/easy-sms.log',
                ],
                'yunpian' => [
                    'api_key' => '824f0ff2f71cab52936axxxxxxxxxx',
                ],
                'aliyun' => [
                    'access_key_id' => '',
                    'access_key_secret' => '',
                    'sign_name' => '',
                ],
            ],
        ],
    ],
];
