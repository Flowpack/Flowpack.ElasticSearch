<?php
namespace Flowpack\ElasticSearch\Domain\Factory;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.ElasticSearch".*
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\ORM\Mapping as ORM;
use Flowpack\ElasticSearch\Domain\Model;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Error\Error;
use TYPO3\Flow\Error\Result as ErrorResult;

/**
 * Reconstitute a Document from the ElasticSearch index.
 *
 * TODO: maybe move into Document::createFromResponse()
 *
 * @Flow\Scope("singleton")
 */
class DocumentFactory {

	/**
	 * @param \Flowpack\ElasticSearch\Domain\Model\AbstractType $type
	 * @param string $id
	 * @param \Flowpack\ElasticSearch\Transfer\Response $response
	 * @throws \Flowpack\ElasticSearch\Domain\Exception\DocumentPropertiesMismatchException
	 * @return \Flowpack\ElasticSearch\Domain\Model\Document
	 */
	public function createFromResponse(Model\AbstractType $type, $id = NULL, \Flowpack\ElasticSearch\Transfer\Response $response) {
		$content = $response->getTreatedContent();

		$verificationResults = new ErrorResult();
		if (isset($content['_index']) && $type->getIndex()->getName() !== $content['_index']) {
			$error = new Error('The received index name "%s" does not match the expected one "%s".', 1340264838, array($content['_index'], $type->getIndex()->getName()));
			$verificationResults->addError($error);
		}
		if (isset($content['_type']) && $type->getName() !== $content['_type']) {
			$error = new Error('The received type name "%s" does not match the expected one "%s".', 1340265103, array($content['_type'], $type->getName()));
			$verificationResults->addError($error);
		}

		if (isset($content['_id']) && $id !== NULL && $id !== $content['_id']) {
			$error = new Error('The received id "%s" does not match the expected one "%s".', 1340269758, array($content['_id'], $id));
			$verificationResults->addError($error);
		}

		if ($verificationResults->hasErrors()) {
			$exception = new \Flowpack\ElasticSearch\Domain\Exception\DocumentPropertiesMismatchException('The document\'s properties do not match the expected ones.', 1340265248);
			$exception->setErrorResult($verificationResults);
			throw $exception;
		}

		$version = $content['_version'];
		$data = $content['_source'];

		return new Model\Document($type, $data, $id, $version);
	}
}

