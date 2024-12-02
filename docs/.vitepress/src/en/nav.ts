import {DefaultTheme} from "vitepress";

const nav: DefaultTheme.NavItem[] = [
    // { text: 'Guide', link: '/en/guide/' },
    { text: 'Components', link: '/en/components/' },
    { text: 'FAQ', link: '/en/faq/index' },
    { text: 'More', items: [
            { text: 'Hyperf', link: 'https://hyperf.wiki/' },
            { text: 'MineAdmin', link: 'https://www.mineadmin.com/' }
        ]
    }
]

export default nav