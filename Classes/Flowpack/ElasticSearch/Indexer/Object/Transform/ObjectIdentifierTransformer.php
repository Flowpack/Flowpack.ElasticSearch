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

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\PersistenceManagerInterface;

/**
 * @Flow\Scope("singleton")
 */
class ObjectIdentifierTransformer implements TransformerInterface
{
    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * Returns the Elasticsearch type this transform() method returns
     *
     * @return string
     */
    public function getTargetMappingType()
    {
        return 'string';
    }

    /**
     * @param mixed $source
     * @param \Flowpack\ElasticSearch\Annotations\Transform $annotation
     * @return string
     */
    public function transformByAnnotation($source, \Flowpack\ElasticSearch\Annotations\Transform $annotation)
    {
        if ($source != null) {
            return $this->persistenceManager->getIdentifierByObject($source);
        }
        return '';
    }
}
