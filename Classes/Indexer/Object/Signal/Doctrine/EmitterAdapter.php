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
use Flowpack\ElasticSearch\Indexer\Object\Signal\EmitterAdapterInterface;
use Flowpack\ElasticSearch\Indexer\Object\Signal\SignalEmitter;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class EmitterAdapter implements EmitterAdapterInterface
{
    /**
     * @Flow\Inject
     * @var SignalEmitter
     */
    protected $signalEmitter;

    /**
     * @param LifecycleEventArgs $eventArguments
     * @return void
     */
    public function postUpdate(LifecycleEventArgs $eventArguments)
    {
        $this->signalEmitter->emitObjectUpdated($eventArguments->getEntity());
    }

    /**
     * @param LifecycleEventArgs $eventArguments
     * @return void
     */
    public function postPersist(LifecycleEventArgs $eventArguments)
    {
        $this->signalEmitter->emitObjectPersisted($eventArguments->getEntity());
    }

    /**
     * @param LifecycleEventArgs $eventArguments
     * @return void
     */
    public function postRemove(LifecycleEventArgs $eventArguments)
    {
        $this->signalEmitter->emitObjectRemoved($eventArguments->getEntity());
    }
}
