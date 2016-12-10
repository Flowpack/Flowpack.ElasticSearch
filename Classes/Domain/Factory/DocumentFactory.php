<?php
namespace Flowpack\ElasticSearch\Domain\Factory;

/*
 * This file is part of the Flowpack.ElasticSearch package.
 *
 * (c) Contributors of the Flowpack Team - flowpack.org
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\ORM\Mapping as ORM;
use Flowpack\ElasticSearch\Domain\Model;
use Neos\Flow\Annotations as Flow;
use Neos\Error\Messages\Error;
use Neos\Error\Messages\Result as ErrorResult;

/**
 * Reconstitute a Document from the ElasticSearch index.
 *
 * TODO: maybe move into Document::createFromResponse()
 *
 * @Flow\Scope("singleton")
 */
class DocumentFactory
{
    /**
     * @param \Flowpack\ElasticSearch\Domain\Model\AbstractType $type
     * @param string $id
     * @param \Flowpack\ElasticSearch\Transfer\Response $response
     * @throws \Flowpack\ElasticSearch\Domain\Exception\DocumentPropertiesMismatchException
     * @return \Flowpack\ElasticSearch\Domain\Model\Document
     */
    public function createFromResponse(Model\AbstractType $type, $id = null, \Flowpack\ElasticSearch\Transfer\Response $response)
    {
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

        if (isset($content['_id']) && $id !== null && $id !== $content['_id']) {
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
