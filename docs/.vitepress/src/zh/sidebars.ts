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
            text: '开始',
            items: [
                {
                    text: "支持的组件列表",
                    link: "/zh_CN/guide/start/components"
                }
            ]
        }
    ],
    '/zh_CN/reference/':[
        {
            text: 'Amqp Job',
            link: '/zh_CN/reference/amqp-job'
        },
        {
            text: 'Cache',
            link: '/zh_CN/reference/cache'
        }
    ]
}

export default sidebar