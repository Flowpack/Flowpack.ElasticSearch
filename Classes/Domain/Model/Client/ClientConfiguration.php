<?php
declare(strict_types=1);

namespace Flowpack\ElasticSearch\Domain\Model\Client;

/*
 * This file is part of the Flowpack.ElasticSearch package.
 *
 * (c) Contributors of the Flowpack Team - flowpack.org
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use GuzzleHttp\Psr7\Uri;
use Neos\Flow\Annotations as Flow;
use Neos\Http\Factories\UriFactory;
use Psr\Http\Message\UriInterface;

/**
 * Client configuration
 */
class ClientConfiguration
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string
     */
    protected $scheme = 'http';

    /**
     * @var string
     */
    protected $username = '';

    /**
     * @var string
     */
    protected $password = '';

    /**
     * @Flow\Inject
     * @var UriFactory
     */
    protected $uriFactory;

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     * @return void
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param int $port
     * @return void
     */
    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @param string $scheme
     * @return void
     */
    public function setScheme(string $scheme): void
    {
        $this->scheme = $scheme;
    }

    /**
     * Returns username
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Sets username
     *
     * @param string $username
     * @return void
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * Returns password
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Sets password
     *
     * @param string $password
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return UriInterface
     */
    public function getUri(): UriInterface
    {
        return $this->uriFactory->createUri()
            ->withScheme($this->scheme)
            ->withHost($this->host)
            ->withPort($this->port)
            ->withUserInfo($this->username, $this->password);
    }
}
