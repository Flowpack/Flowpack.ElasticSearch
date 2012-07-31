<?php
namespace TYPO3\ElasticSearch\Tests\Functional\Mapping;

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
class MappingBuilderTest extends \TYPO3\FLOW3\Tests\FunctionalTestCase {
	/**
	 * @var \TYPO3\ElasticSearch\Mapping\EntityMappingBuilder
	 */
	protected $mappingBuilder;

	/**
	 */
	public function setUp() {
		parent::setUp();
		$this->mappingBuilder = $this->objectManager->get('TYPO3\ElasticSearch\Mapping\EntityMappingBuilder');
	}

	/**
	 * @test
	 */
	public function basicTest() {
		$information = $this->mappingBuilder->buildMappingInformation();
		$this->assertGreaterThanOrEqual(2, count($information));
		$this->assertInstanceOf('TYPO3\ElasticSearch\Domain\Model\Mapping', $information[0]);
	}

}