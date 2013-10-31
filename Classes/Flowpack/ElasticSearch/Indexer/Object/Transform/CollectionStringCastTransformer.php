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
 * @Flow\Scope("singleton")
 */
class CollectionStringCastTransformer implements TransformerInterface {

	/**
	 * Returns the Elasticsearch type this transform() method returns
	 *
	 * @static
	 * @return string
	 */
	public static function getTargetMappingType() {
		return 'array';
	}

	/**
	 * @static
	 */
	public static function transformByAnnotation($source, \Flowpack\ElasticSearch\Annotations\Transform $annotation) {
		$array = array();
		foreach ($source as $item) {
			$array[] = (string)$item;
		}

		return $array;
	}
}

