<?php
namespace Flowpack\ElasticSearch\Indexer\Object\Transform;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.ElasticSearch".*
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 */
interface TargetMappingInterface {

	/**
	 * Returns the Elasticsearch property mapping
	 *
	 * @return array
	 */
	public function getTargetMapping();
}

