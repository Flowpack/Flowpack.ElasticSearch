<?php
namespace Flowpack\ElasticSearch\Domain\Model\Client;

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
	 * @var string
	 */
	protected $username;

	/**
	 * @var string
	 */
	protected $password;

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
	 * @return string
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * @param string $username
	 * @return void
	 */
	public function setUsername($username) {
		$this->username = $username;
	}

	/**
	 * @return string
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * @param string $password
	 * @return void
	 */
	public function setPassword($password) {
		$this->password = $password;
	}

	/**
	 * @return \TYPO3\Flow\Http\Uri
	 */
	public function getUri() {
		$uri = new \TYPO3\Flow\Http\Uri('');
		if ($this->username) {
			$uri->setUsername($this->username);
		}
		if ($this->password) {
			$uri->setPassword($this->password);
		}
		$uri->setScheme($this->scheme);
		$uri->setHost($this->host);
		$uri->setPort($this->port);

		return $uri;
	}
}