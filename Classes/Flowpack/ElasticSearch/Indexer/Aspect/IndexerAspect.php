<?php
namespace Flowpack\ElasticSearch\Indexer\Aspect;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.ElasticSearch".*
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * Indexing aspect
 *
 * @Flow\Aspect
 */
class IndexerAspect {

	/**
	 * @Flow\Inject
	 * @var \Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer
	 */
	protected $objectIndexer;

	/**
	 * @Flow\AfterReturning("setting(Flowpack.ElasticSearch.realtimeIndexing.enabled) && within(TYPO3\Flow\Persistence\PersistenceManagerInterface) && method(public .+->(add|update)())")
	 * @param \TYPO3\Flow\Aop\JoinPointInterface $joinPoint
	 * @return string
	 */
	public function updateObjectToIndex(\TYPO3\Flow\Aop\JoinPointInterface $joinPoint) {
		$arguments = $joinPoint->getMethodArguments();
		$object = reset($arguments);
		$this->objectIndexer->indexObject($object);
	}

	/**
	 * @Flow\AfterReturning("setting(Flowpack.ElasticSearch.realtimeIndexing.enabled) && within(TYPO3\Flow\Persistence\PersistenceManagerInterface) && method(public .+->(remove)())")
	 * @param \TYPO3\Flow\Aop\JoinPointInterface $joinPoint
	 * @return string
	 */
	public function removeObjectFromIndex(\TYPO3\Flow\Aop\JoinPointInterface $joinPoint) {
		$arguments = $joinPoint->getMethodArguments();
		$object = reset($arguments);
		$this->objectIndexer->removeObject($object);
	}
}

?>