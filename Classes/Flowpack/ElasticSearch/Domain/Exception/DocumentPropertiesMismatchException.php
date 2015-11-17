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

/**
 * Signals a mismatch between the
 */
class DocumentPropertiesMismatchException extends \Flowpack\ElasticSearch\Exception
{
    /**
     * @var \TYPO3\Flow\Error\Result
     */
    protected $errorResult;

    /**
     * @param \TYPO3\Flow\Error\Result $result
     */
    public function setErrorResult(\TYPO3\Flow\Error\Result $result)
    {
        $this->errorResult = $result;
    }
}
