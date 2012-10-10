<?php
namespace TYPO3\ElasticSearch\Tests\Functional\Indexer\Object;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.ElasticSearch".   *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\ElasticSearch\Tests\Functional\Fixtures\TweetRepository;
use TYPO3\ElasticSearch\Tests\Functional\Fixtures\Tweet;

/**
 */
class ObjectIndexerTest extends \TYPO3\Flow\Tests\FunctionalTestCase {

	/**
	 * @var boolean
	 */
	static protected $testablePersistenceEnabled = TRUE;

	/**
	 * @var TweetRepository
	 */
	protected $testEntityRepository;

	/**
	 * @var \TYPO3\ElasticSearch\Domain\Model\Client
	 */
	protected $testClient;

	/**
	 */
	public function setUp() {
		parent::setUp();
		$this->testEntityRepository = new TweetRepository();
		$this->testClient = $this->objectManager->get('TYPO3\ElasticSearch\Indexer\Object\ObjectIndexer')->getClient();
	}

	/**
	 * @test
	 */
	public function persistingNewObjectTriggersIndexing() {
		$testEntity = $this->createAndPersistTestEntity();
		$documentId = $this->persistenceManager->getIdentifierByObject($testEntity);

		$resultDocument = $this->testClient
			->findIndex('flow3_elasticsearch_functionaltests_twitter')
			->findType('tweet')
			->findDocumentById($documentId);
		$resultData = $resultDocument->getData();

		$this->assertEquals($testEntity->getMessage(), $resultData['message']);
		$this->assertEquals($testEntity->getUsername(), $resultData['username']);
	}

	/**
	 * @test
	 */
	public function updatingExistingObjectTriggersReindexing() {
		$testEntity = $this->createAndPersistTestEntity();
		$identifier = $this->persistenceManager->getIdentifierByObject($testEntity);

		$initialVersion = $this->testClient
			->findIndex('flow3_elasticsearch_functionaltests_twitter')
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
			->findIndex('flow3_elasticsearch_functionaltests_twitter')
			->findType('tweet')
			->findDocumentById($identifier);

		$this->assertSame($initialVersion + 1, $changedDocument->getVersion());
		$this->assertSame($changedDocument->getField('message'), 'changed message.');
	}

	/**
	 * @test
	 */
	public function removingObjectTriggersIndexRemoval() {
		$testEntity = $this->createAndPersistTestEntity();
		$identifier = $this->persistenceManager->getIdentifierByObject($testEntity);

		$initialDocument = $this->testClient
			->findIndex('flow3_elasticsearch_functionaltests_twitter')
			->findType('tweet')
			->findDocumentById($identifier);
		$this->assertInstanceOf('TYPO3\ElasticSearch\Domain\Model\Document', $initialDocument);

		$persistedTestEntity = $this->testEntityRepository->findByIdentifier($identifier);
		$this->testEntityRepository->remove($persistedTestEntity);
		$this->persistenceManager->persistAll();
		$this->persistenceManager->clearState();

		$foundDocument = $this->testClient
			->findIndex('flow3_elasticsearch_functionaltests_twitter')
			->findType('tweet')
			->findDocumentById($identifier);
		$this->assertNull($foundDocument);
	}

	/**
	 */
	protected function createAndPersistTestEntity() {
		$testEntity = new Tweet();
		$testEntity->setDate(new \DateTime());
		$testEntity->setMessage('This is a test message ' . \TYPO3\Flow\Utility\Algorithms::generateRandomString(8));
		$testEntity->setUsername('Zak McKracken' . \TYPO3\Flow\Utility\Algorithms::generateRandomString(8));

		$this->testEntityRepository->add($testEntity);
		$this->persistenceManager->persistAll();
		$this->persistenceManager->clearState();
		return $testEntity;
	}
}

?>