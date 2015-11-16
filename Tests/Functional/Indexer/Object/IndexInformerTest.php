<?php
namespace Flowpack\ElasticSearch\Tests\Functional\Indexer\Object;

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
class IndexInformerTest extends \TYPO3\Flow\Tests\FunctionalTestCase
{
    /**
     * @var \Flowpack\ElasticSearch\Indexer\Object\IndexInformer
     */
    protected $informer;

    /**
     */
    public function setUp()
    {
        parent::setUp();
        $this->informer = $this->objectManager->get('Flowpack\ElasticSearch\Indexer\Object\IndexInformer');
    }

    /**
     * @test
     */
    public function classAnnotationTest()
    {
        $actual = $this->informer->getClassAnnotation('Flowpack\ElasticSearch\Tests\Functional\Fixtures\JustFewPropertiesToIndex');
        $this->assertInstanceOf('Flowpack\ElasticSearch\Annotations\Indexable', $actual);
        $this->assertSame('dummyindex', $actual->indexName);
        $this->assertSame('sampletype', $actual->typeName);
    }

    /**
     * @test
     */
    public function classWithOnlyOnePropertyAnnotatedHasOnlyThisPropertyToBeIndexed()
    {
        $actual = $this->informer->getClassProperties('Flowpack\ElasticSearch\Tests\Functional\Fixtures\JustFewPropertiesToIndex');
        $this->assertCount(1, $actual);
    }

    /**
     * @test
     */
    public function classWithNoPropertyAnnotatedHasAllPropertiesToBeIndexed()
    {
        $actual = $this->informer->getClassProperties('Flowpack\ElasticSearch\Tests\Functional\Fixtures\Tweet');
        $this->assertGreaterThan(1, $actual);
    }
}
