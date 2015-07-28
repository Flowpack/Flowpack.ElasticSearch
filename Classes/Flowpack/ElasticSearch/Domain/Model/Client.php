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
 * A Client representation
 */
class Client {

	/**
	 * @var string
	 */
	protected $bundle = 'default';

	/**
	 * @Flow\Inject
	 * @var \Flowpack\ElasticSearch\Transfer\RequestService
	 */
	protected $requestService;

	/**
	 * @var array
	 */
	protected $clientConfigurations;

	/**
	 * @var array
	 */
	protected $indexCollection = array();

	/**
	 * @param string $bundle
	 */
	public function setBundle($bundle) {
		$this->bundle = $bundle;
	}

	/**
	 * @return string
	 */
	public function getBundle() {
		return $this->bundle;
	}

	/**
	 * @param array $clientConfigurations
	 */
	public function setClientConfigurations($clientConfigurations) {
		$this->clientConfigurations = $clientConfigurations;
	}

	/**
	 * @return array
	 */
	public function getClientConfigurations() {
		return $this->clientConfigurations;
	}

	/**
	 * @param string $indexName
	 * @return \Flowpack\ElasticSearch\Domain\Model\Index
	 */
	public function findIndex($indexName) {
		if (!array_key_exists($indexName, $this->indexCollection)) {
			$this->indexCollection[$indexName] = new Index($indexName, $this);
		}

		return $this->indexCollection[$indexName];
	}

	/**
	 * Passes a request through to the request service
	 *
	 * @param string $method
	 * @param string $path
	 * @param array $arguments
	 * @param string|array $content
	 *
	 * @return \Flowpack\ElasticSearch\Transfer\Response
	 */
	public function request($method, $path = NULL, $arguments = array(), $content = NULL) {
		return $this->requestService->request($method, $this, $path, $arguments, $content);
	}
}

