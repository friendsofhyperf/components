import {DefaultTheme} from "vitepress";

const nav:DefaultTheme.NavItem[] = [
    // { text: '入门', link: '/zh_CN/guide/' },
    { text: '组件', link: '/zh_CN/components/' },
    { text: 'FAQ', link: '/zh_CN/faq/index' },
    { text: '更多', items:[
            { text: 'Hyperf', link: 'https://hyperf.wiki/' },
            { text: 'MineAdmin', link: 'https://www.mineadmin.com/' }
        ]
    }
]

export default nav
