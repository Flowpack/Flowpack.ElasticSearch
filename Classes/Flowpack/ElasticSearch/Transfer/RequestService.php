<?php
namespace Flowpack\ElasticSearch\Transfer;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.ElasticSearch".*
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use \TYPO3\Flow\Annotations as Flow;

/**
 * Handles the requests
 * @Flow\scope("singleton")
 */
class RequestService {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Http\Client\Browser
	 */
	protected $browser;

	/**
	 */
	public function initializeObject() {
		$this->browser->setRequestEngine(new \TYPO3\Flow\Http\Client\CurlEngine());
	}

	/**
	 * @param string $method
	 * @param \Flowpack\ElasticSearch\Domain\Model\Client $client
	 * @param string $path
	 * @param array $arguments
	 * @param string $content
	 *
	 * @return \Flowpack\ElasticSearch\Transfer\Response
	 */
	public function request($method, \Flowpack\ElasticSearch\Domain\Model\Client $client, $path = NULL, $arguments = array(), $content = NULL) {
		$clientConfigurations = $client->getClientConfigurations();
		$clientConfiguration = $clientConfigurations[0];

		$uri = clone $clientConfiguration->getUri();
		if ($path !== NULL) {
			$uri->setPath($uri->getPath() . $path);
		}

		$response = $this->browser->request($uri, $method, $arguments, array(), array(), $content);
		return new Response($response, $this->browser->getLastRequest());
	}

}

?>