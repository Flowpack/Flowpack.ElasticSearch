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

use Flowpack\ElasticSearch\Annotations\Indexable as IndexableAnnotation;
use Flowpack\ElasticSearch\Indexer\Object\IndexInformer;
use Flowpack\ElasticSearch\Tests\Functional\Fixtures;

/**
 */
class IndexInformerTest extends \Neos\Flow\Tests\FunctionalTestCase
{
    /**
     * @var IndexInformer
     */
    protected $informer;

    /**
     */
    public function setUp()
    {
        parent::setUp();
        $this->informer = $this->objectManager->get(IndexInformer::class);
    }

    /**
     * @test
     */
    public function classAnnotationTest()
    {
        $actual = $this->informer->getClassAnnotation(Fixtures\JustFewPropertiesToIndex::class);
        $this->assertInstanceOf(IndexableAnnotation::class, $actual);
        $this->assertSame('dummyindex', $actual->indexName);
        $this->assertSame('sampletype', $actual->typeName);
    }

    /**
     * @test
     */
    public function classWithOnlyOnePropertyAnnotatedHasOnlyThisPropertyToBeIndexed()
    {
        $actual = $this->informer->getClassProperties(Fixtures\JustFewPropertiesToIndex::class);
        $this->assertCount(1, $actual);
    }

    /**
     * @test
     */
    public function classWithNoPropertyAnnotatedHasAllPropertiesToBeIndexed()
    {
        $actual = $this->informer->getClassProperties(Fixtures\Tweet::class);
        $this->assertGreaterThan(1, $actual);
    }
}
