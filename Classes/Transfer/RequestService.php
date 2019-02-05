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

use Flowpack\ElasticSearch\Domain\Model\Client as ElasticSearchClient;
use Flowpack\ElasticSearch\Domain\Model\Client\ClientConfiguration;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Client\Browser;
use Neos\Flow\Http\Client\CurlEngine;
use Neos\Flow\Http\Request;

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
        $this->browser->setRequestEngine($requestEngine);
    }

    /**
     * @param string $method
     * @param ElasticSearchClient $client
     * @param string $path
     * @param array $arguments
     * @param string|array $content
     * @return Response
     */
    public function request($method, ElasticSearchClient $client, $path = null, $arguments = [], $content = null)
    {
        $clientConfigurations = $client->getClientConfigurations();
        $clientConfiguration = $clientConfigurations[0];
        /** @var ClientConfiguration $clientConfiguration */

        $uri = clone $clientConfiguration->getUri();
        if ($path !== null) {
            if (strpos($path, '?') !== false) {
                list($path, $query) = explode('?', $path);
                $uri->setQuery($query);
            }
            $uri->setPath($uri->getPath() . $path);
        }

        $request = Request::create($uri, $method, $arguments, [], []);
        // In some cases, $content will contain "null" as a string. Better be safe and handle this weird case:
        if ($content === 'null') {
            $request->setContent(null);
        } else {
            $request->setContent((is_array($content) ? json_encode($content) : $content));
        }
        if ($uri->getUsername()) {
            $requestUri = $request->getUri();
            $requestUri->setUsername($uri->getUsername());
            $requestUri->setPassword($uri->getPassword());
        }
        
        $request->setHeader('Content-Type', 'application/json');
        $response = $this->browser->sendRequest($request);

        return new Response($response, $this->browser->getLastRequest());
    }
}
