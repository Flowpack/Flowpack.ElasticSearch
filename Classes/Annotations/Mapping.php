<?php
namespace Flowpack\ElasticSearch\Annotations;

/*
 * This file is part of the Flowpack.ElasticSearch package.
 *
 * (c) Contributors of the Flowpack Team - flowpack.org
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\Common\Annotations\Annotation as DoctrineAnnotation;

/**
 * @Annotation
 * @DoctrineAnnotation\Target("PROPERTY")
 */
final class Mapping
{
    /**
     * The name of the field that will be stored in the index.
     * Defaults to the property/field name.
     *
     * @var string
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping-params.html
     */
    public $index_name;

    /**
     * Set to yes the store actual field in the index, no to not store it.
     * Defaults to `no` (note, the JSON document itself is stored, and it can be retrieved from it).
     *
     * @var string
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping-params.html
     */
    public $store;

    /**
     * Set to analyzed for the field to be indexed and searchable after being broken down into token using an analyzer.
     * not_analyzed means that its still searchable, but does not go through any analysis process or broken down into tokens.
     * no means that it won’t be searchable at all (as an individual field; it may still be included in _all).
     * Defaults to analyzed.
     *
     * @var string
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping-params.html
     */
    public $index;

    /**
     * Possible values are `no`, `yes`, `with_offsets`, `with_positions`, `with_positions_offsets`.
     * Defaults to `no`.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping-params.html
     * @var string
     */
    public $term_vector;

    /**
     * The boost value. Defaults to `1.0`.
     *
     * @var float
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.5/mapping-boost.html
     */
    public $boost;

    /**
     * The analyzer used to analyze the text contents when analyzed during indexing and when searching using a query string.
     * Defaults to the globally configured analyzer.
     *
     * @var string
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping-params.html
     */
    public $analyzer;

    /**
     * The normalizer used to normalizer the content of keyword type fields during indexing.
     *
     * @var string
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/normalizer.html
     */
    public $normalizer;

    /**
     * The analyzer used to analyze the text contents only when searching using a query string.
     *
     * @var string
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-analyzer.html
     */
	public $search_analyzer;
    
    /**
     * The type to use for this
     * Defaults to the property/field type.
     *
     * @var string
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping-types.html
     */
    public $type;

    /**
     * The date format.
     * Defaults to `dateOptionalTime`.
     *
     * @var string
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping-date-format.html
     */
    public $format;

    /**
     * @var array
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping-types.html#types-multi-fields
     */
    public $fields;

    /**
     * @var array
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/properties.html
     */
    public $properties;

    /**
     * @var bool
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/text.html#fielddata-mapping-param
     */
    public $fielddata;

    /**
     * Returns this class's properties as type/value array in order to directly use it for mapping information
     *
     * @return array
     */
    public function getPropertiesArray()
    {
        $properties = get_object_vars($this);
        unset($properties['fields']);

        return $properties;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }
}
