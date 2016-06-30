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
 * @Flow\Scope("singleton")
 */
class DateTransformer implements TransformerInterface
{
    /**
     * Returns the Elasticsearch type this transform() method returns
     *
     * @return string
     */
    public function getTargetMappingType()
    {
        return 'date';
    }

    /**
     * @param \DateTime $source
     * @param \Flowpack\ElasticSearch\Annotations\Transform $annotation
     * @return string
     */
    public function transformByAnnotation($source, \Flowpack\ElasticSearch\Annotations\Transform $annotation)
    {
        return $source->format($annotation->options['format'] ?: 'Y-m-d');
    }
}
