<?php
namespace Flowpack\ElasticSearch\Indexer\Object\Signal\Doctrine;

/*
 * This file is part of the Flowpack.ElasticSearch package.
 *
 * (c) Contributors of the Flowpack Team - flowpack.org
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class EmitterAdapter implements \Flowpack\ElasticSearch\Indexer\Object\Signal\EmitterAdapterInterface
{
    /**
     * @Flow\Inject
     * @var \Flowpack\ElasticSearch\Indexer\Object\Signal\SignalEmitter
     */
    protected $signalEmitter;

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $eventArguments
     * @return void
     */
    public function postUpdate(LifecycleEventArgs $eventArguments)
    {
        $this->signalEmitter->emitObjectUpdated($eventArguments->getEntity());
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $eventArguments
     * @return void
     */
    public function postPersist(LifecycleEventArgs $eventArguments)
    {
        $this->signalEmitter->emitObjectPersisted($eventArguments->getEntity());
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $eventArguments
     * @return void
     */
    public function postRemove(LifecycleEventArgs $eventArguments)
    {
        $this->signalEmitter->emitObjectRemoved($eventArguments->getEntity());
    }

    /**
     * @param PostFlushEventArgs $eventArguments
     * @return void
     */
    public function postFlush(PostFlushEventArgs $eventArguments)
    {
        $this->signalEmitter->emitAllObjectsPersisted();
    }
}
