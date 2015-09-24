<?php
namespace Flowpack\ElasticSearch\Domain\Model;

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
use TYPO3\Flow\Utility\Arrays;

/**
 * Reflects a Mapping of Elasticsearch
 */
class Mapping
{
    /**
     * @var \Flowpack\ElasticSearch\Domain\Model\AbstractType
     */
    protected $type;

    /**
     * @var array
     */
    protected $properties = array();

    /**
     * see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/mapping-root-object-type.html#_dynamic_templates
     * @var array
     */
    protected $dynamicTemplates = array();

    /**
     * This is the full / raw ElasticSearch mapping which is merged with the properties and dynamicTemplates.
     *
     * It can be used to specify arbitrary ElasticSearch mapping options, like f.e. configuring the _all field.
     *
     * @var array
     */
    protected $fullMapping = array();

    /**
     * @param \Flowpack\ElasticSearch\Domain\Model\AbstractType $type
     */
    public function __construct(AbstractType $type)
    {
        $this->type = $type;
    }

    /**
     * Gets a property setting by its path
     *
     * @param array|string $path
     * @return mixed
     */
    public function getPropertyByPath($path)
    {
        return \TYPO3\Flow\Utility\Arrays::getValueByPath($this->properties, $path);
    }

    /**
     * Gets a property setting by its path
     *
     * @param array|string $path
     * @param string $value
     * @return void
     */
    public function setPropertyByPath($path, $value)
    {
        $this->properties = \TYPO3\Flow\Utility\Arrays::setValueByPath($this->properties, $path, $value);
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @return \Flowpack\ElasticSearch\Domain\Model\AbstractType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Return the mapping which would be sent to the server as array
     *
     * @return array
     */
    public function asArray()
    {
        return array($this->type->getName() => Arrays::arrayMergeRecursiveOverrule(array(
            'dynamic_templates' => $this->getDynamicTemplates(),
            'properties' => $this->getProperties()
        ), $this->fullMapping));
    }

    /**
     * Sets this mapping to the server
     */
    public function apply()
    {
        $content = json_encode($this->asArray());
        $response = $this->type->request('PUT', '/_mapping', array(), $content);

        return $response;
    }

    /**
     * @return array
     */
    public function getDynamicTemplates()
    {
        return $this->dynamicTemplates;
    }

    /**
     * Dynamic templates allow to define mapping templates
     *
     * @param $dynamicTemplateName
     * @param array $mappingConfiguration
     */
    public function addDynamicTemplate($dynamicTemplateName, array $mappingConfiguration)
    {
        $this->dynamicTemplates[] = array(
            $dynamicTemplateName => $mappingConfiguration
        );
    }

    /**
     * This is the full / raw ElasticSearch mapping which is merged with the properties and dynamicTemplates.
     *
     * It can be used to specify arbitrary ElasticSearch mapping options, like f.e. configuring the _all field.
     *
     * @param array $fullMapping
     */
    public function setFullMapping(array $fullMapping)
    {
        $this->fullMapping = $fullMapping;
    }

    /**
     * see {@link setFullMapping} for documentation
     * @return array
     */
    public function getFullMapping()
    {
        return $this->fullMapping;
    }
}
