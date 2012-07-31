<?php
namespace TYPO3\ElasticSearch\Tests\Functional\Domain;

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
abstract class AbstractTest extends \TYPO3\FLOW3\Tests\FunctionalTestCase {

	/**
	 * @var \TYPO3\ElasticSearch\Domain\Factory\ClientFactory
	 */
	protected $clientFactory;

	/**
	 * @var \TYPO3\ElasticSearch\Domain\Model\Index
	 */
	protected $testingIndex;

	/**
	 * @var bool
	 */
	protected $removeIndexOnTearDown = FALSE;

	/**
	 * final because else it could seriously damage the Index in the unlikely case there's already an index named FLOW3_ElasticSearch_FunctionalTests
	 */
	final public function setUp() {
		parent::setUp();

		$this->clientFactory = $this->objectManager->get('TYPO3\ElasticSearch\Domain\Factory\ClientFactory');
		$client = $this->clientFactory->create();
		$this->testingIndex = $client->findIndex('typo3_elasticsearch_functionaltests');

		if ($this->testingIndex->exists()) {
			throw new \Exception('The index "typo3_elasticsearch_functionaltests" already existed, aborting.', 1338967487);
		} else {
			$this->testingIndex->create();
			$this->removeIndexOnTearDown = TRUE;
		}

		$this->additionalSetUp();
	}

	/**
	 * may be implemented by inheritors because setUp() is final.
	 */
	protected function additionalSetUp() {
	}

	/**
	 * set to final because this is an important step which may not be overridden.
	 */
	final public function tearDown() {
		parent::tearDown();

		if ($this->removeIndexOnTearDown === TRUE) {
			$this->testingIndex->delete();
		}
	}
}

?>