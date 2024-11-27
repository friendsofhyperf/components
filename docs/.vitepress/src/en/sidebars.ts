import {DefaultTheme} from "vitepress";

const sidebar:DefaultTheme.Sidebar = {
    '/zh/guide/': [
        {
            text: 'Introduce',
            items: [
                {
                    text: 'About FriendsOfHyperf',
                    link: '/zh_CN/guide/introduce/about',
                },
            ]
        },
        {
            text: 'Quick Start',
            items: [
                {
                    text: "Components",
                    link: "/zh_CN/guide/start/components"
                }
            ]
        }
    ]
}

export default sidebar