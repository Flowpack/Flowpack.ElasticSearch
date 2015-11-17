<?php
namespace Flowpack\ElasticSearch\Indexer\Object\Transform;

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
 */
interface TransformerInterface
{
    /**
     * Returns the Elasticsearch type this transform() method returns
     *
     * @abstract
     * @return string
     */
    public function getTargetMappingType();

    /**
     * @param mixed $source
     * @param \Flowpack\ElasticSearch\Annotations\Transform $annotation
     * @return mixed
     */
    public function transformByAnnotation($source, \Flowpack\ElasticSearch\Annotations\Transform $annotation);
}
