<?php
namespace Flowpack\ElasticSearch\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.ElasticSearch".*
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * An abstract document type. Implement your own or use the GenericType provided with this package.
 */
abstract class AbstractType {

	/**
	 * @Flow\Inject
	 * @var \Flowpack\ElasticSearch\Domain\Factory\DocumentFactory
	 */
	protected $documentFactory;

	/**
	 * @var \Flowpack\ElasticSearch\Domain\Model\Index
	 */
	protected $index;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @param Index $index
	 * @param string $name
	 */
	public function __construct(Index $index, $name = NULL) {
		$this->index = $index;

		if ($name === NULL) {
			$this->name = str_replace('\\', '_', get_class($this));
		} else {
			$this->name = $name;
		}
	}

	/**
	 * Gets this type's name.
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return \Flowpack\ElasticSearch\Domain\Model\Index
	 */
	public function getIndex() {
		return $this->index;
	}

	/**
	 * Returns a document
	 *
	 * @param $id
	 *
	 * @return \Flowpack\ElasticSearch\Domain\Model\Document
	 */
	public function findDocumentById($id) {
		$response = $this->request('GET', '/' . $id);
		if ($response->getStatusCode() !== 200) {
			return NULL;
		}
		$document = $this->documentFactory->createFromResponse($this, $id, $response);

		return $document;
	}

	/**
	 * @param $id
	 * @return boolean ...whether the deletion is considered successful
	 */
	public function deleteDocumentById($id) {
		$response = $this->request('DELETE', '/' . $id);
		$treatedContent = $response->getTreatedContent();

		return $response->getStatusCode() === 200 && $treatedContent['ok'] === TRUE && $treatedContent['found'] === TRUE;
	}

	/**
	 * @return integer
	 */
	public function count() {
		$response = $this->request('GET', '/_count');
		if ($response->getStatusCode() !== 200) {
			return NULL;
		}
		$treatedContent = $response->getTreatedContent();

		return (integer)$treatedContent['count'];
	}

	/**
	 * @param array $searchQuery The search query TODO: make it an object
	 */
	public function search(array $searchQuery) {
		$response = $this->request('GET', '/_search', array(), json_encode($searchQuery));

		return $response;
	}

	/**
	 * @param string $method
	 * @param string $path
	 * @param array $arguments
	 * @param string $content
	 *
	 * @return \Flowpack\ElasticSearch\Transfer\Response
	 */
	public function request($method, $path = NULL, $arguments = array(), $content = NULL) {
		$path = '/' . $this->name . ($path ?: '');

		return $this->index->request($method, $path, $arguments, $content);
	}
}

