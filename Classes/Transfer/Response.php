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

use Neos\Flow\Http\Request;
use Neos\Flow\Http\Response as HttpResponse;

class Response
{
    /**
     * @var HttpResponse
     */
    protected $originalResponse;

    /**
     * Contains the implementation-specific treated content
     *
     * @var mixed
     */
    protected $treatedContent;

    /**
     * @param HttpResponse $response
     * @param Request $request
     *
     * @throws Exception
     * @throws Exception\ApiException
     */
    public function __construct(HttpResponse $response, Request $request = null)
    {
        $this->originalResponse = $response;

        $treatedContent = json_decode($response->getContent(), true);

        if (strlen($response->getContent()) > 0) {
            if ($treatedContent === null) {
                throw new Exception('The request returned an invalid JSON string which was "' . $response->getContent() . '".', 1338976439, $response, $request);
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
     * @return HttpResponse
     */
    public function getOriginalResponse()
    {
        return $this->originalResponse;
    }
}
