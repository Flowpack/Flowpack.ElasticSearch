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

use Flowpack\ElasticSearch\Exception;
use TYPO3\Flow\Annotations as Flow;

/**
 * Representation of an Index
 */
class Index {

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * The owner client of this index. Could be set later in order to allow creating pending Index objects
	 *
	 * @var \Flowpack\ElasticSearch\Domain\Model\Client
	 */
	protected $client;

	/**
	 * @param $name
	 * @param \Flowpack\ElasticSearch\Domain\Model\Client $client $client
	 *
	 * @throws \Flowpack\ElasticSearch\Exception
	 */
	public function __construct($name, \Flowpack\ElasticSearch\Domain\Model\Client $client = NULL) {
		$name = trim($name);
		if (strlen($name) < 1 || substr($name, 0, 1) === '_') {
			throw new \Flowpack\ElasticSearch\Exception('The provided index name "' . $name . '" must not be empty and not start with an underscore.', 1340187948);
		} elseif ($name !== strtolower($name)) {
			throw new \Flowpack\ElasticSearch\Exception('The provided index name "' . $name . '" must be all lowercase.', 1340187956);
		}
		$this->name = $name;
		$this->client = $client;
	}

	/**
	 * @param $typeName
	 * @return \Flowpack\ElasticSearch\Domain\Model\AbstractType
	 */
	public function findType($typeName) {
		return new GenericType($this, $typeName);
	}

	/**
	 * @param array<AbstractType> $types
	 *
	 * @return TypeGroup
	 */
	public function findTypeGroup(array $types) {
		return new TypeGroup($this, $types);
	}

	/**
	 * @return boolean
	 */
	public function exists() {
		$response = $this->request('HEAD');

		return $response->getStatusCode() === 200;
	}

	/**
	 * @return void
	 */
	public function create() {
		$this->request('PUT');
	}

	/**
	 * @return void
	 */
	public function delete() {
		$this->request('DELETE');
	}

	/**
	 * Refresh the index
	 *
	 * @return void
	 */
	public function refresh() {
		$this->request('POST', '/_refresh');
	}

	/**
	 * @param string $method
	 * @param string $path
	 * @param array $arguments
	 * @param string $content
	 *
	 * @throws \Flowpack\ElasticSearch\Exception
	 * @return \Flowpack\ElasticSearch\Transfer\Response
	 */
	public function request($method, $path = NULL, $arguments = array(), $content = NULL) {
		if ($this->client === NULL) {
			throw new Exception('The client of the index "' . $this->name . '" is not set, hence no requests can be done.');
		}
		$path = '/' . $this->name . ($path ?: '');

		return $this->client->request($method, $path, $arguments, $content);
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param \Flowpack\ElasticSearch\Domain\Model\Client $client
	 */
	public function setClient($client) {
		$this->client = $client;
	}
}

