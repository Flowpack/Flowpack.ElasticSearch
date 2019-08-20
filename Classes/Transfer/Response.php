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

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Response
{
    /**
     * @var ResponseInterface
     */
    protected $originalResponse;

    /**
     * Contains the implementation-specific treated content
     *
     * @var mixed
     */
    protected $treatedContent;

    /**
     * Response constructor.
     * @param ResponseInterface $response
     * @param RequestInterface|null $request
     * @throws Exception
     * @throws Exception\ApiException
     */
    public function __construct(ResponseInterface $response, RequestInterface $request = null)
    {
        $this->originalResponse = $response;

        $content = $response->getBody()->getContents();
        $treatedContent = json_decode($content, true);

        if (strlen($content) > 0) {
            if ($treatedContent === null) {
                throw new Exception('The request returned an invalid JSON string which was "' . $content . '".', 1338976439, $response, $request);
            }

            if (array_key_exists('error', $treatedContent)) {
                $exceptionMessage = print_r($treatedContent['error'], true);
                throw new Exception\ApiException($exceptionMessage, 1338977435, $response, $request);
            }
        }

        $this->treatedContent = $treatedContent;
    }

    /**
     * Shortcut to response's getStatusCode
     *
     * @return int
     */
    public function getStatusCode(): int
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
     * @return ResponseInterface
     */
    public function getOriginalResponse(): ResponseInterface
    {
        return $this->originalResponse;
    }
}
