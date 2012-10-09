<?php
namespace TYPO3\ElasticSearch;

/*                                                                        *
 * This script belongs to the FLOW3-package "ElasticSearch".              *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use \TYPO3\FLOW3\Package\Package as BasePackage;

/**
 * The ElasticSearch Package
 */
class Package extends BasePackage {

	/**
	 * @var \TYPO3\FLOW3\Configuration\ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * Invokes custom PHP code directly after the package manager has been initialized.
	 *
	 * @param \TYPO3\FLOW3\Core\Bootstrap $bootstrap The current bootstrap
	 *
	 * @return void
	 */
	public function boot(\TYPO3\FLOW3\Core\Bootstrap $bootstrap) {
		$dispatcher = $bootstrap->getSignalSlotDispatcher();
		$dispatcher->connect('TYPO3\FLOW3\Core\Booting\Sequence', 'afterInvokeStep', function(\TYPO3\FLOW3\Core\Booting\Step $step) use ($bootstrap) {
			if ($step->getIdentifier() === 'typo3.flow3:persistence') {
				$this->prepareRealtimeIndexing($bootstrap);
			}
		});
	}

	/**
	 * @param \TYPO3\FLOW3\Core\Bootstrap $bootstrap
	 */
	protected function prepareRealtimeIndexing(\TYPO3\FLOW3\Core\Bootstrap $bootstrap) {
		$this->configurationManager = $bootstrap->getObjectManager()->get('TYPO3\FLOW3\Configuration\ConfigurationManager');
		$settings = $this->configurationManager->getConfiguration(\TYPO3\FLOW3\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, $this->getPackageKey());
		if (isset($settings['realtimeIndexing']['enabled']) && $settings['realtimeIndexing']['enabled'] === TRUE) {
			$bootstrap->getObjectManager()->get('TYPO3\ElasticSearch\Indexer\Object\Signal\EmitterAdapterInterface');
			$bootstrap->getSignalSlotDispatcher()->connect('TYPO3\ElasticSearch\Indexer\Object\Signal\SignalEmitter', 'objectUpdated', 'TYPO3\ElasticSearch\Indexer\Object\ObjectIndexer', 'indexObject');
			$bootstrap->getSignalSlotDispatcher()->connect('TYPO3\ElasticSearch\Indexer\Object\Signal\SignalEmitter', 'objectPersisted', 'TYPO3\ElasticSearch\Indexer\Object\ObjectIndexer', 'indexObject');
			$bootstrap->getSignalSlotDispatcher()->connect('TYPO3\ElasticSearch\Indexer\Object\Signal\SignalEmitter', 'objectRemoved', 'TYPO3\ElasticSearch\Indexer\Object\ObjectIndexer', 'removeObject');
		}
	}
}

?>