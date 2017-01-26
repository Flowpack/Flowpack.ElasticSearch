<?php
namespace Flowpack\ElasticSearch;

/*
 * This file is part of the Flowpack.ElasticSearch package.
 *
 * (c) Contributors of the Flowpack Team - flowpack.org
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer;
use Flowpack\ElasticSearch\Indexer\Object\Signal\SignalEmitter;
use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Flow\Core\Booting\Sequence;
use Neos\Flow\Core\Booting\Step;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Package\Package as BasePackage;

/**
 * The ElasticSearch Package
 */
class Package extends BasePackage
{
    /**
     * @var ConfigurationManager
     */
    protected $configurationManager;

    /**
     * Invokes custom PHP code directly after the package manager has been initialized.
     *
     * @param Bootstrap $bootstrap The current bootstrap
     *
     * @return void
     */
    public function boot(Bootstrap $bootstrap)
    {
        $dispatcher = $bootstrap->getSignalSlotDispatcher();
        $package = $this;
        $dispatcher->connect(Sequence::class, 'afterInvokeStep', function (Step $step) use ($package, $bootstrap) {
            if ($step->getIdentifier() === 'neos.flow:objectmanagement:runtime') {
                $package->prepareRealtimeIndexing($bootstrap);
            }
        });
    }

    /**
     * @param Bootstrap $bootstrap
     *
     * @return void
     */
    public function prepareRealtimeIndexing(Bootstrap $bootstrap)
    {
        $this->configurationManager = $bootstrap->getObjectManager()->get(ConfigurationManager::class);
        $settings = $this->configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, $this->getPackageKey());

        if (isset($settings['realtimeIndexing']['enabled']) && $settings['realtimeIndexing']['enabled'] === true) {
            $bootstrap->getSignalSlotDispatcher()->connect(SignalEmitter::class, 'objectUpdated', ObjectIndexer::class, 'indexObject');
            $bootstrap->getSignalSlotDispatcher()->connect(SignalEmitter::class, 'objectPersisted', ObjectIndexer::class, 'indexObject');
            $bootstrap->getSignalSlotDispatcher()->connect(SignalEmitter::class, 'objectRemoved', ObjectIndexer::class, 'removeObject');
        }
    }
}
