import {DefaultTheme} from "vitepress";

const nav:DefaultTheme.NavItem[] = [
    { text: '組件', link: '/zh-hk/components/' },
    { text: 'FAQ', link: '/zh-hk/faq/index' },
    { text: '更多', items:[
            { text: 'Hyperf', link: 'https://hyperf.wiki/' },
            { text: 'MineAdmin', link: 'https://www.mineadmin.com/' }
        ]
    }
]

export default nav
