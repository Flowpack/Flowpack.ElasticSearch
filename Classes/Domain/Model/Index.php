<?php
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
     * @var array
     */
    protected $settings;

    /**
     * @param string $name
     * @param Client $client $client
     * @throws ElasticSearchException
     */
    public function __construct($name, Client $client = null)
    {
        $name = trim($name);
        if (empty($name) || substr($name, 0, 1) === '_') {
            throw new ElasticSearchException('The provided index name "' . $name . '" must not be empty and not start with an underscore.', 1340187948);
        } elseif ($name !== strtolower($name)) {
            throw new ElasticSearchException('The provided index name "' . $name . '" must be all lowercase.', 1340187956);
        }
        $this->name = $name;
        $this->settingsKey = $name;
        $this->client = $client;
    }

    /**
     * Inject the settings
     *
     * @param array $settings
     * @return void
     */
    public function injectSettings(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @param string $typeName
     * @return AbstractType
     */
    public function findType($typeName)
    {
        return new GenericType($this, $typeName);
    }

    /**
     * @param array <AbstractType> $types
     * @return TypeGroup
     */
    public function findTypeGroup(array $types)
    {
        return new TypeGroup($this, $types);
    }

    /**
     * @return boolean
     */
    public function exists()
    {
        $response = $this->request('HEAD');

        return $response->getStatusCode() === 200;
    }

    /**
     * @param string $method
     * @param string $path
     * @param array $arguments
     * @param string $content
     * @param boolean $prefixIndex
     * @return Response
     * @throws ElasticSearchException
     */
    public function request($method, $path = null, array $arguments = [], $content = null, $prefixIndex = true)
    {
        if ($this->client === null) {
            throw new Exception('The client of the index "' . $this->name . '" is not set, hence no requests can be done.');
        }
        $path = ($path ? trim($path) : '');
        if ($prefixIndex === true) {
            $path = '/' . $this->name . $path;
        } else {
            $path = '/' . $path;
        }

        return $this->client->request($method, $path, $arguments, $content);
    }

    /**
     * @param array $arguments
     * @return void
     */
    public function create(array $arguments = [])
    {
        $this->request('PUT', null, $arguments, json_encode($this->getSettings()));
    }

    /**
     * @return array|null
     */
    protected function getSettings()
    {
        if ($this->client instanceof Client) {
            $path = 'indexes.' . $this->client->getBundle() . '.' . $this->settingsKey;
        } else {
            $path = 'indexes.default' . '.' . $this->settingsKey;
        }
        $settings = Arrays::getValueByPath($this->settings, $path);

        return $settings !== null ? $this->dynamicIndexSettingService->process($settings, $path, $this->getName()) : $settings;
    }

    /**
     * @return void
     */
    public function updateSettings()
    {
        $settings = $this->getSettings();
        $updatableSettings = [];
        foreach (static::$updatableSettings as $settingPath) {
            $setting = Arrays::getValueByPath($settings, $settingPath);
            if ($setting !== null) {
                $updatableSettings = Arrays::setValueByPath($updatableSettings, $settingPath, $setting);
            }
        }
        $this->request('PUT', '/_settings', [], json_encode($updatableSettings));
    }

    /**
     * @return void
     */
    public function delete()
    {
        $this->request('DELETE');
    }

    /**
     * Refresh the index
     *
     * @return void
     */
    public function refresh()
    {
        $this->request('POST', '/_refresh');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param Client $client
     * @return void
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * @param string $settingsKey
     * @return void
     */
    public function setSettingsKey($settingsKey)
    {
        $this->settingsKey = $settingsKey;
    }
}
