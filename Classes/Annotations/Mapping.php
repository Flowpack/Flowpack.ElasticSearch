<?php
namespace TYPO3\ElasticSearch\Annotations;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.ElasticSearch".        *
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
 * @DoctrineAnnotation\Target("PROPERTY")
 */
final class Mapping {

	/**
	 * The name of the field that will be stored in the index. Defaults to the property/field name.
	 * @var string
	 *
	 * @see http://www.elasticsearch.org/guide/reference/mapping/core-types.html
	 */
	public $index_name;

	/**
	 * Defaults to `no`.
	 * @var string
	 *
	 * @see http://www.elasticsearch.org/guide/reference/mapping/core-types.html
	 */
	public $store;

	/**
	 * Defaults to `analyzed`.
	 * @var string
	 *
	 * @see http://www.elasticsearch.org/guide/reference/mapping/core-types.html
	 */
	public $index;

	/**
	 * The boost value. Defaults to `1.0`.
	 * @var float
	 *
	 * @see http://www.elasticsearch.org/guide/reference/mapping/boost-field.html
	 */
	public $boost;

	/**
	 * Possible values are `no`, `yes`, `with_offsets`, `with_positions`, `with_positions_offsets`. Defaults to `no`.
	 * @var string
	 */
	public $term_vector;

	/**
	 * The date format. Defaults to `dateOptionalTime`.
	 * @var string
	 *
	 * @see http://www.elasticsearch.org/guide/reference/mapping/date-format.html
	 */
	public $format;

	/**
	 * Returns this class's properties as type/value array in order to directly use it for mapping information
	 */
	public function getPropertiesArray() {
		return get_object_vars($this);
	}
}

?>