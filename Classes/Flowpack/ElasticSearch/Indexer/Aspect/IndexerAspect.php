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

use TYPO3\Flow\Annotations as Flow;

/**
 * Indexing aspect
 *
 * @Flow\Aspect
 */
class IndexerAspect
{
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
    public function updateObjectToIndex(\TYPO3\Flow\Aop\JoinPointInterface $joinPoint)
    {
        $arguments = $joinPoint->getMethodArguments();
        $object = reset($arguments);
        $this->objectIndexer->indexObject($object);
    }

    /**
     * @Flow\AfterReturning("setting(Flowpack.ElasticSearch.realtimeIndexing.enabled) && within(TYPO3\Flow\Persistence\PersistenceManagerInterface) && method(public .+->(remove)())")
     * @param \TYPO3\Flow\Aop\JoinPointInterface $joinPoint
     * @return string
     */
    public function removeObjectFromIndex(\TYPO3\Flow\Aop\JoinPointInterface $joinPoint)
    {
        $arguments = $joinPoint->getMethodArguments();
        $object = reset($arguments);
        $this->objectIndexer->removeObject($object);
    }
}
