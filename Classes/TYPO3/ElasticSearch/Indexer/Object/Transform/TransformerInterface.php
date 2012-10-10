<?php
namespace TYPO3\ElasticSearch\Indexer\Object\Transform;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.ElasticSearch".   *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use \TYPO3\Flow\Annotations as Flow;

/**
 */
interface TransformerInterface {

	/**
	 * Returns the Elasticsearch type this transform() method returns
	 * @static
	 * @abstract
	 * @return string
	 */
	public static function getTargetMappingType();

	/**
	 * @static
	 */
	public static function transformByAnnotation($source, \TYPO3\ElasticSearch\Annotations\Transform $annotation);


}

?>