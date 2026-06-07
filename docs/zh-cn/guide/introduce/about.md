# 关于 Hyperf Fans

FriendsOfHyperf 是由社区维护的 Hyperf 扩展组件集合，提供面向生产环境的集成能力，
并带来部分受 Laravel 启发的开发体验。

## 仓库模式

源代码统一维护在
[friendsofhyperf/components](https://github.com/friendsofhyperf/components) monorepo 中。
`src/` 下的每个目录也会作为可独立安装的 Composer 包发布。请向 monorepo 提交变更，
不要向自动拆分生成的独立仓库提交。

## 兼容性

当前分支面向 PHP 8.2 或更高版本以及 Hyperf 3.2。各组件页面会说明可选集成和额外依赖。

## 文档语言

文档同时维护英文、简体中文、香港繁体中文和台湾繁体中文。每次文档变更都必须保持四种语言
的页面路径与标题结构一致。

## 参与贡献

请在 monorepo 中提交 Issue 和 Pull Request。行为变更应在同一个 Pull Request 中更新组件测试
以及全部四种语言的文档。
