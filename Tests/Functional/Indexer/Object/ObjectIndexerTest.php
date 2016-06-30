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

use Flowpack\ElasticSearch\Domain\Model\Document;
use Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer;
use Flowpack\ElasticSearch\Tests\Functional\Fixtures\TweetRepository;
use Flowpack\ElasticSearch\Tests\Functional\Fixtures\Tweet;
use TYPO3\Flow\Utility\Algorithms;

/**
 */
class ObjectIndexerTest extends \TYPO3\Flow\Tests\FunctionalTestCase
{
    /**
     * @var boolean
     */
    protected static $testablePersistenceEnabled = true;

    /**
     * @var TweetRepository
     */
    protected $testEntityRepository;

    /**
     * @var \Flowpack\ElasticSearch\Domain\Model\Client
     */
    protected $testClient;

    /**
     */
    public function setUp()
    {
        parent::setUp();
        $this->testEntityRepository = new TweetRepository();
        $this->testClient = $this->objectManager->get(ObjectIndexer::class)->getClient();
    }

    /**
     * @test
     */
    public function persistingNewObjectTriggersIndexing()
    {
        $testEntity = $this->createAndPersistTestEntity();
        $documentId = $this->persistenceManager->getIdentifierByObject($testEntity);

        $resultDocument = $this->testClient
            ->findIndex('flow_elasticsearch_functionaltests_twitter')
            ->findType('tweet')
            ->findDocumentById($documentId);
        $resultData = $resultDocument->getData();

        $this->assertEquals($testEntity->getMessage(), $resultData['message']);
        $this->assertEquals($testEntity->getUsername(), $resultData['username']);
    }

    /**
     * @test
     */
    public function updatingExistingObjectTriggersReindexing()
    {
        $testEntity = $this->createAndPersistTestEntity();
        $identifier = $this->persistenceManager->getIdentifierByObject($testEntity);

        $initialVersion = $this->testClient
            ->findIndex('flow_elasticsearch_functionaltests_twitter')
            ->findType('tweet')
            ->findDocumentById($identifier)
            ->getVersion();
        $this->assertInternalType('integer', $initialVersion);

        $persistedTestEntity = $this->testEntityRepository->findByIdentifier($identifier);
        $persistedTestEntity->setMessage('changed message.');
        $this->testEntityRepository->update($persistedTestEntity);
        $this->persistenceManager->persistAll();
        $this->persistenceManager->clearState();

        $changedDocument = $this->testClient
            ->findIndex('flow_elasticsearch_functionaltests_twitter')
            ->findType('tweet')
            ->findDocumentById($identifier);

        $this->assertSame($initialVersion + 1, $changedDocument->getVersion());
        $this->assertSame($changedDocument->getField('message'), 'changed message.');
    }

    /**
     * @test
     */
    public function removingObjectTriggersIndexRemoval()
    {
        $testEntity = $this->createAndPersistTestEntity();
        $identifier = $this->persistenceManager->getIdentifierByObject($testEntity);

        $initialDocument = $this->testClient
            ->findIndex('flow_elasticsearch_functionaltests_twitter')
            ->findType('tweet')
            ->findDocumentById($identifier);
        $this->assertInstanceOf(Document::class, $initialDocument);

        $persistedTestEntity = $this->testEntityRepository->findByIdentifier($identifier);
        $this->testEntityRepository->remove($persistedTestEntity);
        $this->persistenceManager->persistAll();
        $this->persistenceManager->clearState();

        $foundDocument = $this->testClient
            ->findIndex('flow_elasticsearch_functionaltests_twitter')
            ->findType('tweet')
            ->findDocumentById($identifier);
        $this->assertNull($foundDocument);
    }

    /**
     */
    protected function createAndPersistTestEntity()
    {
        $testEntity = new Tweet();
        $testEntity->setDate(new \DateTime());
        $testEntity->setMessage('This is a test message ' . Algorithms::generateRandomString(8));
        $testEntity->setUsername('Zak McKracken' . Algorithms::generateRandomString(8));

        $this->testEntityRepository->add($testEntity);
        $this->persistenceManager->persistAll();
        $this->persistenceManager->clearState();
        return $testEntity;
    }
}
