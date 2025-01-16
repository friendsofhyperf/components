import {DefaultTheme} from "vitepress";

const nav:DefaultTheme.NavItem[] = [
    { text: '元件', link: '/zh-tw/components/' },
    { text: '常見問題', link: '/zh-tw/faq/index' },
    { text: '更多', items:[
            { text: 'Hyperf', link: 'https://hyperf.wiki/' },
            { text: 'MineAdmin', link: 'https://doc.mineadmin.com/' }
        ]
    }
]

export default nav
