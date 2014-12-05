<?php
namespace Flowpack\ElasticSearch;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.ElasticSearch".*
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Package\Package as BasePackage;

/**
 * The ElasticSearch Package
 */
class Package extends BasePackage {

	/**
	 * @var \TYPO3\Flow\Configuration\ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * Invokes custom PHP code directly after the package manager has been initialized.
	 *
	 * @param \TYPO3\Flow\Core\Bootstrap $bootstrap The current bootstrap
	 *
	 * @return void
	 */
	public function boot(\TYPO3\Flow\Core\Bootstrap $bootstrap) {
		$dispatcher = $bootstrap->getSignalSlotDispatcher();
		$package = $this;
		$dispatcher->connect('TYPO3\Flow\Core\Booting\Sequence', 'afterInvokeStep', function (\TYPO3\Flow\Core\Booting\Step $step) use ($package, $bootstrap) {
			if ($step->getIdentifier() === 'typo3.flow:persistence') {
				$package->prepareRealtimeIndexing($bootstrap);
			}
		});
	}

	/**
	 * @param \TYPO3\Flow\Core\Bootstrap $bootstrap
	 */
	public function prepareRealtimeIndexing(\TYPO3\Flow\Core\Bootstrap $bootstrap) {
		$this->configurationManager = $bootstrap->getObjectManager()->get('TYPO3\Flow\Configuration\ConfigurationManager');
		$settings = $this->configurationManager->getConfiguration(\TYPO3\Flow\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'TYPO3.TYPO3CR.Search');
		if (isset($settings['realtimeIndexing']['enabled']) && $settings['realtimeIndexing']['enabled'] === TRUE) {
			$bootstrap->getSignalSlotDispatcher()->connect('Flowpack\ElasticSearch\Indexer\Object\Signal\SignalEmitter', 'objectUpdated', 'Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer', 'indexObject');
			$bootstrap->getSignalSlotDispatcher()->connect('Flowpack\ElasticSearch\Indexer\Object\Signal\SignalEmitter', 'objectPersisted', 'Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer', 'indexObject');
			$bootstrap->getSignalSlotDispatcher()->connect('Flowpack\ElasticSearch\Indexer\Object\Signal\SignalEmitter', 'objectRemoved', 'Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer', 'removeObject');
		}
	}
}

