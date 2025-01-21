<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Purifier;

use Closure;
use Exception;
use HTMLPurifier;
use HTMLPurifier_AttrDef;
use HTMLPurifier_Config;
use HTMLPurifier_HTMLDefinition;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Support\Filesystem\Filesystem;
use WeakMap;

class Purifier
{
    /**
     * @var WeakMap<object, HTMLPurifier>
     */
    protected WeakMap $instances;

    /**
     * @var array<string, HTMLPurifier_Config>
     */
    protected array $configObjects = [];

    /**
     * Constructor.
     *
     * @throws Exception
     */
    public function __construct(protected Filesystem $files, protected ConfigInterface $config)
    {
        if (! $this->config->has('purifier')) {
            throw new Exception('Configuration parameters not loaded!');
        }
        $this->checkCacheDirectory();
        $this->instances = new WeakMap();
    }

    /**
     * @template T
     * @param T $dirty
     * @return ($dirty is string ? string : ($dirty is array ? array : T))
     */
    public function clean(mixed $dirty, array|string|null $config = null, ?Closure $postCreateConfigHook = null): mixed
    {
        if (is_array($dirty)) {
            return array_map(fn ($item) => $this->clean($item, $config), $dirty);
        }

        $configObject = $this->getConfig($config);
        $postCreateConfigHook?->call($this, $configObject);

        // If $dirty is not an explicit string, bypass purification assuming configuration allows this
        $ignoreNonStrings = (bool) $this->config->get('purifier.ignore_non_strings', false);

        if (is_string($dirty) === false && $ignoreNonStrings === true) {
            return $dirty;
        }

        $this->instances[$configObject] ??= new HTMLPurifier($configObject);

        return $this->instances[$configObject]->purify($dirty, $configObject);
    }

    /**
     * Get HTMLPurifier instance.
     * @deprecated since v3.1, will removed in v3.2
     */
    public function getInstance(): HTMLPurifier
    {
        return new HTMLPurifier($this->getConfig());
    }

    protected function getConfig(array|string|null $config = null): HTMLPurifier_Config
    {
        $configKey = serialize($config);

        if (isset($this->configObjects[$configKey])) {
            return $this->configObjects[$configKey];
        }

        // Create a new configuration object
        $configObject = HTMLPurifier_Config::createDefault();

        // Allow configuration to be modified
        if (! $this->config->get('purifier.finalize')) {
            $configObject->autoFinalize = false;
        }

        // Set default config
        $defaultConfig = [];
        $defaultConfig['Core.Encoding'] = $this->config->get('purifier.encoding');
        $defaultConfig['Cache.SerializerPath'] = $this->config->get('purifier.cache_path');
        $defaultConfig['Cache.SerializerPermissions'] = $this->config->get('purifier.cache_file_mode', 0755);

        if (! $config) {
            $config = $this->config->get('purifier.settings.default');
        } elseif (is_string($config)) {
            $config = $this->config->get('purifier.settings.' . $config);
        }

        if (! is_array($config)) {
            $config = [];
        }

        // Merge configurations
        $config = $defaultConfig + $config;

        // Load to Purifier config
        $configObject->loadArray($config);

        // Load custom definition if set
        if ($definitionConfig = $this->config->get('purifier.settings.custom_definition')) {
            $this->addCustomDefinition($definitionConfig, $configObject);
        }

        // Load custom elements if set
        if ($elements = $this->config->get('purifier.settings.custom_elements')) {
            if ($def = $configObject->maybeGetRawHTMLDefinition()) {
                $this->addCustomElements($elements, $def);
            }
        }

        // Load custom attributes if set
        if ($attributes = $this->config->get('purifier.settings.custom_attributes')) {
            if ($def = $configObject->maybeGetRawHTMLDefinition()) {
                $this->addCustomAttributes($attributes, $def);
            }
        }

        return $this->configObjects[$configKey] = $configObject;
    }

    /**
     * Add a custom definition.
     *
     * @see http://htmlpurifier.org/docs/enduser-customize.html
     *
     * @param HTMLPurifier_Config|null $configObject Defaults to using default config
     */
    private function addCustomDefinition(array $definitionConfig, ?HTMLPurifier_Config $configObject = null): void
    {
        if (! $configObject) {
            $configObject = HTMLPurifier_Config::createDefault();
            $configObject->loadArray($this->getConfig()->getAll());
        }

        // Setup the custom definition
        $configObject->set('HTML.DefinitionID', $definitionConfig['id']);
        $configObject->set('HTML.DefinitionRev', $definitionConfig['rev']);

        // Enable debug mode
        if (! isset($definitionConfig['debug']) || $definitionConfig['debug']) {
            $configObject->set('Cache.DefinitionImpl', null);
        }

        // Start configuring the definition
        if ($def = $configObject->maybeGetRawHTMLDefinition()) {
            // Create the definition attributes
            if (! empty($definitionConfig['attributes'])) {
                $this->addCustomAttributes($definitionConfig['attributes'], $def);
            }

            // Create the definition elements
            if (! empty($definitionConfig['elements'])) {
                $this->addCustomElements($definitionConfig['elements'], $def);
            }
        }
    }

    /**
     * Add provided attributes to the provided definition.
     */
    private function addCustomAttributes(array $attributes, HTMLPurifier_HTMLDefinition $definition): void
    {
        foreach ($attributes as $attribute) {
            // Get configuration of attribute
            $required = ! empty($attribute[3]);
            $onElement = $attribute[0];
            $attrName = $required ? $attribute[1] . '*' : $attribute[1];
            $validValues = $attribute[2];

            if ($onElement === '*') {
                $def = $validValues;
                if (is_string($validValues)) {
                    $def = new $validValues();
                }

                if ($def instanceof HTMLPurifier_AttrDef) {
                    $definition->info_global_attr[$attrName] = $def;
                }

                continue;
            }

            if (class_exists($validValues)) {
                $validValues = new $validValues();
            }

            $definition->addAttribute($onElement, $attrName, $validValues);
        }
    }

    /**
     * Add provided elements to the provided definition.
     */
    private function addCustomElements(array $elements, HTMLPurifier_HTMLDefinition $definition): void
    {
        foreach ($elements as $element) {
            // Get configuration of element
            $name = $element[0];
            $contentSet = $element[1];
            $allowedChildren = $element[2];
            $attributeCollection = $element[3];
            $attributes = $element[4] ?? null;

            if (! empty($attributes)) {
                $definition->addElement($name, $contentSet, $allowedChildren, $attributeCollection, $attributes);
            } else {
                $definition->addElement($name, $contentSet, $allowedChildren, $attributeCollection);
            }
        }
    }

    /**
     * Check/Create cache directory.
     */
    private function checkCacheDirectory(): void
    {
        $cachePath = $this->config->get('purifier.cache_path');

        if ($cachePath && ! $this->files->isDirectory($cachePath)) {
            $this->files->makeDirectory(
                $cachePath,
                $this->config->get('purifier.cache_file_mode', 0755),
                true
            );
        }
    }
}
