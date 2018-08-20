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
use Flowpack\ElasticSearch\Annotations\Transform;
use Flowpack\ElasticSearch\Domain\Model\GenericType;
use Flowpack\ElasticSearch\Domain\Model\Index as ElasticSearchIndex;
use Flowpack\ElasticSearch\Domain\Model\Mapping;
use Flowpack\ElasticSearch\Exception as ElasticSearchException;
use Flowpack\ElasticSearch\Indexer\Object\IndexInformer;
use Flowpack\ElasticSearch\Indexer\Object\Transform\TransformerFactory;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Reflection\ReflectionService;
use Neos\Utility\Arrays;
use Neos\Utility\TypeHandling;

/**
 * Builds the mapping information across the objects
 * @Flow\Scope("singleton")
 */
class EntityMappingBuilder
{
    /**
     * @Flow\Inject
     * @var ReflectionService
     */
    protected $reflectionService;

    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @Flow\Inject
     * @var TransformerFactory
     */
    protected $transformerFactory;

    /**
     * @Flow\Inject
     * @var IndexInformer
     */
    protected $indexInformer;

    /**
     * Builds a Mapping collection from the annotation sources that are present
     *
     * @return MappingCollection<Mapping>
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
        $index = new ElasticSearchIndex($annotation->indexName);
        $type = new GenericType($index, $annotation->typeName);
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
     * @throws ElasticSearchException
     */
    protected function augmentMappingByProperty(Mapping $mapping, $className, $propertyName)
    {
        list($propertyType) = $this->reflectionService->getPropertyTagValues($className, $propertyName, 'var');
        if (($transformAnnotation = $this->reflectionService->getPropertyAnnotation($className, $propertyName, Transform::class)) !== null) {
            $mappingType = $this->transformerFactory->create($transformAnnotation->type)->getTargetMappingType();
        } elseif ($propertyType === 'string') {
            // string must be mapped to text as elasticsearch does not support the 'string' type for version >=5.0
            $mappingType = 'text';
        } elseif (TypeHandling::isSimpleType($propertyType)) {
            $mappingType = $propertyType;
        } elseif ($propertyType === '\DateTime') {
            $mappingType = 'date';
        } else {
            throw new ElasticSearchException('Mapping is only supported for simple types and DateTime objects; "' . $propertyType . '" given but without a Transform directive.');
        }

        $mapping->setPropertyByPath($propertyName, ['type' => $mappingType]);

        $annotation = $this->reflectionService->getPropertyAnnotation($className, $propertyName, MappingAnnotation::class);

        if ($annotation instanceof MappingAnnotation) {
            $mapping->setPropertyByPath($propertyName, $this->processMappingAnnotation($annotation, $mapping->getPropertyByPath($propertyName)));
            if ($annotation->getFields()) {
                $multiFields = [];
                foreach ($annotation->getFields() as $multiFieldAnnotation) {
                    $multiFieldIndexName = trim($multiFieldAnnotation->index_name);
                    if ($multiFieldIndexName === '') {
                        throw new ElasticSearchException('Multi field require an unique index name "' . $className . '::' . $propertyName . '".');
                    }
                    if (isset($multiFields[$multiFieldIndexName])) {
                        throw new ElasticSearchException('Duplicate index name in the same multi field is not allowed "' . $className . '::' . $propertyName . '".');
                    }
                    $multiFieldAnnotation->type = $mappingType;
                    $multiFields[$multiFieldIndexName] = $this->processMappingAnnotation($multiFieldAnnotation);
                }
                $mapping->setPropertyByPath([$propertyName, 'fields'], $multiFields);
            }
        }
    }

    /**
     * @param MappingAnnotation $annotation
     * @param array $propertyMapping
     * @return array
     */
    protected function processMappingAnnotation(MappingAnnotation $annotation, array $propertyMapping = [])
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
