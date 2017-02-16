<?php
namespace Flowpack\ElasticSearch\Indexer\Aspect;

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
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Aop\JoinPointInterface;

/**
 * Indexing aspect
 *
 * @Flow\Aspect
 */
class IndexerAspect
{
    /**
     * @Flow\Inject
     * @var ObjectIndexer
     */
    protected $objectIndexer;

    /**
     * @Flow\AfterReturning("setting(Flowpack.ElasticSearch.realtimeIndexing.enabled) && within(Neos\Flow\Persistence\PersistenceManagerInterface) && method(public .+->(add|update)())")
     * @param JoinPointInterface $joinPoint
     * @return string
     */
    public function updateObjectToIndex(JoinPointInterface $joinPoint)
    {
        $arguments = $joinPoint->getMethodArguments();
        $object = reset($arguments);
        $this->objectIndexer->indexObject($object);
    }

    /**
     * @Flow\AfterReturning("setting(Flowpack.ElasticSearch.realtimeIndexing.enabled) && within(Neos\Flow\Persistence\PersistenceManagerInterface) && method(public .+->(remove)())")
     * @param JoinPointInterface $joinPoint
     * @return string
     */
    public function removeObjectFromIndex(JoinPointInterface $joinPoint)
    {
        $arguments = $joinPoint->getMethodArguments();
        $object = reset($arguments);
        $this->objectIndexer->removeObject($object);
    }
}
