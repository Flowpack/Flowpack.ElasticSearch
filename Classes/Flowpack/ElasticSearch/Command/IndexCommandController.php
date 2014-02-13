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

use Flowpack\ElasticSearch\Domain\Model\Client;
use Flowpack\ElasticSearch\Domain\Model\Index;
use Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Error\Error;
use TYPO3\Flow\Error\Result as ErrorResult;
use TYPO3\Flow\Exception;

/**
 * Provides CLI features for index handling
 *
 * @Flow\Scope("singleton")
 */
class IndexCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @Flow\Inject
	 * @var \Flowpack\ElasticSearch\Domain\Factory\ClientFactory
	 */
	protected $clientFactory;

	/**
	 * @Flow\Inject
	 * @var \Flowpack\ElasticSearch\Indexer\Object\IndexInformer
	 */
	protected $indexInformer;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @Flow\Inject
	 * @var \Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer
	 */
	protected $objectIndexer;

	/**
	 * Creat a new index in ElasticSearch
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
	 * Shows the status of the current mapping
	 *
	 * @param boolean $conductUpdate Set to TRUE to conduct the required corrections
	 * @param string $clientName The client name to use
	 */
	public function statusCommand($conductUpdate = FALSE, $clientName = NULL) {
		$result = new ErrorResult();

		$client = $this->clientFactory->create($clientName);
		$classesAndAnnotations = $this->indexInformer->getClassesAndAnnotations();
		/** @var $annotation \Flowpack\ElasticSearch\Annotations\Indexable */
		foreach ($classesAndAnnotations as $className => $annotation) {
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
				$states = $this->getModificationsNeededStatesAndIdentifiers($client, $className);
				if ($conductUpdate) {
					$inserted = 0;
					$updated = 0;
					foreach ($states[ObjectIndexer::ACTION_TYPE_CREATE] AS $identifier) {
						try {
							$this->objectIndexer->indexObject($this->persistenceManager->getObjectByIdentifier($identifier, $className));
							$inserted++;
						} catch (\Exception $exception) {
							$result->forProperty($className)->addError(new Error('An error occured while trying to add an object to the ElasticSearch backend. The exception message was "%s".', 1340356330, array($exception->getMessage())));
						}
					}
					foreach ($states[ObjectIndexer::ACTION_TYPE_UPDATE] AS $identifier) {
						try {
							$this->objectIndexer->indexObject($this->persistenceManager->getObjectByIdentifier($identifier, $className));
							$updated++;
						} catch (\Exception $exception) {
							$result->forProperty($className)->addError(new Error('An error occured while trying to update an object to the ElasticSearch backend. The exception message was "%s".', 1340358590, array($exception->getMessage())));
						}
					}
					$this->outputFormatted("Objects inserted: <b>%s</b>", array($inserted), 8);
					$this->outputFormatted("Objects updated: <b>%s</b>", array($updated), 8);
				} else {
					$this->outputFormatted("Modifications needed: <b>create</b> %d, <b>update</b> %d", array(count($states[ObjectIndexer::ACTION_TYPE_CREATE]), count($states[ObjectIndexer::ACTION_TYPE_UPDATE])), 8);
				}
			}
		}

		if ($result->hasErrors()) {
			$this->outputLine();
			$this->outputLine('The following errors occured:');
			/** @var $error \TYPO3\Flow\Error\Error */
			foreach ($result->getFlattenedErrors() as $className => $errors) {
				foreach ($errors as $error) {
					$this->outputLine();
					$this->outputFormatted("<b>\x1b[41mError\x1b[0m</b> for \x1b[33m%s\x1b[0m:", array($className), 8);
					$this->outputFormatted((string)$error, array(), 4);
				}
			}
		}
	}

	/**
	 * @param Client $client
	 * @param string $className
	 *
	 * @return array
	 */
	protected function getModificationsNeededStatesAndIdentifiers(Client $client, $className) {
		$query = $this->persistenceManager->createQueryForType($className);
		$states = array(
			ObjectIndexer::ACTION_TYPE_CREATE => array(),
			ObjectIndexer::ACTION_TYPE_UPDATE => array(),
			ObjectIndexer::ACTION_TYPE_DELETE => array(),
		);
		foreach ($query->execute() as $object) {
			$state = $this->objectIndexer->objectIndexActionRequired($object, $client);
			$states[$state][] = $this->persistenceManager->getIdentifierByObject($object);
		}

		return $states;
	}
}

