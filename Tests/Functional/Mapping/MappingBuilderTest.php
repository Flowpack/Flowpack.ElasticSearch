<?php
namespace Flowpack\ElasticSearch\Tests\Functional\Mapping;

/*
 * This file is part of the Flowpack.ElasticSearch package.
 *
 * (c) Contributors of the Flowpack Team - flowpack.org
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

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
