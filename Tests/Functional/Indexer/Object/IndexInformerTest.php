<?php
namespace Flowpack\ElasticSearch\Tests\Functional\Indexer\Object;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.ElasticSearch".*
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
	 * @var \Flowpack\ElasticSearch\Indexer\Object\IndexInformer
	 */
	protected $informer;

	/**
	 */
	public function setUp() {
		parent::setUp();
		$this->informer = $this->objectManager->get('Flowpack\ElasticSearch\Indexer\Object\IndexInformer');
	}

	/**
	 * @test
	 */
	public function classAnnotationTest() {
		$actual = $this->informer->getClassAnnotation('Flowpack\ElasticSearch\Tests\Functional\Fixtures\JustFewPropertiesToIndex');
		$this->assertInstanceOf('Flowpack\ElasticSearch\Annotations\Indexable', $actual);
		$this->assertSame('dummyindex', $actual->indexName);
		$this->assertSame('sampletype', $actual->typeName);
	}

	/**
	 * @test
	 */
	public function classWithOnlyOnePropertyAnnotatedHasOnlyThisPropertyToBeIndexed() {
		$actual = $this->informer->getClassProperties('Flowpack\ElasticSearch\Tests\Functional\Fixtures\JustFewPropertiesToIndex');
		$this->assertCount(1, $actual);
	}

	/**
	 * @test
	 */
	public function classWithNoPropertyAnnotatedHasAllPropertiesToBeIndexed() {
		$actual = $this->informer->getClassProperties('Flowpack\ElasticSearch\Tests\Functional\Fixtures\Tweet');
		$this->assertGreaterThan(1, $actual);
	}
}