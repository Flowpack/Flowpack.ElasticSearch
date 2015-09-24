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
 *
 */
class Response
{
    /**
     * @var \TYPO3\Flow\Http\Response
     */
    protected $originalResponse;

    /**
     * Contains the implementation-specific treated content
     *
     * @var mixed
     */
    protected $treatedContent;

    /**
     * @param \TYPO3\Flow\Http\Response $response
     * @param \TYPO3\Flow\Http\Request $request
     *
     * @throws \Flowpack\ElasticSearch\Transfer\Exception
     * @throws \Flowpack\ElasticSearch\Transfer\Exception\ApiException
     */
    public function __construct(\TYPO3\Flow\Http\Response $response, \TYPO3\Flow\Http\Request $request = null)
    {
        $this->originalResponse = $response;

        $treatedContent = json_decode($response->getContent(), true);

        if (strlen($response->getContent()) > 0) {
            if ($treatedContent === null) {
                throw new Exception('The request returned an invalid JSON string which was "' . $response->getContent() . '".', 1338976439, $response, $request);
            }

            if (array_key_exists('error', $treatedContent)) {
                throw new Exception\ApiException($treatedContent['error'], 1338977435, $response, $request);
            }
        }

        $this->treatedContent = $treatedContent;
    }

    /**
     * Shortcut to response's getStatusCode
     *
     * @return integer
     */
    public function getStatusCode()
    {
        return $this->originalResponse->getStatusCode();
    }

    /**
     * @return mixed
     */
    public function getTreatedContent()
    {
        return $this->treatedContent;
    }

    /**
     * @return \TYPO3\Flow\Http\Response
     */
    public function getOriginalResponse()
    {
        return $this->originalResponse;
    }
}
