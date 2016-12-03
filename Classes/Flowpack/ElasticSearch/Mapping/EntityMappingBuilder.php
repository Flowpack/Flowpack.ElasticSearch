<?php
namespace Flowpack\ElasticSearch\Mapping;

/*
 * This file is part of the Flowpack.ElasticSearch package.
 *
 * (c) Contributors of the Flowpack Team - flowpack.org
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Flowpack\ElasticSearch\Annotations\Indexable as IndexableAnnotation;
use Flowpack\ElasticSearch\Annotations\Mapping as MappingAnnotation;
use Flowpack\ElasticSearch\Domain\Model\Mapping;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Utility\Arrays;

/**
 * Builds the mapping information across the objects
 * @Flow\Scope("singleton")
 */
class EntityMappingBuilder
{
    /**
     * @Flow\Inject
     * @var \Neos\Flow\Reflection\ReflectionService
     */
    protected $reflectionService;

    /**
     * @Flow\Inject
     * @var \Neos\Flow\ObjectManagement\ObjectManagerInterface
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
     * @param IndexableAnnotation $annotation
     * @return Mapping
     */
    protected function buildMappingFromClassAndAnnotation($className, IndexableAnnotation $annotation)
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
     * @return void
     * @throws \Flowpack\ElasticSearch\Exception
     */
    protected function augmentMappingByProperty(Mapping $mapping, $className, $propertyName)
    {
        list($propertyType) = $this->reflectionService->getPropertyTagValues($className, $propertyName, 'var');
        if (($transformAnnotation = $this->reflectionService->getPropertyAnnotation($className, $propertyName, 'Flowpack\ElasticSearch\Annotations\Transform')) !== null) {
            $mappingType = $this->transformerFactory->create($transformAnnotation->type)->getTargetMappingType();
        } elseif (\Neos\Flow\Utility\TypeHandling::isSimpleType($propertyType)) {
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
                $multiFields = [];
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
    protected function processMappingAnnotation(MappingAnnotation $annotation, array $propertyMapping = array())
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
