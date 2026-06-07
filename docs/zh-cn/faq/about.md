# 关于 FriendsOfHyperf

## FriendsOfHyperf 是什么？

FriendsOfHyperf 是维护 Hyperf 可复用组件的社区项目。组件集涵盖开发工具、数据库辅助功能、
消息通信、数据验证、监控以及外部服务集成。

## 仓库如何组织？

`components` 仓库是唯一事实来源。`src/` 下的每个目录都是可独立安装的 Composer 包，
而 `friendsofhyperf/components` 会安装完整组件集。

## 组件如何发布？

发布流程会将 monorepo 拆分为只读的独立组件仓库。Issue 和 Pull Request 都应提交到 monorepo。

## 文档如何维护？

文档站点使用 VitePress，并为四种语言维护路径一致的页面。提交文档变更前，请在仓库根目录运行
`npm run docs:check`。
