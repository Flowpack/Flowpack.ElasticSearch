<?php
namespace Flowpack\ElasticSearch\Indexer\Object\Signal;

/*
 * This file is part of the Flowpack.ElasticSearch package.
 *
 * (c) Contributors of the Flowpack Team - flowpack.org
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class SignalEmitter
{
    /**
     * @Flow\Signal
     * @param object $object The object that has been updated
     * @return void
     */
    public function emitObjectUpdated($object)
    {
    }

    /**
     * @Flow\Signal
     * @param object $object The object that has been updated
     * @return void
     */
    public function emitObjectPersisted($object)
    {
    }

    /**
     * @Flow\Signal
     * @param object $object The object that has been updated
     * @return void
     */
    public function emitObjectRemoved($object)
    {
    }
}
