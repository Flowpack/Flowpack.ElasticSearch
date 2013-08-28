<?php
namespace TYPO3\ElasticSearch\Indexer\Object\Signal\Doctrine;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.ElasticSearch".   *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * @Flow\Scope("singleton")
 */
class EmitterAdapter implements \TYPO3\ElasticSearch\Indexer\Object\Signal\EmitterAdapterInterface {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\ElasticSearch\Indexer\Object\Signal\SignalEmitter
	 */
	protected $signalEmitter;

	/**
	 * @param \Doctrine\ORM\Event\LifecycleEventArgs $eventArguments
	 * @return void
	 */
	public function postUpdate(LifecycleEventArgs $eventArguments) {
		$this->signalEmitter->emitObjectUpdated($eventArguments->getEntity());
	}

	/**
	 * @param \Doctrine\ORM\Event\LifecycleEventArgs $eventArguments
	 * @return void
	 */
	public function postPersist(LifecycleEventArgs $eventArguments) {
		$this->signalEmitter->emitObjectPersisted($eventArguments->getEntity());
	}

	/**
	 * @param \Doctrine\ORM\Event\LifecycleEventArgs $eventArguments
	 * @return void
	 */
	public function postRemove(LifecycleEventArgs $eventArguments) {
		$this->signalEmitter->emitObjectRemoved($eventArguments->getEntity());
	}

}

?>