# Purifier

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/purifier)](https://packagist.org/packages/friendsofhyperf/purifier)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/purifier)](https://packagist.org/packages/friendsofhyperf/purifier)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/purifier)](https://github.com/friendsofhyperf/purifier)

HTML filter. forked from [mews/purifier](https://github.com/mewebstudio/Purifier).

## Installation

Require this package with composer:

```shell
composer require friendsofhyperf/purifier
```

The service provider will be auto-discovered. You do not need to add the provider anywhere.

## Usage

Use these methods inside your requests or middleware, wherever you need the HTML cleaned up:

```php
clean($request->get('inputname'));
```

or

```php
ApplicationContext::getContainer()->get(Purifier::class)->clean($request->get('inputname'));
```

dynamic config

```php
clean('This is my H1 title', 'titles');
clean('This is my H1 title', array('Attr.EnableID' => true));
```

or

```php
ApplicationContext::getContainer()->get(Purifier::class)->clean('This is my H1 title', 'titles');
ApplicationContext::getContainer()->get(Purifier::class)->clean('This is my H1 title', array('Attr.EnableID' => true));
```

use [URI filter](http://htmlpurifier.org/docs/enduser-uri-filter.html)

```php
ApplicationContext::getContainer()->get(Purifier::class)->clean('This is my H1 title', 'titles', function (HTMLPurifier_Config $config) {
    $uri = $config->getDefinition('URI');
    $uri->addFilter(new HTMLPurifier_URIFilter_NameOfFilter(), $config);
});
```

Alternatively, if you're looking to clean your HTML inside your Eloquent models, you can use our custom casts:

```php
<?php

namespace App\Models;

use Hyperf\DbConnection\Model\Model;
use Trigold\Purifier\Casts\CleanHtml;
use Trigold\Purifier\Casts\CleanHtmlInput;
use Trigold\Purifier\Casts\CleanHtmlOutput;

class Monster extends Model
{
    protected array $casts = [
        'bio' => CleanHtml::class, // cleans both when getting and setting the value
        'description' => CleanHtmlInput::class, // cleans when setting the value
        'history' => CleanHtmlOutput::class, // cleans when getting the value
    ];
}
```

## Configuration

To use your own settings, publish config.

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/purifier
```

Config file `config/autoload/purifier.php` should like this

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
                // Content model actually excludes several tags, not modelled here
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
