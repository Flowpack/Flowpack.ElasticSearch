<?php
namespace Flowpack\ElasticSearch\Tests\Functional\Domain;

/*
 * This file is part of the Flowpack.ElasticSearch package.
 *
 * (c) Contributors of the Flowpack Team - flowpack.org
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Flowpack\ElasticSearch\Domain\Model\Index;

class IndexTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function indexWithoutPrefix()
    {
        $this->clientFactory = $this->objectManager->get(ClientFactory::class);
        $client = $this->clientFactory->create("FunctionalTests");
        $testObject = new Index('index_without_prefix', $client);
        
        static::assertSame('index_without_prefix', $index->getOriginalName());
        static::assertSame('index_without_prefix', $index->getName());
        static::assert(['prefix' => null], $index->getSettings());
    }

    /**
     * @test
     */
    public function indexWithPrefix()
    {
        $this->clientFactory = $this->objectManager->get(ClientFactory::class);
        $client = $this->clientFactory->create("FunctionalTests");
        $testObject = new Index('index_with_prefix', $client);
        
        static::assertSame('index_with_prefix', $index->getOriginalName());
        static::assertSame('prefix_index_with_prefix', $index->getName());
        static::assert(['prefix' => 'prefix'], $index->getSettings());
    }
}
