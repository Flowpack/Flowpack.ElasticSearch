<?php
namespace TYPO3\ElasticSearch\Domain\Model\Client;

/*                                                                        *
 * This script belongs to the FLOW3-package "TYPO3.ElasticSearch".        *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use \TYPO3\Flow\Annotations as Flow;

/**
 * Client configuration
 */
class ClientConfiguration {

	/**
	 * @var string
	 */
	protected $host;

	/**
	 * @var integer
	 */
	protected $port;

	/**
	 * @var string
	 */
	protected $scheme = 'http';

	/**
	 * @param string $host
	 */
	public function setHost($host) {
		$this->host = $host;
	}

	/**
	 * @return string
	 */
	public function getHost() {
		return $this->host;
	}

	/**
	 * @param int $port
	 */
	public function setPort($port) {
		$this->port = $port;
	}

	/**
	 * @return int
	 */
	public function getPort() {
		return $this->port;
	}

	/**
	 * @param string $scheme
	 */
	public function setScheme($scheme) {
		$this->scheme = $scheme;
	}

	/**
	 * @return string
	 */
	public function getScheme() {
		return $this->scheme;
	}

	/**
	 * @return \TYPO3\Flow\Http\Uri
	 */
	public function getUri() {
		$uri = new \TYPO3\Flow\Http\Uri('');
		$uri->setScheme($this->scheme);
		$uri->setHost($this->host);
		$uri->setPort($this->port);

		return $uri;
	}
}