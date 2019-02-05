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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Index
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Returns a document
     *
     * @param string $id
     * @return Document
     */
    public function findDocumentById($id)
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
     */
    public function request($method, $path = null, array $arguments = [], $content = null, $header = null)
    {
        $path = '/' . $this->name . ($path ?: '');

        return $this->index->request($method, $path, $arguments, $content);
    }

    /**
     * @param string $id
     * @return boolean ...whether the deletion is considered successful
     */
    public function deleteDocumentById($id)
    {
        $response = $this->request('DELETE', '/' . $id);
        $treatedContent = $response->getTreatedContent();

        return $response->getStatusCode() === 200 && $treatedContent['result'] === 'deleted';
    }

    /**
     * @return int
     */
    public function count()
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
     */
    public function search(array $searchQuery)
    {
        return $this->request('GET', '/_search', [], json_encode($searchQuery));
    }

    /**
     * A bulk request always needs the following strukture:
     * action_and_meta_data
     * optional_source
     * action_and_meta_data
     * optional_source
     * As the index and type are already in the url the meta_data part could be empty
     *
     * @param array $arguments
     * @param string|array $content
     * @return Response
     */
    public function bulkRequest(array $arguments = [], $content = null)
    {
        $path = '/' . $this->name . '/_bulk';
        if (is_array($content)) {
            $ndjsonContent = '';
            foreach ($content as $contentItem) {
                // JSON_FORCE_OBJECT is important here as a empty meta_data needs to be a empty json object
                $ndjsonContent = $ndjsonContent . json_encode($contentItem, JSON_FORCE_OBJECT) . "\n";
            }
            $content = $ndjsonContent;
        }
        return $this->index->request('POST', $path, $arguments, $content, true, 'application/x-ndjson');
    }
}
