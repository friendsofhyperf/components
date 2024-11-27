import {DefaultTheme} from "vitepress";

const sidebar:DefaultTheme.Sidebar = {
    '/zh/guide/': [
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
                    text: "组件列表",
                    link: "/zh_CN/guide/start/components"
                }
            ]
        }
    ]
}

export default sidebar