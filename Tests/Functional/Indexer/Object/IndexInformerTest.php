<?php
namespace TYPO3\ElasticSearch\Tests\Functional\Indexer\Object;

/*                                                                        *
 * This script belongs to the FLOW3-package "TYPO3.ElasticSearch".        *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 */
class IndexInformerTest extends \TYPO3\Flow\Tests\FunctionalTestCase {
	/**
	 * @var \TYPO3\ElasticSearch\Indexer\Object\IndexInformer
	 */
	protected $informer;

	/**
	 */
	public function setUp() {
		parent::setUp();
		$this->informer = $this->objectManager->get('TYPO3\ElasticSearch\Indexer\Object\IndexInformer');
	}

	/**
	 * @test
	 */
	public function classAnnotationTest() {
		$actual = $this->informer->getClassAnnotation('TYPO3\ElasticSearch\Tests\Functional\Fixtures\JustFewPropertiesToIndex');
		$this->assertInstanceOf('TYPO3\ElasticSearch\Annotations\Indexable', $actual);
		$this->assertSame('dummyindex', $actual->indexName);
		$this->assertSame('sampletype', $actual->typeName);
	}

	/**
	 * @test
	 */
	public function classWithOnlyOnePropertyAnnotatedHasOnlyThisPropertyToBeIndexed() {
		$actual = $this->informer->getClassProperties('TYPO3\ElasticSearch\Tests\Functional\Fixtures\JustFewPropertiesToIndex');
		$this->assertCount(1, $actual);
	}

	/**
	 * @test
	 */
	public function classWithNoPropertyAnnotatedHasAllPropertiesToBeIndexed() {
		$actual = $this->informer->getClassProperties('TYPO3\ElasticSearch\Tests\Functional\Fixtures\Tweet');
		$this->assertGreaterThan(1, $actual);
	}
}