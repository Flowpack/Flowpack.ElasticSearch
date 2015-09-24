<?php
namespace Flowpack\ElasticSearch\Tests\Functional\Fixtures;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.ElasticSearch".*
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use \TYPO3\Flow\Annotations as Flow;
use \Flowpack\ElasticSearch\Annotations as ElasticSearch;

/**
 * This class contains just one property that has to be flagged as indexable.
 *
 * @Flow\Entity
 * @ElasticSearch\Indexable(indexName="dummyindex", typeName="sampletype")
 */
class JustFewPropertiesToIndex
{
    /**
     * @var string
     * @ElasticSearch\Indexable
     */
    protected $value1;

    /**
     * @var string
     */
    protected $value2;

    /**
     * @var string
     */
    protected $value3;
}
