import {DefaultTheme} from "vitepress";

const nav:DefaultTheme.NavItem[] = [
    { text: '组件', link: '/zh-cn/components/' },
    { text: 'FAQ', link: '/zh-cn/faq/index' },
    { text: '更多', items:[
            { text: 'Hyperf', link: 'https://hyperf.wiki/' },
            { text: 'MineAdmin', link: 'https://www.mineadmin.com/' }
        ]
    }
]

export default nav
