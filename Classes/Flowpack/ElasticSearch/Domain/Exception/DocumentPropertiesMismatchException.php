<?php
namespace Flowpack\ElasticSearch\Domain\Exception;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.ElasticSearch".*
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

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
