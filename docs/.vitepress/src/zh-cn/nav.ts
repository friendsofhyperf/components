import {DefaultTheme} from "vitepress";

const nav:DefaultTheme.NavItem[] = [
    { text: '组件', link: '/zh-cn/components/' },
    { text: '常见问题', link: '/zh-cn/faq/index' },
    { text: '更多', items:[
            { text: 'Hyperf', link: 'https://hyperf.wiki/' },
            { text: 'MineAdmin', link: 'https://doc.mineadmin.com/' }
        ]
    }
]

export default nav
