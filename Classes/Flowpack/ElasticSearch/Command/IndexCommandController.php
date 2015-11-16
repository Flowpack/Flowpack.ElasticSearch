<?php
namespace Flowpack\ElasticSearch\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.ElasticSearch".*
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Flowpack\ElasticSearch\Annotations\Indexable;
use Flowpack\ElasticSearch\Domain\Factory\ClientFactory;
use Flowpack\ElasticSearch\Domain\Model\Client;
use Flowpack\ElasticSearch\Domain\Model\Index;
use Flowpack\ElasticSearch\Indexer\Object\IndexInformer;
use Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer;
use Flowpack\ElasticSearch\Service\IndexerService;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Configuration\ConfigurationManager;
use TYPO3\Flow\Core\Booting\Scripts;
use TYPO3\Flow\Error\Error;
use TYPO3\Flow\Error\Result as ErrorResult;
use TYPO3\Flow\Exception;
use TYPO3\Flow\Log\SystemLoggerInterface;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;
use TYPO3\Flow\Utility\Arrays;

/**
 * Provides CLI features for index handling
 *
 * @Flow\Scope("singleton")
 */
class IndexCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @Flow\Inject
	 * @var ClientFactory
	 */
	protected $clientFactory;

	/**
	 * @Flow\Inject
	 * @var IndexInformer
	 */
	protected $indexInformer;

	/**
	 * @Flow\Inject
	 * @var PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @Flow\Inject
	 * @var ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * @Flow\Inject
	 * @var SystemLoggerInterface
	 */
	protected $systemLogger;

	/**
	 * @Flow\Inject
	 * @var ObjectIndexer
	 */
	protected $objectIndexer;

	/**
	 * Create a new index in ElasticSearch
	 *
	 * @param string $indexName The name of the new index
	 * @param string $clientName The client name to use
	 */
	public function createCommand($indexName, $clientName = NULL) {
		if (!in_array($indexName, $this->indexInformer->getAllIndexNames())) {
			$this->outputFormatted("The index <b>%s</b> is not configured in the current application", array($indexName));
			$this->quit(1);
		}

		$client = $this->clientFactory->create($clientName);
		try {
			$index = new Index($indexName, $client);
			if ($index->exists()) {
				$this->outputFormatted("The index <b>%s</b> exists", array($indexName));
				$this->quit(1);
			}
			$index->create();
			$this->outputFormatted("Index <b>%s</b> created with success", array($indexName));
		} catch (Exception $exception) {
			$this->outputFormatted("Unable to create an index named: <b>%s</b>", array($indexName));
			$this->quit(1);
		}
	}

	/**
	 * Update index settings
	 *
	 * @param string $indexName The name of the new index
	 * @param string $clientName The client name to use
	 */
	public function updateSettingsCommand($indexName, $clientName = NULL) {
		if (!in_array($indexName, $this->indexInformer->getAllIndexNames())) {
			$this->outputFormatted("The index <b>%s</b> is not configured in the current application", array($indexName));
			$this->quit(1);
		}

		$client = $this->clientFactory->create($clientName);
		try {
			$index = new Index($indexName, $client);
			if (!$index->exists()) {
				$this->outputFormatted("The index <b>%s</b> does not exists", array($indexName));
				$this->quit(1);
			}
			$index->updateSettings();
			$this->outputFormatted("Index settings <b>%s</b> updated with success", array($indexName));
		} catch (Exception $exception) {
			$this->outputFormatted("Unable to update settings for <b>%s</b> index", array($indexName));
			$this->quit(1);
		}
	}

	/**
	 * Delete an index in ElasticSearch
	 *
	 * @param string $indexName The name of the index to be removed
	 * @param string $clientName The client name to use
	 */
	public function deleteCommand($indexName, $clientName = NULL) {
		if (!in_array($indexName, $this->indexInformer->getAllIndexNames())) {
			$this->outputFormatted("The index <b>%s</b> is not configured in the current application", array($indexName));
			$this->quit(1);
		}

		$client = $this->clientFactory->create($clientName);
		try {
			$index = new Index($indexName, $client);
			if (!$index->exists()) {
				$this->outputFormatted("The index <b>%s</b> does not exists", array($indexName));
				$this->quit(1);
			}
			$index->delete();
			$this->outputFormatted("Index <b>%s</b> deleted with success", array($indexName));
		} catch (Exception $exception) {
			$this->outputFormatted("Unable to delete an index named: <b>%s</b>", array($indexName));
			$this->quit(1);
		}
	}

	/**
	 * Refresh an index in ElasticSearch
	 *
	 * @param string $indexName The name of the index to be removed
	 * @param string $clientName The client name to use
	 */
	public function refreshCommand($indexName, $clientName = NULL) {
		if (!in_array($indexName, $this->indexInformer->getAllIndexNames())) {
			$this->outputFormatted("The index <b>%s</b> is not configured in the current application", array($indexName));
		}

		$client = $this->clientFactory->create($clientName);
		try {
			$index = new Index($indexName, $client);
			if (!$index->exists()) {
				$this->outputFormatted("The index <b>%s</b> does not exists", array($indexName));
				$this->quit(1);
			}
			$index->refresh();
			$this->outputFormatted("Index <b>%s</b> refreshed with success", array($indexName));
		} catch (Exception $exception) {
			$this->outputFormatted("Unable to refresh an index named: <b>%s</b>", array($indexName));
			$this->quit(1);
		}
	}

	/**
	 * List available document type
	 */
	public function showConfiguredTypesCommand() {
		$classesAndAnnotations = $this->indexInformer->getClassesAndAnnotations();
		$this->outputFormatted("<b>Available document type</b>");
		/** @var $annotation \Flowpack\ElasticSearch\Annotations\Indexable */
		foreach ($classesAndAnnotations as $className => $annotation) {
			$this->outputFormatted("%s", array($className), 4);
		}
	}

	/**
	 * Shows the status of the current mapping
	 *
	 * @param string $object Class name of a domain object. If given, will only work on this single object
	 * @param boolean $conductUpdate Set to TRUE to conduct the required corrections
	 * @param string $clientName The client name to use
	 * @param integer $batchSize The number of record processed per batch
	 */
	public function statusCommand($object = NULL, $conductUpdate = FALSE, $clientName = NULL, $batchSize = 200) {
		$result = new ErrorResult();

		$client = $this->clientFactory->create($clientName);

		$classesAndAnnotations = $this->indexInformer->getClassesAndAnnotations();
		if ($object !== NULL) {
			if (!isset($classesAndAnnotations[$object])) {
				$this->outputFormatted("Error: Object '<b>%s</b>' is not configured correctly, check the Indexable annotation.", array($object));
				$this->quit(1);
			}
			$classesAndAnnotations = array($object => $classesAndAnnotations[$object]);
		}
		array_walk($classesAndAnnotations, function (Indexable $annotation, $className) use ($result, $client, $conductUpdate, $batchSize, $clientName) {
			$this->outputFormatted("Object \x1b[33m%s\x1b[0m", array($className), 4);
			$this->outputFormatted("Index <b>%s</b> Type <b>%s</b>", array($annotation->indexName, $annotation->typeName), 8);
			$count = $client->findIndex($annotation->indexName)->findType($annotation->typeName)->count();
			if ($count === NULL) {
				$result->forProperty($className)->addError(new Error('ElasticSearch was unable to retrieve a count for the type "%s" at index "%s". Probably these don\' exist.', 1340289921, array($annotation->typeName, $annotation->indexName)));
			}
			$this->outputFormatted("Documents in Search: <b>%s</b>", array($count !== NULL ? $count : "\x1b[41mError\x1b[0m"), 8);

			try {
				$count = $this->persistenceManager->createQueryForType($className)->count();
			} catch (\Exception $exception) {
				$count = NULL;
				$result->forProperty($className)->addError(new Error('The persistence backend was unable to retrieve a count for the type "%s". The exception message was "%s".', 1340290088, array($className, $exception->getMessage())));
			}
			$this->outputFormatted("Documents in Persistence: <b>%s</b>", array($count !== NULL ? $count : "\x1b[41mError\x1b[0m"), 8);
			if (!$result->forProperty($className)->hasErrors()) {
				$numberOfBatch = ceil($count / $batchSize);
				$settings = $this->configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS);
				$batchIdentifier = uniqid();
				$startTime = microtime(TRUE);
				$this->systemLogger->log(sprintf('Update process for "%s" started, batch: %s, count: %s, numberOfBatch: %s, batchSize: %s', $className, $batchIdentifier, $count, $numberOfBatch, $batchSize), LOG_INFO, NULL, 'ElasticSearch');
				for ($i = 0; $i < $numberOfBatch; $i++) {
					$offset = $i * $batchSize;
					$arguments = array(
						'className' => $className,
						'batchSize' => $batchSize,
						'offset' => $offset,
						'batchIdentifier' => $batchIdentifier,
						'conductUpdate' => $conductUpdate
					);
					if ($clientName !== NULL) {
						$arguments['clientName'] = $clientName;
					}

					if (!Scripts::executeCommand('flowpack.elasticsearch:index:statusInternal', Arrays::getValueByPath($settings, 'TYPO3.Flow'), TRUE, $arguments)) {
						$this->systemLogger->log(sprintf('Unable to conduct update for "%s" ended, batch: %s, duration: %s', $className, $batchIdentifier, microtime(TRUE) - $startTime), LOG_CRIT, NULL, 'ElasticSearch');
						$this->outputFormatted("Error: Unable to conduct update, check your logs for error details.", array(), 8);
						$this->quit(1);
					}
				}
				$this->systemLogger->log(sprintf('Update process for "%s" ended, batch: %s, duration: %s', $className, $batchIdentifier, microtime(TRUE) - $startTime), LOG_INFO, NULL, 'ElasticSearch');

			}
		});
	}

	/**
	 * @param string $className Class name of a domain object. If given, will only work on this single object
	 * @param integer $batchSize The number of record processed per batch
	 * @param integer $offset Batch offset
	 * @param string $batchIdentifier Batch identifier
	 * @param string $clientName The client name to use
	 * @param boolean $conductUpdate Set to TRUE to conduct the required corrections
	 * @Flow\Internal
	 */
	public function statusInternalCommand($className, $batchSize, $offset, $batchIdentifier, $clientName = NULL, $conductUpdate = FALSE) {
		$states = $this->indexInformer->getModificationsNeededStatesAndObjects($className, $batchSize, $offset, $this->clientFactory->create($clientName));
		$created = $updated = 0;
		$run = $offset / $batchSize;
		if ($conductUpdate) {
			foreach ($states[ObjectIndexer::ACTION_TYPE_CREATE] AS $object) {
				try {
					$this->objectIndexer->indexObject($object);
					$created++;
				} catch (\Exception $exception) {
					$this->systemLogger->log(sprintf('An error occurred while trying to add an object of type "%s" to the ElasticSearch backend. The exception message was "%s" (%s), run: %s, batch: %s', $className, $exception->getCode(), $exception->getMessage(), $run, $batchIdentifier), LOG_ERR, NULL, 'ElasticSearch');
				}
			}
			foreach ($states[ObjectIndexer::ACTION_TYPE_UPDATE] AS $object) {
				try {
					$this->objectIndexer->indexObject($object);
					$updated++;
				} catch (\Exception $exception) {
					$this->systemLogger->log(sprintf('An error occurred while trying to update an object of type "%s" to the ElasticSearch backend. The exception message was "%s" (%s), run: %s, batch: %s', $className, $exception->getCode(), $exception->getMessage(), $run, $batchIdentifier), LOG_ERR, NULL, 'ElasticSearch');
				}
			}
			$this->systemLogger->log(sprintf('Batch update process for "%s", objects created: %s, objects updated: %s, run: %s, batch: %s', $className, $created, $updated, $run, $batchIdentifier), LOG_INFO, NULL, 'ElasticSearch');
		}
	}
}