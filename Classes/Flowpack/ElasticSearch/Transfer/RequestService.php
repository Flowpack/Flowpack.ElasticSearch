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

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Http\Client\CurlEngine;

/**
 * Handles the requests
 * @Flow\scope("singleton")
 */
class RequestService
{
    /**
     * @Flow\Inject
     * @var \TYPO3\Flow\Http\Client\Browser
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
     * @param \Flowpack\ElasticSearch\Domain\Model\Client $client
     * @param string $path
     * @param array $arguments
     * @param string|array $content
     *
     * @return \Flowpack\ElasticSearch\Transfer\Response
     */
    public function request($method, \Flowpack\ElasticSearch\Domain\Model\Client $client, $path = null, $arguments = array(), $content = null)
    {
        $clientConfigurations = $client->getClientConfigurations();
        $clientConfiguration = $clientConfigurations[0];

        $uri = clone $clientConfiguration->getUri();
        if ($path !== null) {
            $uri->setPath($uri->getPath() . $path);
        }

        if ($uri->getUsername()) {
            $requestEngine = $this->browser->getRequestEngine();
            $requestEngine->setOption(CURLOPT_USERPWD, $uri->getUsername() . ':' . $uri->getPassword() );
            $requestEngine->setOption(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        }

        $response = $this->browser->request($uri, $method, $arguments, array(), array(),
            is_array($content) ? json_encode($content) : $content);

        return new Response($response, $this->browser->getLastRequest());
    }
}
