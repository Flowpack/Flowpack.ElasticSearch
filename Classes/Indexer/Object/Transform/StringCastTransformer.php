<?php
namespace TYPO3\ElasticSearch\Indexer\Object\Transform;

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

/**
 * @FLOW3\Scope("singleton")
 */
class StringCastTransformer implements TransformerInterface {

	/**
	 * Returns the Elasticsearch type this transform() method returns
	 *
	 * @static
	 * @return string
	 */
	public static function getTargetMappingType() {
		return 'string';
	}

	/**
	 * @static
	 */
	public static function transformByAnnotation($source, \TYPO3\ElasticSearch\Annotations\Transform $annotation) {
		return (string)$source;
	}
}

?>