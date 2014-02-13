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

/**
 * Exception that occurs related to ElasticSearch transfers
 */
class Exception extends \Flowpack\ElasticSearch\Exception {

	/**
	 * @var \TYPO3\Flow\Http\Response
	 */
	protected $response;

	/**
	 * @var \TYPO3\Flow\Http\Request
	 */
	protected $request;

	/**
	 *
	 */
	public function __construct($message, $code, \TYPO3\Flow\Http\Response $response, \TYPO3\Flow\Http\Request $request = NULL, \Exception $previous = NULL) {
		$this->response = $response;
		$this->request = $request;
		if ($request !== NULL) {
			$message = sprintf("[%s %s]: %s\n\nRequest data: %s",
				$request->getMethod(),
				$request->getUri(),
				$message . '; Response body: ' . $response->getContent(),

				$request->getContent()
			);
		}

		parent::__construct($message, $code, $previous);
	}

	/**
	 * @return \TYPO3\Flow\Http\Request
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 * @return \TYPO3\Flow\Http\Response
	 */
	public function getResponse() {
		return $this->response;
	}
}

