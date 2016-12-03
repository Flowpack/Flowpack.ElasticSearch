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
     * @var \Neos\Flow\Http\Response
     */
    protected $response;

    /**
     * @var \Neos\Flow\Http\Request
     */
    protected $request;

    /**
     * Exception constructor.
     *
     * @param string $message
     * @param integer $code
     * @param \Neos\Flow\Http\Response $response
     * @param \Neos\Flow\Http\Request $request
     * @param \Exception $previous
     */
    public function __construct($message, $code, \Neos\Flow\Http\Response $response, \Neos\Flow\Http\Request $request = null, \Exception $previous = null)
    {
        $this->response = $response;
        $this->request = $request;
        if ($request !== null) {
            $message = sprintf("Elasticsearch request failed.\n[%s %s]: %s\n\nRequest data: %s",
                $request->getMethod(),
                $request->getUri(),
                $message . '; Response body: ' . $response->getContent(),

                $request->getContent()
            );
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return \Neos\Flow\Http\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return \Neos\Flow\Http\Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
