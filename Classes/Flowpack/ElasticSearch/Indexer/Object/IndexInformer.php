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
use TYPO3\Flow\Object\ObjectManagerInterface;
use TYPO3\Flow\Reflection\ReflectionService;

/**
 * Provides information about the index rules of Objects
 * @Flow\Scope("singleton")
 */
class IndexInformer
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
     * @var array
     */
    protected $indexAnnotations = array();

    /**
     */
    public function initializeObject()
    {
        $this->indexAnnotations = self::buildIndexClassesAndProperties($this->objectManager);
    }

    /**
     * Returns the to-index classes and their annotation
     *
     * @return array
     */
    public function getClassesAndAnnotations()
    {
        static $classesAndAnnotations;
        if ($classesAndAnnotations === null) {
            $classesAndAnnotations = array();
            foreach (array_keys($this->indexAnnotations) as $className) {
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
    public function getAllIndexNames()
    {
        $indexes = array();
        foreach ($this->getClassesAndAnnotations() as $configuration) {
            /** @var Indexable $configuration */
            $indexes[$configuration->indexName] = $configuration->indexName;
        }

        return array_keys($indexes);
    }

    /**
     * @param string $className
     * @return Indexable The annotation for this class
     */
    public function getClassAnnotation($className)
    {
        if (!isset($this->indexAnnotations[$className])) {
            return null;
        }

        return $this->indexAnnotations[$className]['annotation'];
    }

    /**
     * @param string $className
     * @return array
     */
    public function getClassProperties($className)
    {
        if (!isset($this->indexAnnotations[$className])) {
            return null;
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
     * @param ObjectManagerInterface $objectManager
     * @return array
     * @Flow\CompileStatic
     */
    public static function buildIndexClassesAndProperties($objectManager)
    {
        /** @var ReflectionService $reflectionService */
        $reflectionService = $objectManager->get('TYPO3\Flow\Reflection\ReflectionService');

        $indexAnnotations = array();

        $annotationClassName = 'Flowpack\ElasticSearch\Annotations\Indexable';
        foreach ($reflectionService->getClassNamesByAnnotation($annotationClassName) as $className) {
            if ($reflectionService->isClassAbstract($className)) {
                throw new \Flowpack\ElasticSearch\Exception(sprintf('The class with name "%s" is annotated with %s, but is abstract. Indexable classes must not be abstract.', $className, $annotationClassName), 1339595182);
            }
            $indexAnnotations[$className]['annotation'] = $reflectionService->getClassAnnotation($className, $annotationClassName);

            // if no single properties are set to be indexed, consider all properties to be indexed.
            $annotatedProperties = $reflectionService->getPropertyNamesByAnnotation($className, $annotationClassName);
            if (!empty($annotatedProperties)) {
                $indexAnnotations[$className]['properties'] = $annotatedProperties;
            } else {
                foreach ($reflectionService->getClassPropertyNames($className) as $propertyName) {
                    $indexAnnotations[$className]['properties'][] = $propertyName;
                }
            }
        }

        return $indexAnnotations;
    }
}
