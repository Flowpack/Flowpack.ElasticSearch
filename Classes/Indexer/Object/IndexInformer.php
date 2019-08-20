<?php
declare(strict_types=1);

namespace Flowpack\ElasticSearch\Indexer\Object;

/*
 * This file is part of the Flowpack.ElasticSearch package.
 *
 * (c) Contributors of the Flowpack Team - flowpack.org
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Flowpack\ElasticSearch\Annotations\Indexable;
use Flowpack\ElasticSearch\Exception as ElasticSearchException;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Reflection\ReflectionService;

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
    protected $indexAnnotations = [];

    /**
     * @return void
     */
    public function initializeObject()
    {
        $this->indexAnnotations = self::buildIndexClassesAndProperties($this->objectManager);
    }

    /**
     * Creates the source array of what classes and properties have to be annotated.
     * The returned array consists of class names, with a sub-key having both 'annotation' and 'properties' set.
     * The annotation contains the class's annotation, while properties contains each property that has to be indexed.
     * Each property might either have TRUE as value, or also an annotation instance, if given.
     *
     * @param ObjectManagerInterface $objectManager
     * @return array
     * @throws ElasticSearchException
     */
    public static function buildIndexClassesAndProperties($objectManager)
    {
        /** @var ReflectionService $reflectionService */
        $reflectionService = $objectManager->get(ReflectionService::class);

        $indexAnnotations = [];

        $annotationClassName = Indexable::class;
        foreach ($reflectionService->getClassNamesByAnnotation($annotationClassName) as $className) {
            if ($reflectionService->isClassAbstract($className)) {
                throw new ElasticSearchException(sprintf('The class with name "%s" is annotated with %s, but is abstract. Indexable classes must not be abstract.', $className, $annotationClassName), 1339595182);
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

    /**
     * Returns all indexes name deplared in class annotations
     *
     * @return array
     */
    public function getAllIndexNames()
    {
        $indexes = [];
        foreach ($this->getClassesAndAnnotations() as $configuration) {
            /** @var Indexable $configuration */
            $indexes[$configuration->indexName] = $configuration->indexName;
        }

        return array_keys($indexes);
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
            $classesAndAnnotations = [];
            foreach (array_keys($this->indexAnnotations) as $className) {
                $classesAndAnnotations[$className] = $this->indexAnnotations[$className]['annotation'];
            }
        }

        return $classesAndAnnotations;
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
}
