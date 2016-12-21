<?php
namespace Flowpack\ElasticSearch\Domain\Exception;

/*
 * This file is part of the Flowpack.ElasticSearch package.
 *
 * (c) Contributors of the Flowpack Team - flowpack.org
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Error\Messages\Result;

/**
 * Signals a mismatch between the
 */
class DocumentPropertiesMismatchException extends \Flowpack\ElasticSearch\Exception
{
    /**
     * @var Result
     */
    protected $errorResult;

    /**
     * @param Result $result
     * @return void
     */
    public function setErrorResult(Result $result)
    {
        $this->errorResult = $result;
    }
}
