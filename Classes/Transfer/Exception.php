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
use Flowpack\ElasticSearch\Exception as ElasticSearchException;
use Neos\Flow\Http\Request;
use Neos\Flow\Http\Response as FlowResponse;

/**
 * Exception that occurs related to ElasticSearch transfers
 */
class Exception extends ElasticSearchException
{
    /**
     * @var FlowResponse
     */
    protected $response;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Exception constructor.
     *
     * @param string $message
     * @param int $code
     * @param FlowResponse $response
     * @param Request $request
     * @param \Exception $previous
     */
    public function __construct($message, $code, FlowResponse $response, Request $request = null, \Exception $previous = null)
    {
        $this->response = $response;
        $this->request = $request;
        if ($request !== null) {
            $message = sprintf(
                "Elasticsearch request failed.\n[%s %s]: %s\n\nRequest data: %s",
                $request->getMethod(),
                $request->getUri(),
                $message . '; Response body: ' . $response->getContent(),
                $request->getContent()
            );
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return FlowResponse
     */
    public function getResponse()
    {
        return $this->response;
    }
}
