<?php
namespace Flowpack\ElasticSearch\Mapping;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.ElasticSearch".*
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Flowpack\ElasticSearch\Annotations\Mapping as MappingAnnotation;
use Flowpack\ElasticSearch\Domain\Model\Mapping;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Arrays;

/**
 * Builds the mapping information across the objects
 * @Flow\Scope("singleton")
 */
class EntityMappingBuilder
{
    /**
     * @Flow\Inject
     * @var \TYPO3\Flow\Reflection\ReflectionService
     */
    protected $reflectionService;

    /**
     * @Flow\Inject
     * @var \TYPO3\Flow\Object\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @Flow\Inject
     * @var \Flowpack\ElasticSearch\Indexer\Object\Transform\TransformerFactory
     */
    protected $transformerFactory;

    /**
     * @Flow\Inject
     * @var \Flowpack\ElasticSearch\Indexer\Object\IndexInformer
     */
    protected $indexInformer;

    /**
     * Builds a Mapping collection from the annotation sources that are present
     *
     * @return \Flowpack\ElasticSearch\Mapping\MappingCollection<\Flowpack\ElasticSearch\Domain\Mapping>
     */
    public function buildMappingInformation()
    {
        $mappings = new MappingCollection(MappingCollection::TYPE_ENTITY);
        foreach ($this->indexInformer->getClassesAndAnnotations() as $className => $annotation) {
            $mappings->add($this->buildMappingFromClassAndAnnotation($className, $annotation));
        }

        return $mappings;
    }

    /**
     * @param string $className
     * @param \Flowpack\ElasticSearch\Annotations\Indexable $annotation
     * @return Mapping
     */
    protected function buildMappingFromClassAndAnnotation($className, \Flowpack\ElasticSearch\Annotations\Indexable $annotation)
    {
        $index = new \Flowpack\ElasticSearch\Domain\Model\Index($annotation->indexName);
        $type = new \Flowpack\ElasticSearch\Domain\Model\GenericType($index, $annotation->typeName);
        $mapping = new Mapping($type);
        foreach ($this->indexInformer->getClassProperties($className) as $propertyName) {
            $this->augmentMappingByProperty($mapping, $className, $propertyName);
        }

        return $mapping;
    }

    /**
     * @param Mapping $mapping
     * @param string $className
     * @param string $propertyName
     *
     * @throws \Flowpack\ElasticSearch\Exception
     * @return void
     */
    protected function augmentMappingByProperty(Mapping $mapping, $className, $propertyName)
    {
        list($propertyType) = $this->reflectionService->getPropertyTagValues($className, $propertyName, 'var');
        if (($transformAnnotation = $this->reflectionService->getPropertyAnnotation($className, $propertyName, 'Flowpack\ElasticSearch\Annotations\Transform')) !== null) {
            $mappingType = $this->transformerFactory->create($transformAnnotation->type)->getTargetMappingType();
        } elseif (\TYPO3\Flow\Utility\TypeHandling::isSimpleType($propertyType)) {
            $mappingType = $propertyType;
        } elseif ($propertyType === '\DateTime') {
            $mappingType = 'date';
        } else {
            throw new \Flowpack\ElasticSearch\Exception('Mapping is only supported for simple types and DateTime objects; "' . $propertyType . '" given but without a Transform directive.');
        }

        $mapping->setPropertyByPath($propertyName, array('type' => $mappingType));

        $annotation = $this->reflectionService->getPropertyAnnotation($className, $propertyName, 'Flowpack\ElasticSearch\Annotations\Mapping');

        if ($annotation instanceof MappingAnnotation) {
            $mapping->setPropertyByPath($propertyName, $this->processMappingAnnotation($annotation, $mapping->getPropertyByPath($propertyName)));
            if ($annotation->getFields()) {
                foreach ($annotation->getFields() as $multiFieldAnnotation) {
                    $multiFieldIndexName = trim($multiFieldAnnotation->index_name);
                    if ($multiFieldIndexName === '') {
                        throw new \Flowpack\ElasticSearch\Exception('Multi field require an unique index name "' . $className . '::' . $propertyName . '".');
                    }
                    if (isset($multiFields[$multiFieldIndexName])) {
                        throw new \Flowpack\ElasticSearch\Exception('Duplicate index name in the same multi field is not allowed "' . $className . '::' . $propertyName . '".');
                    }
                    $multiFieldAnnotation->type = $mappingType;
                    $multiFields[$multiFieldIndexName] = $this->processMappingAnnotation($multiFieldAnnotation);
                }
                $mapping->setPropertyByPath(array($propertyName, 'fields'), $multiFields);
            }
        }
    }

    /**
     * @param MappingAnnotation $annotation
     * @param array $propertyMapping
     * @return array
     */
    protected function processMappingAnnotation(MappingAnnotation $annotation, $propertyMapping = array())
    {
        foreach ($annotation->getPropertiesArray() as $mappingDirective => $directiveValue) {
            if ($directiveValue === null) {
                continue;
            }
            $propertyMapping = Arrays::setValueByPath($propertyMapping, $mappingDirective, $directiveValue);
        }

        return $propertyMapping;
    }
}
