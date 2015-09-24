<?php
namespace Flowpack\ElasticSearch\Tests\Functional\Mapping;

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
class MappingBuilderTest extends \TYPO3\Flow\Tests\FunctionalTestCase
{
    /**
     * @var \Flowpack\ElasticSearch\Mapping\EntityMappingBuilder
     */
    protected $mappingBuilder;

    /**
     */
    public function setUp()
    {
        parent::setUp();
        $this->mappingBuilder = $this->objectManager->get('Flowpack\ElasticSearch\Mapping\EntityMappingBuilder');
    }

    /**
     * @test
     */
    public function basicTest()
    {
        $information = $this->mappingBuilder->buildMappingInformation();
        $this->assertGreaterThanOrEqual(2, count($information));
        $this->assertInstanceOf('Flowpack\ElasticSearch\Domain\Model\Mapping', $information[0]);
    }
}
