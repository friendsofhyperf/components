import { defineConfig } from 'vitepress'
import enGetConfig from "./src/en/config";
import enGetNavs from "./src/en/nav";
import enGetSidebar from "./src/en/sidebars";
import zhGetConfig from "./src/zh/config";
import zhGetNavs from "./src/zh/nav";
import zhGetSidebar from "./src/zh/sidebars";
import taskLists from 'markdown-it-task-lists' 

// https://vitepress.dev/reference/site-config
export default defineConfig({
  title: "FriendsOfHyperf",
  description: "🚀 The most popular components for Hyperf.",
  ignoreDeadLinks: true,
  head: [
    [
      'script',
      {},
      `
      var _hmt = _hmt || [];
      (function() {
        var hm = document.createElement("script");
        hm.src = "https://hm.baidu.com/hm.js?df278fe462855b3046c941a8adc19064";
        var s = document.getElementsByTagName("script")[0]; 
        s.parentNode.insertBefore(hm, s);
      })();
      `
    ]
  ],
  locales:{
    root:{
      label:"中文",
      lang:"zh",
      ...zhGetConfig,
    },
    en:{
      label:"English",
      lang:"en",
      link:"/en/index",
      ...enGetConfig,
      themeConfig:{
        logo: '/logo.svg',
        nav: enGetNavs,
        sidebar:enGetSidebar,
        outline:{
          level:[2 ,4],
        },
      }
    }
  },
  markdown: {
    config: (md) => {
      // use more markdown-it plugins!
      md.use(taskLists)
    }
  },
  sitemap: {
    hostname: 'https://hyperf.fans'
  },
  themeConfig: {
    outline:{
      label: '页面导航',
      level: [2, 4],
    },
    editLink: {
      pattern: 'https://github.com/friendsofhyperf/components/edit/main/docs/:path',
      text: '在Github上编辑此页面',
    },
    lastUpdated: {
      text: '最后更新于',
    },
    docFooter: {
      next: '下一页',
      prev: '上一页',
    },
    sidebarMenuLabel: '菜单',
    returnToTopLabel: '回到顶部',
    search:{
      provider:"local",
      options:{
        locales: {
          zh: {
            translations: {
              button: {
                buttonText: '搜索文档',
                buttonAriaLabel: '搜索文档'
              },
              modal: {
                noResultsText: '无法找到相关结果',
                resetButtonTitle: '清除查询条件',
                footer: {
                  selectText: '选择',
                  navigateText: '切换'
                }
              }
            }
          },
          en: {
            translations: {
              button: {
                buttonText: 'Search documents',
                buttonAriaLabel: 'Search documents'
              },
              modal: {
                noResultsText: 'No relevant results found',
                resetButtonTitle: 'Clear query conditions',
                footer: {
                  selectText: 'Select',
                  navigateText: 'Switch'
                }
              }
            }
          }
        }
      }
    },
    i18nRouting:false,
    // https://vitepress.dev/reference/default-theme-config

    nav: zhGetNavs,

    sidebar: zhGetSidebar,

    socialLinks: [
      { icon: 'github', link: 'https://github.com/friendsofhyperf/components' },
    ]
  }
})
