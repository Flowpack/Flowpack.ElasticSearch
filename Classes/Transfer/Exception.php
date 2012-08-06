<?php
namespace TYPO3\ElasticSearch\Transfer;

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
 * Exception that occurs related to ElasticSearch transfers
 */
class Exception extends \TYPO3\ElasticSearch\Exception {

	/**
	 * @var \TYPO3\FLOW3\Http\Response
	 */
	protected $response;

	/**
	 * @var \TYPO3\FLOW3\Http\Request
	 */
	protected $request;

	/**
	 *
	 */
	public function __construct($message, $code, \TYPO3\FLOW3\Http\Response $response, \TYPO3\FLOW3\Http\Request $request = NULL, \Exception $previous = NULL) {
		$this->response = $response;
		$this->request = $request;
		if ($request !== NULL) {
			$message = sprintf("[%s %s]: %s\n\nData: %s",
				$request->getMethod(),
				$request->getUri(),
				$message . '; Request body: ' . $response->getContent(),
				$request->getContent()
			);
		}

		parent::__construct($message, $code, $previous);
	}

	/**
	 * @return \TYPO3\FLOW3\Http\Request
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 * @return \TYPO3\FLOW3\Http\Response
	 */
	public function getResponse() {
		return $this->response;
	}
}

?>