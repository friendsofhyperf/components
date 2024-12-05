# Purifier

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/purifier)](https://packagist.org/packages/friendsofhyperf/purifier)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/purifier)](https://packagist.org/packages/friendsofhyperf/purifier)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/purifier)](https://github.com/friendsofhyperf/purifier)

HTML 过滤器. 派生于 [mews/purifier](https://github.com/mewebstudio/Purifier).

## 安装

使用 Composer 安裝此组件：

```shell
composer require friendsofhyperf/purifier
```

将自动发现服务提供商。您不需要在任何地方添加 Provider.

## 用法

在请求或中间件中使用以下方法，以清理 HTML:

```php
clean($request->get('inputname'));
```

或者

```php
ApplicationContext::getContainer()->get(Purifier::class)->clean($request->get('inputname'));
```

动态配置：

```php
clean('This is my H1 title', 'titles');
clean('This is my H1 title', array('Attr.EnableID' => true));
```

或者

```php
ApplicationContext::getContainer()->get(Purifier::class)->clean('This is my H1 title', 'titles');
ApplicationContext::getContainer()->get(Purifier::class)->clean('This is my H1 title', array('Attr.EnableID' => true));
```

使用 [URI 过滤器](http://htmlpurifier.org/docs/enduser-uri-filter.html)

```php
ApplicationContext::getContainer()->get(Purifier::class)->clean('This is my H1 title', 'titles', function (HTMLPurifier_Config $config) {
    $uri = $config->getDefinition('URI');
    $uri->addFilter(new HTMLPurifier_URIFilter_NameOfFilter(), $config);
});
```

或者，如果您想在数据库模型中清理 HTML，可以使用我们自定义的 casts:

```php
<?php

namespace App\Models;

use Hyperf\DbConnection\Model\Model;
use FriendsOfHyperf\Purifier\Casts\CleanHtml;
use FriendsOfHyperf\Purifier\Casts\CleanHtmlInput;
use FriendsOfHyperf\Purifier\Casts\CleanHtmlOutput;

class Monster extends Model
{
    protected array $casts = [
        'bio' => CleanHtml::class, // 在获取和设置值时都会进行清理
        'description' => CleanHtmlInput::class, // 设置值时清理
        'history' => CleanHtmlOutput::class, // 获取值时进行清理
    ];
}
```

## 配置

要使用您自己的设置，请发布配置.

```sehll
php bin/hyperf.php vendor:publish friendsofhyperf/purifier
```

配置文件 `config/autoload/purifier.php` 内容如下:

```php

return [
    'encoding' => 'UTF-8',
    'finalize' => true,
    'ignore_non_strings' => false,
    'cache_path' => storage_path('app/purifier'),
    'cache_file_mode' => 0755,
    'settings' => [
        'default' => [
            'HTML.Doctype' => 'HTML 4.01 Transitional',
            'HTML.Allowed' => 'div,b,strong,i,em,u,a[href|title],ul,ol,li,p[style],br,span[style],img[width|height|alt|src]',
            'CSS.AllowedProperties' => 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align',
            'AutoFormat.AutoParagraph' => true,
            'AutoFormat.RemoveEmpty' => true,
        ],
        'test' => [
            'Attr.EnableID' => 'true',
        ],
        "youtube" => [
            "HTML.SafeIframe" => 'true',
            "URI.SafeIframeRegexp" => "%^(http://|https://|//)(www.youtube.com/embed/|player.vimeo.com/video/)%",
        ],
        'custom_definition' => [
            'id'  => 'html5-definitions',
            'rev' => 1,
            'debug' => false,
            'elements' => [
                // https://developers.whatwg.org/sections.html
                ['section', 'Block', 'Flow', 'Common'],
                ['nav',     'Block', 'Flow', 'Common'],
                ['article', 'Block', 'Flow', 'Common'],
                ['aside',   'Block', 'Flow', 'Common'],
                ['header',  'Block', 'Flow', 'Common'],
                ['footer',  'Block', 'Flow', 'Common'],
                ['address', 'Block', 'Flow', 'Common'],
                ['hgroup', 'Block', 'Required: h1 | h2 | h3 | h4 | h5 | h6', 'Common'],
                // https://developers.whatwg.org/grouping-content.html
                ['figure', 'Block', 'Optional: (figcaption, Flow) | (Flow, figcaption) | Flow', 'Common'],
                ['figcaption', 'Inline', 'Flow', 'Common'],
                // https://developers.whatwg.org/the-video-element.html#the-video-element
                ['video', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', [
                    'src' => 'URI',
                    'type' => 'Text',
                    'width' => 'Length',
                    'height' => 'Length',
                    'poster' => 'URI',
                    'preload' => 'Enum#auto,metadata,none',
                    'controls' => 'Bool',
                ]],
                ['source', 'Block', 'Flow', 'Common', [
                    'src' => 'URI',
                    'type' => 'Text',
                ]],
                // https://developers.whatwg.org/text-level-semantics.html
                ['s',    'Inline', 'Inline', 'Common'],
                ['var',  'Inline', 'Inline', 'Common'],
                ['sub',  'Inline', 'Inline', 'Common'],
                ['sup',  'Inline', 'Inline', 'Common'],
                ['mark', 'Inline', 'Inline', 'Common'],
                ['wbr',  'Inline', 'Empty', 'Core'],
                // https://developers.whatwg.org/edits.html
                ['ins', 'Block', 'Flow', 'Common', ['cite' => 'URI', 'datetime' => 'CDATA']],
                ['del', 'Block', 'Flow', 'Common', ['cite' => 'URI', 'datetime' => 'CDATA']],
            ],
            'attributes' => [
                ['iframe', 'allowfullscreen', 'Bool'],
                ['table', 'height', 'Text'],
                ['td', 'border', 'Text'],
                ['th', 'border', 'Text'],
                ['tr', 'width', 'Text'],
                ['tr', 'height', 'Text'],
                ['tr', 'border', 'Text'],
            ],
        ],
        'custom_attributes' => [
            ['a', 'target', 'Enum#_blank,_self,_target,_top'],
        ],
        'custom_elements' => [
            ['u', 'Inline', 'Inline', 'Common'],
        ],
    ],

];
```
