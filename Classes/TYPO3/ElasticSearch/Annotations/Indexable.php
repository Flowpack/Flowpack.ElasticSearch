<?php
namespace TYPO3\ElasticSearch\Annotations;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.ElasticSearch".   *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\Common\Annotations\Annotation as DoctrineAnnotation;

/**
 * @Annotation
 * @DoctrineAnnotation\Target({"CLASS", "PROPERTY"})
 */
final class Indexable {

	/**
	 * The name of the index this object has to be stored to, if target is CLASS
	 * @var string
	 */
	public $indexName;

	/**
	 * The type this object has to be stored as, if target is CLASS
	 * @var string
	 */
	public $typeName;
}

?>