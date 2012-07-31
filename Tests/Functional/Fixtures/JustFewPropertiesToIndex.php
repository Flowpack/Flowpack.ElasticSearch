<?php
namespace TYPO3\ElasticSearch\Tests\Functional\Fixtures;

/*                                                                        *
 * This script belongs to the FLOW3-package "TYPO3.ElasticSearch".        *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use \TYPO3\FLOW3\Annotations as FLOW3;
use \TYPO3\ElasticSearch\Annotations as ElasticSearch;

/**
 * This class contains just one property that has to be flagged as indexable.
 *
 * @FLOW3\Entity
 * @ElasticSearch\Indexable(indexName="dummyindex", typeName="sampletype")
 */
class JustFewPropertiesToIndex {

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

?>