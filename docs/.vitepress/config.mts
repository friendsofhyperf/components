import { defineConfig } from 'vitepress'

import enConfig from "./src/en/config";
import enNav from "./src/en/nav";
import enSidebar from "./src/en/sidebars";

import cnConfig from "./src/zh-cn/config";
import cnNav from "./src/zh-cn/nav";
import cnSidebar from "./src/zh-cn/sidebars";

import hkConfig from "./src/zh-hk/config";
import hkNav from "./src/zh-hk/nav";
import hkSidebar from "./src/zh-hk/sidebars";

import twConfig from "./src/zh-tw/config";
import twNav from "./src/zh-tw/nav";
import twSidebar from "./src/zh-tw/sidebars";

import taskLists from 'markdown-it-task-lists'

// https://vitepress.dev/reference/site-config
export default defineConfig({
  title: "Hyperf Fans",
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
    ],
    [
      'script',
      {},
      `
    (function(c,l,a,r,i,t,y){
        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", "pgqv4ftymf");
      `
    ]
  ],
  locales: {
    root: {
      label: "简体中文",
      lang: "zh",
      ...cnConfig,
    },
    "zh-hk": {
      label: "繁體中文（港）",
      lang: "zh-hk",
      link: "/zh-hk/index",
      ...hkConfig,
      themeConfig: {
        logo: '/logo.svg',
        nav: hkNav,
        sidebar: hkSidebar,
        outline: {
          level: [2, 4],
        },
      }
    },
    "zh-tw": {
      label: "繁體中文（臺）",
      lang: "zh-tw",
      link: "/zh-tw/index",
      ...twConfig,
      themeConfig: {
        logo: '/logo.svg',
        nav: twNav,
        sidebar: twSidebar,
        outline: {
          level: [2, 4],
        },
      }
    },
    en: {
      label: "English",
      lang: "en",
      link: "/en/index",
      ...enConfig,
      themeConfig: {
        logo: '/logo.svg',
        nav: enNav,
        sidebar: enSidebar,
        outline: {
          level: [2, 4],
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
    hostname: 'https://docs.hdj.me'
  },
  themeConfig: {
    outline: {
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
    search: {
      provider: "local",
      options: {
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

    i18nRouting: false,

    // https://vitepress.dev/reference/default-theme-config
    nav: cnNav,
    sidebar: cnSidebar,

    socialLinks: [
      { icon: 'github', link: 'https://github.com/friendsofhyperf/components' },
    ]
  }
})
