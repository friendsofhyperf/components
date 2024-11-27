import {DefaultTheme} from "vitepress";

const sidebar:DefaultTheme.Sidebar = {
    '/zh_CN/guide/': [
        {
            text: '介绍',
            items: [
                {
                    text: '关于 FriendsOfHyperf',
                    link: '/zh_CN/guide/introduce/about',
                },
            ]
        },
        {
            text: '快速开始',
            items: [
                {
                    text: "支持的组件列表",
                    link: "/zh_CN/guide/start/components"
                }
            ]
        }
    ],
    '/zh_CN/docs/':[
        {
            text: '组件',
            items: [
                {
                    text: 'Amqp Job',
                    link: '/zh_CN/docs/amqp-job'
                },
                {
                    text: 'Cache',
                    link: '/zh_CN/docs/cache'
                },
                {
                    text: 'Command Signals',
                    link: '/zh_CN/docs/command-signals'
                },
                {
                    text: 'Command Validation',
                    link: '/zh_CN/docs/command-validation'
                },
                {
                    text: 'Compoships',
                    link: '/zh_CN/docs/compoships'
                },
                {
                    text: 'Confd',
                    link: '/zh_CN/docs/confd'
                },
                {
                    text: 'Config Consul',
                    link: '/zh_CN/docs/config-consul'
                },
                {
                    text: 'Console Spinner',
                    link: '/zh_CN/docs/console-spinner'
                },
                {
                    text: 'Di Plus',
                    link: '/zh_CN/docs/di-plus'
                },
                {
                    text: 'Elasticsearch',
                    link: '/zh_CN/docs/elasticsearch'
                },
                {
                    text: 'Encryption',
                    link: '/zh_CN/docs/encryption'
                },
                {
                    text: 'Exception Event',
                    link: '/zh_CN/docs/exception-event'
                },
                {
                    text: 'Facade',
                    link: '/zh_CN/docs/facade'
                },
                {
                    text: 'Fast Paginate',
                    link: '/zh_CN/docs/fast-paginate'
                },
                {
                    text: 'Grpc Validation',
                    link: '/zh_CN/docs/grpc-validation'
                },
                {
                    text: 'Helpers',
                    link: '/zh_CN/docs/helpers'
                },
                {
                    text: 'Http Client',
                    link: '/zh_CN/docs/http-client'
                },
                {
                    text: 'Http Logger',
                    link: '/zh_CN/docs/http-logger'
                },
                {
                    text: 'Ide Helper',
                    link: '/zh_CN/docs/ide-helper'
                },
                {
                    text: 'Ipc Broadcaster',
                    link: '/zh_CN/docs/ipc-broadcaster'
                },
                {
                    text: 'Lock',
                    link: '/zh_CN/docs/lock'
                },
                {
                    text: 'Macros',
                    link: '/zh_CN/docs/macros'
                },
                {
                    text: 'Mail',
                    link: '/zh_CN/docs/mail'
                },
                {
                    text: 'Middleware Plus',
                    link: '/zh_CN/docs/middleware-plus'
                },
                {
                    text: 'Model Factory',
                    link: '/zh_CN/docs/model-factory'
                },
                {
                    text: 'Model Hashids',
                    link: '/zh_CN/docs/model-hashids'
                },
                {
                    text: 'Model Morph Addon',
                    link: '/zh_CN/docs/model-morph-addon'
                },
                {
                    text: 'Model Observer',
                    link: '/zh_CN/docs/model-observer'
                },
                {
                    text: 'Model Scope',
                    link: '/zh_CN/docs/model-scope'
                },
                {
                    text: 'Monolog Hook',
                    link: '/zh_CN/docs/monolog-hook'
                },
                {
                    text: 'Mysql Grammar Addon',
                    link: '/zh_CN/docs/mysql-grammar-addon'
                },
                {
                    text: 'Notification',
                    link: '/zh_CN/docs/notification'
                },
                {
                    text: 'Notification Easysms',
                    link: '/zh_CN/docs/notification-easysms'
                },
                {
                    text: 'Notification Mail',
                    link: '/zh_CN/docs/notification-mail'
                },
                {
                    text: 'OpenAI Client',
                    link: '/zh_CN/docs/openai-client'
                },
                {
                    text: 'Pest Plugin Hyperf',
                    link: '/zh_CN/docs/pest-plugin-hyperf'
                },
                {
                    text: 'Pretty Console',
                    link: '/zh_CN/docs/pretty-console'
                },
                {
                    text: 'Recaptcha',
                    link: '/zh_CN/docs/recaptcha'
                },
                {
                    text: 'Redis Subscriber',
                    link: '/zh_CN/docs/redis-subscriber'
                },
                {
                    text: 'Sentry',
                    link: '/zh_CN/docs/sentry'
                },
                {
                    text: 'Support',
                    link: '/zh_CN/docs/support'
                },
                {
                    text: 'Tcp Sender',
                    link: '/zh_CN/docs/tcp-sender'
                },
                {
                    text: 'Telescope',
                    link: '/zh_CN/docs/telescope'
                },
                {
                    text: 'Tinker',
                    link: '/zh_CN/docs/tinker'
                },
                {
                    text: 'Trigger',
                    link: '/zh_CN/docs/trigger'
                },
                {
                    text: 'Validated DTO',
                    link: '/zh_CN/docs/validated-dto'
                }
            ]
        }
    ]
}

export default sidebar