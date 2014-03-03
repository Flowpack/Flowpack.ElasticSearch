<?php
namespace Flowpack\ElasticSearch\Annotations;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.ElasticSearch".*
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
	 * The name of the field that will be stored in the index.
	 * Defaults to the property/field name.
	 *
	 * @var string
	 * @see http://www.elasticsearch.org/guide/reference/mapping/core-types.html
	 */
	public $index_name;

	/**
	 * Set to yes the store actual field in the index, no to not store it.
	 * Defaults to `no` (note, the JSON document itself is stored, and it can be retrieved from it).
	 *
	 * @var string
	 * @see http://www.elasticsearch.org/guide/reference/mapping/core-types.html
	 */
	public $store;

	/**
	 * Set to analyzed for the field to be indexed and searchable after being broken down into token using an analyzer.
	 * not_analyzed means that its still searchable, but does not go through any analysis process or broken down into tokens.
	 * no means that it won’t be searchable at all (as an individual field; it may still be included in _all).
	 * Defaults to analyzed.
	 *
	 * @var string
	 * @see http://www.elasticsearch.org/guide/reference/mapping/core-types.html
	 */
	public $index;

	/**
	 * Possible values are `no`, `yes`, `with_offsets`, `with_positions`, `with_positions_offsets`.
	 * Defaults to `no`.
	 *
	 * @see http://www.elasticsearch.org/guide/reference/mapping/core-types.html
	 * @var string
	 */
	public $term_vector;

	/**
	 * The boost value. Defaults to `1.0`.
	 *
	 * @var float
	 * @see http://www.elasticsearch.org/guide/reference/mapping/boost-field.html
	 */
	public $boost;

	/**
	 * The analyzer used to analyze the text contents when analyzed during indexing and when searching using a query string.
	 * Defaults to the globally configured analyzer.
	 *
	 * @var string
	 * @see http://www.elasticsearch.org/guide/reference/mapping/core-types.html
	 */
	public $analyzer;

	/**
	 * The type to use for this
	 * Defaults to the property/field type.
	 *
	 * @var string
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/mapping-types.html
	 */
	public $type;

	/**
	 * The date format.
	 * Defaults to `dateOptionalTime`.
	 *
	 * @var string
	 * @see http://www.elasticsearch.org/guide/reference/mapping/date-format.html
	 */
	public $format;

	/**
	 * @var array
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/1.x/_multi_fields.html
	 */
	public $fields;

	/**
	 * Returns this class's properties as type/value array in order to directly use it for mapping information
	 */
	public function getPropertiesArray() {
		$properties = get_object_vars($this);
		unset($properties['fields']);
		return $properties;
	}

	/**
	 * @return array
	 */
	public function getFields() {
		return $this->fields;
	}
}