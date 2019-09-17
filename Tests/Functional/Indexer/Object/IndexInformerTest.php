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
use Neos\Flow\Tests\FunctionalTestCase;

class IndexInformerTest extends FunctionalTestCase
{
    /**
     * @var IndexInformer
     */
    protected $informer;

    /**
     */
    public function setUp(): void
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
        static::assertInstanceOf(IndexableAnnotation::class, $actual);
        static::assertSame('dummyindex', $actual->indexName);
        static::assertSame('sampletype', $actual->typeName);
    }

    /**
     * @test
     */
    public function classWithOnlyOnePropertyAnnotatedHasOnlyThisPropertyToBeIndexed()
    {
        $actual = $this->informer->getClassProperties(Fixtures\JustFewPropertiesToIndex::class);
        static::assertCount(1, $actual);
    }

    /**
     * @test
     */
    public function classWithNoPropertyAnnotatedHasAllPropertiesToBeIndexed()
    {
        $actual = $this->informer->getClassProperties(Fixtures\Tweet::class);
        static::assertGreaterThan(1, $actual);
    }
}
