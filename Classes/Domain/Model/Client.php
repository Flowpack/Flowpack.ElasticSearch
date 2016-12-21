<?php
namespace Flowpack\ElasticSearch\Domain\Model;

/*
 * This file is part of the Flowpack.ElasticSearch package.
 *
 * (c) Contributors of the Flowpack Team - flowpack.org
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;

/**
 * A Client representation
 */
class Client
{
    /**
     * @var string
     */
    protected $bundle = 'default';

    /**
     * @Flow\Inject
     * @var \Flowpack\ElasticSearch\Transfer\RequestService
     */
    protected $requestService;

    /**
     * @var array
     */
    protected $clientConfigurations;

    /**
     * @var array
     */
    protected $indexCollection = array();

    /**
     * @param string $bundle
     */
    public function setBundle($bundle)
    {
        $this->bundle = $bundle;
    }

    /**
     * @return string
     */
    public function getBundle()
    {
        return $this->bundle;
    }

    /**
     * @param array $clientConfigurations
     */
    public function setClientConfigurations($clientConfigurations)
    {
        $this->clientConfigurations = $clientConfigurations;
    }

    /**
     * @return array
     */
    public function getClientConfigurations()
    {
        return $this->clientConfigurations;
    }

    /**
     * @param string $indexName
     * @return \Flowpack\ElasticSearch\Domain\Model\Index
     */
    public function findIndex($indexName)
    {
        if (!array_key_exists($indexName, $this->indexCollection)) {
            $this->indexCollection[$indexName] = new Index($indexName, $this);
        }

        return $this->indexCollection[$indexName];
    }

    /**
     * Passes a request through to the request service
     *
     * @param string $method
     * @param string $path
     * @param array $arguments
     * @param string|array $content
     * @return \Flowpack\ElasticSearch\Transfer\Response
     */
    public function request($method, $path = null, array $arguments = array(), $content = null)
    {
        return $this->requestService->request($method, $this, $path, $arguments, $content);
    }
}
