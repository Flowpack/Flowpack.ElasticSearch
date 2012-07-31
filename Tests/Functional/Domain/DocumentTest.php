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

use \TYPO3\ElasticSearch\Tests\Functional\Fixtures\TwitterType;

/**
 */
class DocumentTest extends \TYPO3\ElasticSearch\Tests\Functional\Domain\AbstractTest {

	/**
	 * Array that returns sample data. Intentionally returns only one record.
	 * @return array
	 */
	public function simpleDocumentDataProvider() {
		return array(
			array(
				array(
					'user' => 'kimchy',
					'post_date' => '2009-11-15T14:12:12',
					'message' => 'trying out Elastic Search'
				)
			)
		);
	}

	/**
	 * @dataProvider simpleDocumentDataProvider
	 * @test
	 */
	public function idOfFreshNewDocumentIsPopulatedAfterStoring(array $data = NULL) {
		$document = new \TYPO3\ElasticSearch\Domain\Model\Document(new TwitterType($this->testingIndex), $data);
		$this->assertNull($document->getId());
		$document->store();
		$this->assertRegExp('/\w+/', $document->getId());
	}

	/**
	 * @dataProvider simpleDocumentDataProvider
	 * @test
	 */
	public function versionOfFreshNewDocumentIsCreatedAfterStoringAndIncreasedAfterSubsequentStoring(array $data = NULL) {
		$document = new \TYPO3\ElasticSearch\Domain\Model\Document(new TwitterType($this->testingIndex), $data);
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
	public function existingIdOfDocumentIsNotModifiedAfterStoring(array $data) {
		$id = '42-1010-42';
		$document = new \TYPO3\ElasticSearch\Domain\Model\Document(new TwitterType($this->testingIndex), $data, $id);
		$document->store();
		$this->assertSame($id, $document->getId());
	}
}