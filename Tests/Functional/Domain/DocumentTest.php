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

use Flowpack\ElasticSearch\Domain\Model\Document;
use Flowpack\ElasticSearch\Tests\Functional\Fixtures\TwitterType;

/**
 */
class DocumentTest extends AbstractTest
{
    /**
     * Array that returns sample data. Intentionally returns only one record.
     * @return array
     */
    public function simpleDocumentDataProvider()
    {
        return [
            [
                [
                    'user' => 'kimchy',
                    'post_date' => '2009-11-15T14:12:12',
                    'message' => 'trying out Elastic Search'
                ]
            ]
        ];
    }

    /**
     * @dataProvider simpleDocumentDataProvider
     * @test
     */
    public function idOfFreshNewDocumentIsPopulatedAfterStoring(array $data = null)
    {
        $document = new Document(new TwitterType($this->testingIndex), $data);
        $this->assertNull($document->getId());
        $document->store();
        $this->assertRegExp('/\w+/', $document->getId());
    }

    /**
     * @dataProvider simpleDocumentDataProvider
     * @test
     */
    public function versionOfFreshNewDocumentIsCreatedAfterStoringAndIncreasedAfterSubsequentStoring(array $data = null)
    {
        $document = new Document(new TwitterType($this->testingIndex), $data);
        $this->assertNull($document->getVersion());
        $document->store();
        $idAfterFirstStoring = $document->getId();
        $this->assertSame(1, $document->getVersion());
        $document->store();
        $this->assertSame(2, $document->getVersion());
        $this->assertSame($idAfterFirstStoring, $document->getId());
    }

    /**
     * @dataProvider simpleDocumentDataProvider
     * @test
     */
    public function existingIdOfDocumentIsNotModifiedAfterStoring(array $data)
    {
        $id = '42-1010-42';
        $document = new Document(new TwitterType($this->testingIndex), $data, $id);
        $document->store();
        $this->assertSame($id, $document->getId());
    }
}
