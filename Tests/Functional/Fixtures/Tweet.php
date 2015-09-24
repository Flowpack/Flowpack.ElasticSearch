<?php
namespace Flowpack\ElasticSearch\Tests\Functional\Fixtures;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.ElasticSearch".*
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use \TYPO3\Flow\Annotations as Flow;
use \Flowpack\ElasticSearch\Annotations as ElasticSearch;

/**
 * An object for the "twitter" index, representing a "tweet" document.
 *
 * @Flow\Entity
 * @ElasticSearch\Indexable(indexName="flow3_elasticsearch_functionaltests_twitter", typeName="tweet")
 */
class Tweet
{
    /**
     * @var string
     * @ElasticSearch\Mapping(boost=2.0, term_vector="with_offsets")
     */
    protected $username;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var \DateTime
     * @ElasticSearch\Mapping(format="YYYY-MM-dd")
     * @ElasticSearch\Transform("Date", options={ "format"="Y-m-d" })
     */
    protected $date;

    /**
     * @param \DateTime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }
}
