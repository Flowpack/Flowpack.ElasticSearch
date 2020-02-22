<?php
declare(strict_types=1);

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
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Exception that occurs related to ElasticSearch transfers
 */
class Exception extends ElasticSearchException
{
    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param string $message
     * @param int $code
     * @param ResponseInterface $response
     * @param RequestInterface|null $request
     * @param \Exception|null $previous
     */
    public function __construct($message, $code, ResponseInterface $response, RequestInterface $request = null, \Exception $previous = null)
    {
        $this->response = $response;
        $this->request = $request;

        $this->request->getBody()->rewind();
        $this->response->getBody()->rewind();

        if ($request !== null) {
            $message = sprintf(
"Elasticsearch request failed:
\n[%s %s]: %s
\n\n
Request data: 
\n%s
\n\n
Response body:
\n%s",
                $request->getMethod(),
                $request->getUri(),
                $message,
                $request->getBody()->getContents(),
                $response->getBody()->getContents()
            );
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
