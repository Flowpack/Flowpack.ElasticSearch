<?php
namespace Flowpack\ElasticSearch\Indexer\Object;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.ElasticSearch".*
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Flowpack\ElasticSearch\Annotations\Indexable;
use TYPO3\Flow\Annotations as Flow;

/**
 * Provides information about the index rules of Objects
 * @Flow\Scope("singleton")
 */
class IndexInformer {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Reflection\ReflectionService
	 */
	protected $reflectionService;

	/**
	 * @var array
	 */
	protected $indexAnnotations = array();

	/**
	 */
	public function initializeObject() {
		$this->buildIndexClassesAndProperties();
	}

	/**
	 * Returns the to-index classes and their annotation
	 *
	 * @return array
	 */
	public function getClassesAndAnnotations() {
		static $classesAndAnnotations;
		if ($classesAndAnnotations === NULL) {
			$classesAndAnnotations = array();
			foreach (array_keys($this->indexAnnotations) AS $className) {
				$classesAndAnnotations[$className] = $this->indexAnnotations[$className]['annotation'];
			}
		}

		return $classesAndAnnotations;
	}

	/**
	 * Returns all indexes name deplared in class annotations
	 *
	 * @return array
	 */
	public function getAllIndexName() {
		$indexes = array();
		foreach ($this->getClassesAndAnnotations() as $configuration) {
			/** @var Indexable $configuration */
			$indexes[$configuration->indexName] = $configuration->indexName;
		}

		return array_keys($indexes);
	}

	/**
	 * @param string $className
	 * @return \Flowpack\ElasticSearch\Annotations\Indexable The annotation for this class
	 */
	public function getClassAnnotation($className) {
		if (!isset($this->indexAnnotations[$className])) {
			return NULL;
		}

		return $this->indexAnnotations[$className]['annotation'];
	}

	/**
	 * @param string $className
	 * @return array
	 */
	public function getClassProperties($className) {
		if (!isset($this->indexAnnotations[$className])) {
			return NULL;
		}

		return $this->indexAnnotations[$className]['properties'];
	}

	/**
	 * Creates the source array of what classes and properties have to be annotated.
	 * The returned array consists of class names, with a sub-key having both 'annotation' and 'properties' set.
	 * The annotation contains the class's annotation, while properties contains each property that has to be indexed.
	 * Each property might either have TRUE as value, or also an annotation instance, if given.
	 *
	 * @throws \Flowpack\ElasticSearch\Exception
	 * @return array multidim array
	 */
	protected function buildIndexClassesAndProperties() {
		$annotationClassName = 'Flowpack\ElasticSearch\Annotations\Indexable';
		foreach ($this->reflectionService->getClassNamesByAnnotation($annotationClassName) AS $className) {
			if ($this->reflectionService->isClassAbstract($className)) {
				throw new \Flowpack\ElasticSearch\Exception(sprintf('The class with name "%s" is annotated with %s, but is abstract. Indexable classes must not be abstract.', $className, $annotationClassName), 1339595182);
			}
			$this->indexAnnotations[$className]['annotation'] = $this->reflectionService->getClassAnnotation($className, $annotationClassName);

			// if no single properties are set to be indexed, consider all properties to be indexed.
			$annotatedProperties = $this->reflectionService->getPropertyNamesByAnnotation($className, $annotationClassName);
			if (!empty($annotatedProperties)) {
				$this->indexAnnotations[$className]['properties'] = $annotatedProperties;
			} else {
				foreach ($this->reflectionService->getClassPropertyNames($className) AS $propertyName) {
					$this->indexAnnotations[$className]['properties'][] = $propertyName;
				}
			}
		}
	}
}

