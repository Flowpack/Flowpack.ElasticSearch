<?php
namespace TYPO3\ElasticSearch\Mapping;

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
 * Builds the mapping information across the objects
 * @Flow\Scope("singleton")
 */
class EntityMappingBuilder {

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
	 * @var \TYPO3\ElasticSearch\Indexer\Object\Transform\TransformerFactory
	 */
	protected $transformerFactory;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\ElasticSearch\Indexer\Object\IndexInformer
	 */
	protected $indexInformer;

	/**
	 * Builds a Mapping collection from the annotation sources that are present
	 * @return \TYPO3\ElasticSearch\Mapping\MappingCollection<\TYPO3\ElasticSearch\Domain\Mapping>
	 */
	public function buildMappingInformation() {
		$mappings = new MappingCollection(MappingCollection::TYPE_ENTITY);
		foreach ($this->indexInformer->getClassesAndAnnotations() as $className => $annotation) {
			$mappings->add($this->buildMappingFromClassAndAnnotation($className, $annotation));
		}

		return $mappings;
	}

	/**
	 * @param string $className
	 * @param \TYPO3\ElasticSearch\Annotations\Indexable $annotation
	 * @return \TYPO3\ElasticSearch\Domain\Model\Mapping
	 */
	protected function buildMappingFromClassAndAnnotation($className, \TYPO3\ElasticSearch\Annotations\Indexable $annotation) {
		$index = new \TYPO3\ElasticSearch\Domain\Model\Index($annotation->indexName);
		$type = new \TYPO3\ElasticSearch\Domain\Model\GenericType($index, $annotation->typeName);
		$mapping = new \TYPO3\ElasticSearch\Domain\Model\Mapping($type);
		foreach ($this->indexInformer->getClassProperties($className) AS $propertyName) {
			$this->augmentMappingByProperty($mapping, $className, $propertyName);
		}

		return $mapping;
	}

	/**
	 * @param \TYPO3\ElasticSearch\Domain\Model\Mapping $mapping
	 * @param string $className
	 * @param string $propertyName
	 *
	 * @throws \TYPO3\ElasticSearch\Exception
	 * @return void
	 */
	protected function augmentMappingByProperty(\TYPO3\ElasticSearch\Domain\Model\Mapping $mapping, $className, $propertyName) {
		list($propertyType) = $this->reflectionService->getPropertyTagValues($className, $propertyName, 'var');
		if (\TYPO3\Flow\Utility\TypeHandling::isSimpleType($propertyType)) {
			$mappingType = $propertyType;
		} elseif (($transformAnnotation = $this->reflectionService->getPropertyAnnotation($className, $propertyName, 'TYPO3\ElasticSearch\Annotations\Transform')) !== NULL) {
			$mappingType = $this->transformerFactory->create($transformAnnotation->type)->getTargetMappingType();
		} elseif ($propertyType === '\DateTime') {
			$mappingType = 'date';
		} else {
			throw new \TYPO3\ElasticSearch\Exception('Mapping is only supported for simple types and DateTime objects; "' . $propertyType . '" given but without a Transform directive.');
		}
		$mapping->setPropertyByPath($propertyName, array('type' => $mappingType));

		$annotation = $this->reflectionService->getPropertyAnnotation($className, $propertyName, 'TYPO3\ElasticSearch\Annotations\Mapping');
		if ($annotation instanceof \TYPO3\ElasticSearch\Annotations\Mapping) {
			foreach ($annotation->getPropertiesArray() AS $mappingDirective => $directiveValue) {
				if ($directiveValue === NULL) {
					continue;
				}
				$mapping->setPropertyByPath(array($propertyName, $mappingDirective), $directiveValue);
			}
		}
	}



}
?>