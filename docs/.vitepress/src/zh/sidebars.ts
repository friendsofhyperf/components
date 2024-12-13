import {DefaultTheme} from "vitepress";

const sidebar:DefaultTheme.Sidebar = {
    // '/zh_CN/guide/': [
    //     {
    //         text: '介绍',
    //         items: [
    //             {
    //                 text: '关于 FriendsOfHyperf',
    //                 link: '/zh_CN/guide/introduce/about',
    //             },
    //         ]
    //     },
    //     {
    //         text: '快速开始',
    //         items: [
    //             {
    //                 text: "支持的组件列表",
    //                 link: "/zh_CN/guide/start/components"
    //             }
    //         ]
    //     }
    // ],
    '/zh_CN/faq/':[{
        text: '常见问题',
        items: [
            {
                text: '关于 FriendsOfHyperf',
                link: '/zh_CN/faq/about'
            },
            {
                text: '如何使用',
                link: '/zh_CN/faq/how-to-use'
            }
        ]
    }],
    '/zh_CN/components/':[
        {
            text: '组件',
            items: [
                {
                    text: 'Amqp Job',
                    link: '/zh_CN/components/amqp-job.md'
                },
                {
                    text: 'Cache',
                    link: '/zh_CN/components/cache.md'
                },
                {
                    text: 'Command Signals',
                    link: '/zh_CN/components/command-signals.md'
                },
                {
                    text: 'Command Validation',
                    link: '/zh_CN/components/command-validation.md'
                },
                {
                    text: 'Compoships',
                    link: '/zh_CN/components/compoships.md'
                },
                {
                    text: 'Confd',
                    link: '/zh_CN/components/confd.md'
                },
                {
                    text: 'Config Consul',
                    link: '/zh_CN/components/config-consul.md'
                },
                {
                    text: 'Console Spinner',
                    link: '/zh_CN/components/console-spinner.md'
                },
                {
                    text: 'Di Plus',
                    link: '/zh_CN/components/di-plus.md'
                },
                {
                    text: 'Elasticsearch',
                    link: '/zh_CN/components/elasticsearch.md'
                },
                {
                    text: 'Encryption',
                    link: '/zh_CN/components/encryption.md'
                },
                {
                    text: 'Exception Event',
                    link: '/zh_CN/components/exception-event.md'
                },
                {
                    text: 'Facade',
                    link: '/zh_CN/components/facade.md'
                },
                {
                    text: 'Fast Paginate',
                    link: '/zh_CN/components/fast-paginate.md'
                },
                {
                    text: 'Grpc Validation',
                    link: '/zh_CN/components/grpc-validation.md'
                },
                {
                    text: 'Helpers',
                    link: '/zh_CN/components/helpers.md'
                },
                {
                    text: 'Http Client',
                    link: '/zh_CN/components/http-client.md'
                },
                {
                    text: 'Http Logger',
                    link: '/zh_CN/components/http-logger.md'
                },
                {
                    text: 'Ide Helper',
                    link: '/zh_CN/components/ide-helper.md'
                },
                {
                    text: 'Ipc Broadcaster',
                    link: '/zh_CN/components/ipc-broadcaster.md'
                },
                {
                    text: 'Lock',
                    link: '/zh_CN/components/lock.md'
                },
                {
                    text: 'Macros',
                    link: '/zh_CN/components/macros.md'
                },
                {
                    text: 'Mail',
                    link: '/zh_CN/components/mail.md'
                },
                {
                    text: 'Middleware Plus',
                    link: '/zh_CN/components/middleware-plus.md'
                },
                {
                    text: 'Model Factory',
                    link: '/zh_CN/components/model-factory.md'
                },
                {
                    text: 'Model Hashids',
                    link: '/zh_CN/components/model-hashids.md'
                },
                {
                    text: 'Model Morph Addon',
                    link: '/zh_CN/components/model-morph-addon.md'
                },
                {
                    text: 'Model Observer',
                    link: '/zh_CN/components/model-observer.md'
                },
                {
                    text: 'Model Scope',
                    link: '/zh_CN/components/model-scope.md'
                },
                {
                    text: 'Monolog Hook',
                    link: '/zh_CN/components/monolog-hook.md'
                },
                {
                    text: 'Mysql Grammar Addon',
                    link: '/zh_CN/components/mysql-grammar-addon.md'
                },
                {
                    text: 'Notification',
                    link: '/zh_CN/components/notification.md'
                },
                {
                    text: 'Notification Easysms',
                    link: '/zh_CN/components/notification-easysms.md'
                },
                {
                    text: 'Notification Mail',
                    link: '/zh_CN/components/notification-mail.md'
                },
                {
                    text: 'OpenAI Client',
                    link: '/zh_CN/components/openai-client.md'
                },
                {
                    text: 'Pest Plugin Hyperf',
                    link: '/zh_CN/components/pest-plugin-hyperf.md'
                },
                {
                    text: 'Pretty Console',
                    link: '/zh_CN/components/pretty-console.md'
                },
                {
                    text: 'Purifier',
                    link: '/zh_CN/components/purifier.md'
                },
                {
                    text: 'Recaptcha',
                    link: '/zh_CN/components/recaptcha.md'
                },
                {
                    text: 'Redis Subscriber',
                    link: '/zh_CN/components/redis-subscriber.md'
                },
                {
                    text: 'Sentry',
                    link: '/zh_CN/components/sentry.md'
                },
                {
                    text: 'Support',
                    link: '/zh_CN/components/support.md'
                },
                {
                    text: 'Tcp Sender',
                    link: '/zh_CN/components/tcp-sender.md'
                },
                {
                    text: 'Telescope',
                    link: '/zh_CN/components/telescope.md'
                },
                {
                    text: 'Telescope Elasticsearch Driver',
                    link: '/zh_CN/components/telescope-elasticsearch.md'
                },
                {
                    text: 'Tinker',
                    link: '/zh_CN/components/tinker.md'
                },
                {
                    text: 'Trigger',
                    link: '/zh_CN/components/trigger.md'
                },
                {
                    text: 'Validated DTO',
                    link: '/zh_CN/components/validated-dto.md'
                },
                {
                    text: 'Web Tinker',
                    link: '/zh_CN/components/web-tinker.md'
                }
            ]
        }
    ]
}

export default sidebar