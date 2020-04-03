<?php
declare(strict_types=1);

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

use Flowpack\ElasticSearch\Domain\Exception\DocumentPropertiesMismatchException;
use Flowpack\ElasticSearch\Domain\Factory\DocumentFactory;
use Flowpack\ElasticSearch\Transfer\Response;
use Neos\Flow\Annotations as Flow;

/**
 * An abstract document type. Implement your own or use the GenericType provided with this package.
 */
abstract class AbstractType
{
    /**
     * @Flow\Inject
     * @var DocumentFactory
     */
    protected $documentFactory;

    /**
     * @var Index
     */
    protected $index;

    /**
     * @var string
     */
    protected $name;

    /**
     * @param Index $index
     * @param string $name
     */
    public function __construct(Index $index, $name = null)
    {
        $this->index = $index;

        if ($name === null) {
            $this->name = str_replace('\\', '_', get_class($this));
        } else {
            $this->name = $name;
        }
    }

    /**
     * Gets this type's name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Index
     */
    public function getIndex(): Index
    {
        return $this->index;
    }

    /**
     * Returns a document
     *
     * @param string $id
     * @return Document
     * @throws DocumentPropertiesMismatchException
     * @throws \Flowpack\ElasticSearch\Exception
     * @throws \Neos\Flow\Http\Exception
     */
    public function findDocumentById(string $id): Document
    {
        $response = $this->request('GET', '/' . $id);
        if ($response->getStatusCode() !== 200) {
            return null;
        }

        return $this->documentFactory->createFromResponse($this, $id, $response);
    }

    /**
     * @param string $method
     * @param string $path
     * @param array $arguments
     * @param string $content
     * @return Response
     * @throws \Flowpack\ElasticSearch\Exception
     * @throws \Neos\Flow\Http\Exception
     */
    public function request(string $method, ?string $path = null, array $arguments = [], ?string $content = null): Response
    {
        return $this->index->request($method, $path, $arguments, $content);
    }

    /**
     * @param string $id
     * @return boolean ...whether the deletion is considered successful
     * @throws \Flowpack\ElasticSearch\Exception
     * @throws \Neos\Flow\Http\Exception
     */
    public function deleteDocumentById(string $id): bool
    {
        $response = $this->request('DELETE', '/' . $id);
        $treatedContent = $response->getTreatedContent();

        return $response->getStatusCode() === 200 && $treatedContent['result'] === 'deleted';
    }

    /**
     * @return int
     * @throws \Flowpack\ElasticSearch\Exception
     * @throws \Neos\Flow\Http\Exception
     */
    public function count(): ?int
    {
        $response = $this->request('GET', '/_count');
        if ($response->getStatusCode() !== 200) {
            return null;
        }
        $treatedContent = $response->getTreatedContent();

        return (integer)$treatedContent['count'];
    }

    /**
     * @param array $searchQuery The search query TODO: make it an object
     * @return Response
     * @throws \Flowpack\ElasticSearch\Exception
     * @throws \Neos\Flow\Http\Exception
     */
    public function search(array $searchQuery): Response
    {
        return $this->request('GET', '/_search', [], json_encode($searchQuery));
    }
}
