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

use Flowpack\ElasticSearch\Domain\Model\Client as ElasticSearchClient;
use Flowpack\ElasticSearch\Domain\Model\Client\ClientConfiguration;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Client\Browser;
use Neos\Flow\Http\Client\CurlEngine;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Handles the requests
 *
 * @Flow\scope("singleton")
 */
class RequestService
{
    /**
     * @Flow\Inject
     * @var Browser
     */
    protected $browser;

    /**
     * @Flow\Inject
     * @var ServerRequestFactoryInterface
     */
    protected $requestFactory;

    /**
     * @Flow\Inject
     * @var StreamFactoryInterface
     */
    protected $contentStreamFactory;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @param array $settings
     * @return void
     */
    public function injectSettings(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return void
     */
    public function initializeObject()
    {
        $requestEngine = new CurlEngine();
        $requestEngine->setOption(CURLOPT_TIMEOUT, $this->settings['transfer']['connectionTimeout']);
        $requestEngine->setOption(CURLOPT_SSL_VERIFYPEER, $this->settings['transfer']['sslVerifyPeer'] ?? true ? 2 : 0);
        $requestEngine->setOption(CURLOPT_SSL_VERIFYHOST, $this->settings['transfer']['sslVerifyHost'] ?? true ? 2 : 0);

        if (!empty($this->settings['transfer']['sslCaInfo'])) {
            $requestEngine->setOption(CURLOPT_CAINFO, $this->settings['transfer']['sslCaInfo']);
        }

        if (!empty($this->settings['transfer']['sslKey'])) {
            $requestEngine->setOption(CURLOPT_SSLKEY, $this->settings['transfer']['sslKey']);
        }

        if (!empty($this->settings['transfer']['sslCert'])) {
            $requestEngine->setOption(CURLOPT_SSLCERT, $this->settings['transfer']['sslCert']);
        }

        if (!empty($this->settings['transfer']['sslKeyPasswd'])) {
            $requestEngine->setOption(CURLOPT_SSLKEYPASSWD, $this->settings['transfer']['sslKeyPasswd']);
        }

        $this->browser->setRequestEngine($requestEngine);
    }

    /**
     * @param string $method
     * @param ElasticSearchClient $client
     * @param string $path
     * @param array $arguments
     * @param string|array $content
     * @return Response
     * @throws Exception
     * @throws Exception\ApiException
     * @throws \Neos\Flow\Http\Exception
     */
    public function request($method, ElasticSearchClient $client, ?string $path = null, array $arguments = [], $content = null): Response
    {
        $clientConfigurations = $client->getClientConfigurations();
        $clientConfiguration = $clientConfigurations[0];
        /** @var ClientConfiguration $clientConfiguration */

        $uri = clone $clientConfiguration->getUri();

        if ($path !== null) {
            if (strpos($path, '?') !== false) {
                list($path, $query) = explode('?', $path);
                $uri = $uri->withQuery($query);
            }

            $uri = $uri->withPath($uri->getPath() . $path);
        }

        if (!empty($arguments)) {
            $uri = $uri->withQuery($uri->getQuery() . '&' . http_build_query($arguments));
        }

        $request = $this->requestFactory->createServerRequest($method, $uri);

        // In some cases, $content will contain "null" as a string. Better be safe and handle this weird case:
        if ($content !== 'null' && $content !== null) {
            $request = $request->withBody($this->contentStreamFactory->createStream(is_array($content) ? json_encode($content) : (string)$content));
        }

        $request = $request->withHeader('Content-Type', 'application/json');
        $response = $this->browser->sendRequest($request);

        return new Response($response, $this->browser->getLastRequest());
    }
}
