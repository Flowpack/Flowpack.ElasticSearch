<?php
declare(strict_types=1);

namespace Flowpack\ElasticSearch\Domain\Model;

/*
 * This file is part of the Flowpack.ElasticSearch package.
 *
 * (c) Contributors of the Flowpack Team - flowpack.org
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Flowpack\ElasticSearch\Exception as ElasticSearchException;
use Flowpack\ElasticSearch\Service\DynamicIndexSettingService;
use Flowpack\ElasticSearch\Transfer\Response;
use Neos\Utility\Arrays;
use Neos\Flow\Annotations as Flow;

/**
 * Representation of an Index
 */
class Index
{
    /**
     * @var array
     * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-update-settings.html
     */
    static protected $updatableSettings = [
        'index.number_of_replicas',
        'index.auto_expand_replicas',
        'index.blocks.read_only',
        'index.blocks.read',
        'index.blocks.write',
        'index.blocks.metadata',
        'index.refresh_interval',
        'index.index_concurrency',
        'index.codec',
        'index.codec.bloom.load',
        'index.fail_on_merge_failure',
        'index.translog.flush_threshold_ops',
        'index.translog.flush_threshold_size',
        'index.translog.flush_threshold_period',
        'index.translog.disable_flush',
        'index.cache.filter.max_size',
        'index.cache.filter.expire',
        'index.gateway.snapshot_interval',
        'index.routing.allocation.include',
        'index.routing.allocation.exclude',
        'index.routing.allocation.require',
        'index.routing.allocation.disable_allocation',
        'index.routing.allocation.disable_new_allocation',
        'index.routing.allocation.disable_replica_allocation',
        'index.routing.allocation.enable',
        'index.routing.allocation.total_shards_per_node',
        'index.recovery.initial_shards',
        'index.gc_deletes',
        'index.ttl.disable_purge',
        'index.translog.fs.type',
        'index.compound_format',
        'index.compound_on_flush',
        'index.warmer.enabled',
    ];

    static protected $allowedIndexCreateKeys = [
        'settings',
        'aliases',
        'mappings'
    ];

    /**
     * @var DynamicIndexSettingService
     * @Flow\Inject
     */
    protected $dynamicIndexSettingService;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $settingsKey;

    /**
     * The owner client of this index. Could be set later in order to allow creating pending Index objects
     *
     * @var Client
     */
    protected $client;

    /**
     * These are the Flow "Settings" aka Configuration, NOT the index settings
     * @var array
     */
    protected $settings;

    /**
     * @param string $name
     * @param Client $client $client
     * @throws ElasticSearchException
     */
    public function __construct(string $name, Client $client = null)
    {
        $name = trim($name);
        if (empty($name) || strpos($name, '_') === 0) {
            throw new ElasticSearchException('The provided index name "' . $name . '" must not be empty and not start with an underscore.', 1340187948);
        }

        if ($name !== strtolower($name)) {
            throw new ElasticSearchException('The provided index name "' . $name . '" must be all lowercase.', 1340187956);
        }

        $this->name = $name;
        $this->settingsKey = $name;
        $this->client = $client;
    }

    /**
     * Inject the framework settings
     *
     * @param array $settings
     * @return void
     */
    public function injectSettings(array $settings): void
    {
        $this->settings = $settings;
    }

    /**
     * @param string $typeName
     * @return AbstractType
     */
    public function findType(string $typeName): AbstractType
    {
        return new GenericType($this, $typeName);
    }

    /**
     * @param array<AbstractType> $types
     * @return TypeGroup
     */
    public function findTypeGroup(array $types): TypeGroup
    {
        return new TypeGroup($this, $types);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function exists(): bool
    {
        $response = $this->request('HEAD');

        return $response->getStatusCode() === 200;
    }

    /**
     * @param string $method
     * @param string $path
     * @param array $arguments
     * @param string|array $content
     * @param bool $prefixIndex
     * @return Response
     * @throws ElasticSearchException
     * @throws \Neos\Flow\Http\Exception
     */
    public function request(string $method, string $path = null, array $arguments = [], $content = null, bool $prefixIndex = true): Response
    {
        if ($this->client === null) {
            throw new ElasticSearchException('The client of the index "' . $this->prefixName() . '" is not set, hence no requests can be done.', 1566313883);
        }
        $path = ltrim($path ? trim($path) : '', '/');
        if ($prefixIndex === true) {
            $path = '/' . $this->prefixName() . '/' . $path;
        } else {
            $path = '/' . ltrim($path, '/');
        }

        return $this->client->request($method, $path, $arguments, $content);
    }

    /**
     * @throws ElasticSearchException
     * @throws \Neos\Flow\Http\Exception
     */
    public function create(): void
    {
        $indexConfiguration = $this->getConfiguration() ?? [];
        $indexCreateObject = array_filter($indexConfiguration, static fn($key) => in_array($key, self::$allowedIndexCreateKeys, true), ARRAY_FILTER_USE_KEY);
        $this->request('PUT', null, [], $this->encodeRequestBody($indexCreateObject));
    }

    /**
     * @return array|null
     */
    protected function getConfiguration(): ?array
    {
        if ($this->client instanceof Client) {
            $path = 'indexes.' . $this->client->getBundle() . '.' . $this->settingsKey;
        } else {
            $path = 'indexes.default' . '.' . $this->settingsKey;
        }

        $cconfiguration = Arrays::getValueByPath($this->settings, $path);
        return $cconfiguration !== null ? $this->dynamicIndexSettingService->process($cconfiguration, $path, $this->name) : $cconfiguration;
    }

    /**
     * @throws ElasticSearchException
     * @throws \Neos\Flow\Http\Exception
     */
    public function updateSettings(): void
    {
        // we only ever need the settings path from all the settings.
        $settings = $this->getConfiguration()['settings'] ?? [];
        $updatableSettings = [];
        foreach (static::$updatableSettings as $settingPath) {
            $setting = Arrays::getValueByPath($settings, $settingPath);
            if ($setting !== null) {
                $updatableSettings = Arrays::setValueByPath($updatableSettings, $settingPath, $setting);
            }
        }
        if ($updatableSettings !== []) {
            $this->request('PUT', '/_settings', [], $this->encodeRequestBody($updatableSettings));
        }
    }

    /**
     * @return Response
     * @throws ElasticSearchException
     * @throws \Neos\Flow\Http\Exception
     */
    public function delete(): Response
    {
        return $this->request('DELETE');
    }

    /**
     * Refresh the index
     *
     * @return Response
     * @throws ElasticSearchException
     * @throws \Neos\Flow\Http\Exception
     */
    public function refresh(): Response
    {
        return $this->request('POST', '/_refresh');
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->prefixName();
    }

    public function getOriginalName(): string
    {
        return $this->name;
    }

    /**
     * @param Client $client
     * @return void
     */
    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    /**
     * @param string $settingsKey
     * @return void
     */
    public function setSettingsKey(string $settingsKey): void
    {
        $this->settingsKey = $settingsKey;
    }

    /**
     * Prepends configured preset to the base index name
     *
     * @return string
     */
    private function prefixName(): string
    {
        $indexConfiguration = $this->getConfiguration();
        if (!isset($indexConfiguration['prefix']) || empty($indexConfiguration['prefix'])) {
            return $this->name;
        }

        return $indexConfiguration['prefix'] . '-' . $this->name;
    }

    private function encodeRequestBody(array $content): string
    {
        if ($content === []) {
            return '';
        }

        return json_encode($content);
    }
}
