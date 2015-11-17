<?php
namespace Flowpack\ElasticSearch\Transfer;

/*
 * This file is part of the Flowpack.ElasticSearch package.
 *
 * (c) Contributors of the Flowpack Team - flowpack.org
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

/**
 * Exception that occurs related to ElasticSearch transfers
 */
class Exception extends \Flowpack\ElasticSearch\Exception
{
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
    public function __construct($message, $code, \TYPO3\Flow\Http\Response $response, \TYPO3\Flow\Http\Request $request = null, \Exception $previous = null)
    {
        $this->response = $response;
        $this->request = $request;
        if ($request !== null) {
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
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return \TYPO3\Flow\Http\Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
